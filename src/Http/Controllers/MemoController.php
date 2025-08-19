<?php
namespace Saidabdulsalam\LaravelMemo\Http\Controllers;

use Saidabdulsalam\LaravelMemo\Models\Memo;
use Saidabdulsalam\LaravelMemo\Http\Requests\MemoRequest;
use Saidabdulsalam\LaravelMemo\Http\Resources\MemoResource;
use Saidabdulsalam\LaravelMemo\Enums\MemoStatus;
use Saidabdulsalam\LaravelMemo\Enums\MemoType;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Saidabdulsalam\LaravelMemo\Events\MemoApproved;
use Saidabdulsalam\LaravelMemo\Events\MemoComment;
use Saidabdulsalam\LaravelMemo\Events\MemoCreated;
use Saidabdulsalam\LaravelMemo\Events\MemoRejected;
use Saidabdulsalam\LaravelMemo\Events\MemoUpdated;
use Saidabdulsalam\LaravelMemo\Notifications\MemoAssigned;
use Saidabdulsalam\LaravelMemo\Models\Comment;
use Saidabdulsalam\LaravelMemo\Models\MemoLog;

class MemoController extends Controller
{
    public function boot(Request $request){
        $user = $request->user();
        $models = config('memo.members_models', []);
        $names = config('memo.name', []);
        $name = '';
        $user_type = get_class($user);
        foreach ($models as $key => $model) {
            if($model == $user_type){
                $name = $names[$key];
                break;
            }
        }
        return response()->json([
            "user"=>['id'=> $user->id, 'user_type'=>$user_type, 'full_name'=>$user->{$name}]
        ]);
    }

    public function index(Request $request)
    {
        $filter = $request->all();
        $user = $request->user();
        if(isset($filter['category'])){
            $filter[strtolower($filter['category'])] = [
                "owner_id" => $user->id,
                "owner_type" => get_class($user)
            ];
        }

        $departmentId = $user->{config('memo.user_department_id_column')};
        $officeId = $user->{config('memo.user_office_id_column')};
        $filter['department_id'] = $departmentId;
        $filter['office_id'] = $officeId;
        $memos = Memo::filter($filter)
            ->latest()
            ->paginate(config('memo.pagination_length'));

        return MemoResource::collection($memos);
    }

    public function members(Request $request){
        $models = config('memo.members_models', []);
        $names = config('memo.name', []);
        $filters = config('memo.members_models_filters', []);
        $mergedData = [];

        foreach ($models as $key => $model) {
            if (!class_exists($model)) continue;
            $name = $names[$key] ?? null;
            $filter = $filters[$key] ?? null;
            if ($filter && is_array($filter)) {
                $data = $model::where($filter)->get();
            } else {
                $data = $model::all();
            }
            $mergedData = array_merge($mergedData, $data->map(function ($m) use ($model, $name) {
                return [
                    "approver_id" => $m->id,
                    "approver_type" => $model,
                    "full_name" => $name ? $m->{$name} : null,
                ];
            })->toArray());
        }

        return response()->json($mergedData);
    }

    public function departments(){
        $model  = config('memo.department_model');
        return response()->json($model::all());
    }

    public function saveComment(Request $request)
    {
        $validated = $request->validate([
            'memo_id'=>'required',
            'comment' => 'required|string',
            'files' => 'nullable|string',
        ]);

        $memo = Memo::findOrFail($request->memo_id);
        $user = $request->user();
        $commentData = [
            'memo_id' => $memo->id,
            'comment' => $validated['comment'],
            'files' => $validated['files'] ?? null,
            'status' => MemoStatus::SUBMITTED,
            "approver_id"=>  $user->id,
            "approver_type"=>  get_class($user)
        ];

        Comment::create($commentData);
        event(new MemoComment($memo, $request->user()));
        return response()->json(['message' => 'Comment saved successfully'], 201);
    }

    public function createOrUpdateMemo(MemoRequest $request)
    {
        $id = $request->id;
        $owner = $request->user();
        $data = $request->validated();
        $data['owner_id'] = $owner->id;
        $data['owner_type'] = get_class($owner);
    
        if ($id) {
            $memo = Memo::findOrFail($id);
            $user = $request->user();
            $is_memo_owner = ($user->id == $memo->owner_id && $memo->owner_type == get_class($user));
    
            if ($memo->status === MemoStatus::APPROVED) {
                if($is_memo_owner){
                    abort(422, 'Not allowed to update an approved memo.');
                }
            }
         
            if($is_memo_owner){
                $memo->update($request->validated());
                $comment = Comment::create([
                    'memo_id'=> $memo->id,
                    'comment'=> "Memo content updated",
                    'approver_id'=>$user->id,
                    'approver_type'=> get_class($user),
                    "department_id" =>  $request->department_id,
                    "type" => MemoType::getValue($request->type) ?? MemoType::REQUEST,
                ]);
                event(new MemoUpdated($memo));
            }
            
            $this->manageApprovers($memo, $request->input('approvers', []), $is_memo_owner, $request);

        } else {
            $memo = new Memo();
            $memo->fill($data);
            $memo->status = MemoStatus::getValue($request->status) ?? MemoStatus::SUBMITTED;
            $memo->type = MemoType::getValue($request->type) ?? MemoType::REQUEST;
            $memo->department_id = $this->cleanNullValues($request->department_id) ?? $owner->{config('memo.user_department_id_column')};
            $memo->office_id = $this->cleanNullValues($owner->{config('memo.user_office_id_column')});
            $memo->save();
            $this->manageApprovers($memo, $request->input('approvers', []),true, $request);
            event(new MemoCreated($memo));

            // notify all users in selected departments (if configured)
            $deptModel = config('memo.department_model');
            if($memo->department_id && class_exists($deptModel)){
                try{
                    $users = $deptModel::whereIn('id', $memo->department_id)->with('users')->get();
                    foreach($users as $d){
                        if(isset($d->users) && is_iterable($d->users)){
                            foreach($d->users as $u){
                                if(method_exists($u, 'notify')){
                                    try{ $u->notify(new MemoAssigned($memo)); }catch(\Throwable$e){}
                                }
                            }
                        }
                    }
                }catch(\Throwable$e){
                    // ignore if department model doesn't have users relation
                }
            }
        }
        
        $memo = $memo->fresh();
        return (new MemoResource($memo))
            ->response()
            ->setStatusCode($id ? 200 : 201);
    }

    protected function cleanNullValues($value)
    {
         if ($value =='null') {
            return null;
        }
        return $value;
    }
    
    protected function manageApprovers(Memo $memo, array $approvers, $is_memo_owner = true,  Request $request = null)
    {
        $existingApprovers = $memo->approvers()->get()->keyBy('id');

        foreach ($approvers as $idx => $approver) {
            if (isset($approver['id'])) {
                $record = $existingApprovers->get($approver['id']);
                if (!$record) continue;

                $canUpdate = $is_memo_owner || ($record->forwarded == 1 && $record->approver_id == $request->user()->id && $record->approver_type == get_class($request->user()));

                if ($canUpdate) {
                    $newStatus = MemoStatus::getValue($approver['status'] ?? 'PENDING') ?? MemoStatus::PENDING;
                    $record->status = $newStatus;
                    $record->save();

                    MemoLog::create([
                        'memo_id' => $memo->id,
                        'approver_id' => $record->approver_id,
                        'approver_type' => $record->approver_type,
                        'status' => $newStatus,
                    ]);

                    if (!$is_memo_owner) {
                        if ($newStatus == MemoStatus::APPROVED) {
                            event(new MemoApproved($memo, $request->user()));
                            $this->forwardNextApprover($memo, $record->approver_id, $record->approver_type);
                        } elseif ($newStatus == MemoStatus::REJECTED) {
                            event(new MemoRejected($memo, $request->user()));
                        }
                    }
                }

            } else {
                if (class_exists($approver['approver_type']) && $is_memo_owner) {
                    $forwarded = 0;
                    if ($idx === 0 && $memo->approvers()->count() == 0) {
                        $forwarded = 1;
                    }

                    $new = $memo->approvers()->create([
                        'approver_id' => $approver['approver_id'],
                        'approver_type' => $approver['approver_type'],
                        'status' => MemoStatus::getValue($approver['status'] ?? 'PENDING') ?? MemoStatus::PENDING,
                        'forwarded' => $forwarded,
                    ]);

                    // reload relation so the approver model is available
                    $new->load('approver');

                    if ($forwarded && $new->approver && method_exists($new->approver, 'notify')) {
                        try {
                            $new->approver->notify(new MemoAssigned($memo));
                        } catch (\Throwable $e) {}
                    }
                }
            }
        }

        if ($is_memo_owner) {
            $incomingIds = array_filter(array_column($approvers, 'id'));
            foreach ($existingApprovers as $existing) {
                if (!in_array($existing->id, $incomingIds, true)) {
                    $memo->approvers()->where('id', $existing->id)->delete();
                }
            }
        }
    }

    public function memoStatus()
    {
        return response()->json(collect(MemoStatus::getKeys())->keys());
    }

    public function approveMemo($id)
    {
        $memo = Memo::findOrFail($id);

        if ($memo->status === MemoStatus::APPROVED) {
            return response()->json(['message' => 'Memo is already approved.'], 422);
        }

        $memo->status = MemoStatus::APPROVED;
        $memo->save();

        return new MemoResource($memo);
    }

    public function rejectMemo($id, Request $request)
    {
        $memo = Memo::findOrFail($id);

        if ($memo->status === MemoStatus::REJECTED) {
            return response()->json(['message' => 'Memo is already rejected.'], 422);
        }

        $memo->status = MemoStatus::REJECTED;
        if ($request->filled('reason')) {
            $memo->rejection_reason = $request->input('reason');
        }
        $memo->save();

        return new MemoResource($memo);
    }

    public function updateMemoStatus(Request $request)
    {
        $request->validate(["id" => "required|exists:memos,id"]);
        $memo = Memo::findOrFail($request->id);
        $status = MemoStatus::getValue($request->input('status'));

        if ($memo->status === $status) {
            return response()->json(['message' => 'Memo is already ' . $request->input('status')], 422);
        }

        $memo->status = $status;
        $memo->save();

        $approver_id = $request->user()->id();
        $approver_type = get_class($request->user());

        $existingComments = $memo->comments()
            ->where("approver_id", $approver_id)
            ->where("approver_type", $approver_type)
            ->pluck("id")
            ->toArray();

        $comments = $request->input('comments', []);

        foreach ($comments as $commentData) {
            if (isset($commentData['id'])) {
                $comment = Comment::findOrFail($commentData['id']);
                $comment->comment = $commentData['comment'];
                $comment->save();
            } else {
                $memo->comments()->create([
                    'comment' => $commentData['comment'],
                    'approver_id' => $approver_id,
                    'approver_type' => $approver_type,
                ]);
            }
        }

        foreach ($existingComments as $existingComment) {
            if (!in_array($existingComment, collect($comments)->pluck("id")->toArray(), true)) {
                Comment::find($existingComment)->delete();
            }
        }

        $memo = $memo->fresh();

        if($status === MemoStatus::APPROVED){
            event(new MemoApproved($memo,$request->user()));
        }else{
            event(new MemoRejected($memo,$request->user()));
        }
        return new MemoResource($memo);
    }

    public function deleteMemo($id)
    {
        $memo = Memo::findOrFail($id);
        $memo->delete();

        return response()->json(['message' => 'Memo deleted successfully']);
    }

    public function updateComment(Request $request, $id)
    {
        $validated = $request->validate(['comment' => 'required|string']);
        $comment = Comment::findOrFail($id);
        $comment->update($validated);
        return response()->json(['message' => 'Comment updated successfully']);
    }

    public function deleteComment($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();
        return response()->json(['message' => 'Comment deleted successfully']);
    }

    protected function forwardNextApprover(Memo $memo, $currentApproverId, $currentApproverType)
    {
        $approvers = $memo->approvers()->orderBy('id')->get();
        $foundCurrent = false;
        foreach ($approvers as $app) {
            if ($foundCurrent) {
                $app->forwarded = 1;
                $app->save();
                if(method_exists($app->approver, 'notify')){
                    try{ $app->approver->notify(new MemoAssigned($memo)); }catch(\Throwable$e){}
                }
                break;
            }
            if ($app->approver_id == $currentApproverId && $app->approver_type == $currentApproverType) {
                $foundCurrent = true;
            }
        }
    }

    public function memoTypes()
    {
        return response()->json(collect(MemoType::getKeys())->keys());
    }
}
        
<?php

namespace Saidabdulsalam\LaravelMemo\Http\Controllers;

use Saidabdulsalam\LaravelMemo\Models\Memo;
use Saidabdulsalam\LaravelMemo\Http\Requests\MemoRequest;
use Saidabdulsalam\LaravelMemo\Http\Resources\MemoResource;
use Saidabdulsalam\LaravelMemo\Enums\MemoStatus;
use Saidabdulsalam\LaravelMemo\Enums\MemoType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Saidabdulsalam\LaravelMemo\Events\MemoApproved;
use Saidabdulsalam\LaravelMemo\Events\MemoComment;
use Saidabdulsalam\LaravelMemo\Events\MemoCreated;
use Saidabdulsalam\LaravelMemo\Events\MemoRejected;
use Saidabdulsalam\LaravelMemo\Events\MemoUpdated;
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
        // if(isset($filter['my_memo'])){
        //     $query = [
        //         $request->user()->id,
        //         get_class($request->user()),
        //     ];

        //     if($filter['my_memo']){
        //         $filter['my_memo'] =  $query;
        //         $filter['my_memo'][] =  true;
        //     }else{
        //         $filter['my_memo'] =  $query;
        //         $filter['my_memo'][] = false;
        //     }
        // }
        $user = $request->user();
        if(isset($filter['category'])){
            $filter[strtolower($filter['category'])] = [
                "owner_id" => $user->id,
                "owner_type" => get_class($user)
            ];
        }
        $memos = Memo::filter($filter)->latest()->paginate(config('memo.pagination_length'));
        return MemoResource::collection($memos);
    }

    public function members(Request $request){
       
        // Get models from config
        $models = config('memo.members_models', []); // Default to an empty array
        $names = config('memo.name', []); // Default to an empty array
        $filters = config('memo.members_models_filters', []); // Default to an empty array
        $mergedData = [];

        foreach ($models as $key => $model) {
            // Check if the model class exists
            if (!class_exists($model)) {
                continue; // Skip this iteration if the model does not exist
            }
            
            $name = $names[$key] ?? null; // Use null coalescing to prevent undefined index error
            
            // Check if filter is set and valid
            $filter = $filters[$key] ?? null;

            // Fetch data based on filter
            if ($filter && is_array($filter)) {
                // Make sure the filter is an array
                $data = $model::where($filter)->get();
            } else {
                $data = $model::all();
            }

            // Merge the data while ensuring we have a valid name
            $mergedData = array_merge($mergedData, $data->map(function ($m) use ($model, $name) {
                return [
                    "approver_id" => $m->id,
                    "approver_type" => $model,
                    "full_name" => $name ? $m->{$name} : null, // Safely access the property
                ];
            })->toArray());
        }

        return response()->json($mergedData);

            // return response()->json([
            //     'data' => $mergedData,
            //     'meta' => [
            //         'current_page' => $data->currentPage(),
            //         'last_page' => $data->lastPage(),
            //         'per_page' => $data->perPage(),
            //         'next_page_url'=>$data->nextPageUrl(),
            //         'next_prev_url'=>$data->previousPageUrl(),
            //         'total' => $data->total(),
            //     ],
            // ]);
        
    }

    public function departments(){
        $model  = config('memo.department_model');
        return response()->json($model::all());
    }

    public function saveComment(Request $request)
    {
        // Validate the incoming request data
        
        $validated = $request->validate([
            'memo_id'=>'required',
            'comment' => 'required|string',
            'files' => 'nullable|string',
            // '[approver_id]' => 'nullable|integer',
            // 'approver_type' => 'nullable|integer',
        ]);

        // Find the memo by ID
        $memo = Memo::findOrFail($request->memo_id);
        $user = $request->user();
        // Create the comment data array
        $commentData = [
            'memo_id' => $memo->id,
            'comment' => $validated['comment'],
            'files' => $validated['files'] ?? null,
            'status' => MemoStatus::SUBMITTED, // default status
            "approver_id"=>  $user->id,
            "approver_type"=>  get_class($user)
        ];

        // Check if the comment is from the memo owner
        // if ($user->id !== $memo->owner_id) {
        //     $commentData['approver_id'] = null;
        //     $commentData['approver_type'] = null;
        // }
       
        // Create the comment
        Comment::create($commentData);
        event(new MemoComment($memo, $request->user()));
        // Return a response
        return response()->json([
            'message' => 'Comment saved successfully',
        ], 201);
    }

    public function createOrUpdateMemo(MemoRequest $request)
    {
        $id = $request->id;
        $owner = $request->user();
        $data = $request->validated();
        $data['owner_id'] = $owner->id;
        $data['owner_type'] = get_class($owner);
    
        if ($id) {
            // Update existing memo
            $memo = Memo::findOrFail($id);
            $user = $request->user();
            $is_memo_owner = ($user->id == $memo->owner_id && $memo->owner_type == get_class($user));
    
            if ($memo->status === MemoStatus::APPROVED) {
                if($is_memo_owner){
                    abort(422, 'Not allowed to update an approved memo.');
                }
            }
         
            if($is_memo_owner){
                $comment = Comment::create([
                    'memo_id'=> $memo->id,
                    'comment'=> $request->content,
                    'approver_id'=>$user->id,
                    'approver_type'=> get_class($user),
                    "department_id" =>  $request->department_id,
                    "type" => MemoType::getValue($request->type) ?? MemoType::REQUEST,
                ]);
                // $memo->update([
                // ]);
                event(new MemoUpdated($comment));
            }
            
            $this->manageApprovers($memo, $request->input('approvers', []), $is_memo_owner, $request);

        } else {
            // Create new memo
            $memo = new Memo();
            $memo->fill($data);
            $memo->status = MemoStatus::getValue($request->status) ?? MemoStatus::SUBMITTED;
            $memo->type = MemoType::getValue($request->type) ?? MemoType::REQUEST;
            $memo->save();
            $this->manageApprovers($memo, $request->input('approvers', []),true, $request);
            event(new MemoCreated($memo));
        }
       
        
    
        // Refresh memo
        $memo = $memo->fresh();
       
       
    
        // if (!$id && $is_memo_owner && (MemoStatus::DRAFT == $memo->status || MemoStatus::SUBMITTED == $memo->status)) {
        //     $memo->save();
        // }
    
        return new MemoResource($memo);
    }
    
    protected function manageApprovers(Memo $memo, array $approvers, $is_memo_owner = true,  $request)
    {
        $existingApprovers = $memo->approvers()
            ->get(['id', 'approver_id', 'approver_type'])
            ->map(function ($approver) {
                return [
                    'id' => $approver->id,
                    'approver_id' => $approver->approver_id,
                    'approver_type' => $approver->approver_type,
                ];
            })
            ->toArray();
    
        $approverIds = array_column($approvers, 'id');
        $existingApproverIds = array_column($existingApprovers, 'id');
    
        foreach ($approvers as $approver) {
            if (isset($approver['id'])) {
                // Update the existing approver
                
                $memo->approvers()
                    ->where('id', $approver['id'])
                    ->update([
                        'status'=> MemoStatus::getValue($approver['status']?? "PENDING") ?? MemoStatus::PENDING
                    ]);

                MemoLog::create([
                    'memo_id'=>$memo->id,
                    'approver_id'=> $approver['approver_id'],
                    'approver_type'=> $approver['approver_type'],
                    'status' => MemoStatus::getValue($approver['status']?? "PENDING") ?? MemoStatus::PENDING, 
                ]);

                if(!$is_memo_owner){
                    if($approver['status'] == 'APPROVED'){
                        event(new MemoApproved($memo,$request->user()));
                    }else if($approver['status'] == 'REJECTED'){
                        
                        event(new MemoRejected($memo,$request->user()));
                    }
                }
                //$this->checkAllApprovers($approver, $request->user());
            } else {
                // Add new approver if it does not exist
                if (!in_array(['approver_id' => $approver['approver_id'], 'approver_type' => $approver['approver_type']], $existingApprovers, true) && class_exists($approver['approver_type']) && $is_memo_owner) {
                    $memo->approvers()->create([
                        'approver_id' => $approver['approver_id'],
                        'approver_type' => $approver['approver_type'],
                        'status'=> MemoStatus::getValue($approver['status']??"PENDING")?? MemoStatus::PENDING
                    ]);
                }
            }
        }
    
        if ($is_memo_owner) {
            // Delete existing approvers that are not in the new list of approvers
            foreach ($existingApprovers as $existingApprover) {
                if (!in_array($existingApprover['id'], $approverIds, true)) {
                    $memo->approvers()->where('id', $existingApprover['id'])->delete();
                }
            }
        }
    }
    

    public function memoStatus()
    {
        return response()->json(collect(MemoStatus::getKeys())->keys());
    }

    public function memoTypes()
    {
        return response()->json(collect(MemoType::getKeys())->keys());
    }

    public function approveMemo($id)
    {
        $memo = Memo::findOrFail($id);

        // Check if the memo is already approved
        if ($memo->status === MemoStatus::APPROVED) {
            return response()->json(['message' => 'Memo is already approved.'], 422);
        }

        // Update the status to approved
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
            $memo->rejection_reason = $request->input('reason'); // Make sure you have this column in your migration if needed
        }
        $memo->save();

        return new MemoResource($memo);
    }


    public function updateMemoStatus(Request $request)
    {
        // Validate that the memo ID is provided in the request
        $request->validate([
            "id" => "required|exists:memos,id" // Ensure the ID exists in the memos table
        ]);

        // Retrieve the memo based on the provided ID
        $memo = Memo::findOrFail($request->id);
        $status = MemoStatus::getValue($request->input('status'));

        // Check if the memo is already in the desired status
        if ($memo->status === $status) {
            return response()->json(['message' => 'Memo is already ' . $request->input('status')], 422);
        }

        // Update the memo's status
        $memo->status = $status;
        $memo->save();

        // Get the approver details
        $approver_id = $request->user()->id();
        $approver_type = get_class($request->user());

        // Handle comments
        $existingComments = $memo->comments()
            ->where("approver_id", $approver_id)
            ->where("approver_type", $approver_type)
            ->pluck("id")
            ->toArray();

        $comments = $request->input('comments', []); // Default to an empty array if no comments provided

        foreach ($comments as $commentData) {
            if (isset($commentData['id'])) {
                // Update existing comment if the ID is provided
                $comment = Comment::findOrFail($commentData['id']);
                $comment->comment = $commentData['comment'];
                $comment->save();
            } else {
                // Create a new comment if no ID is provided
                $memo->comments()->create([
                    'comment' => $commentData['comment'],
                    'approver_id' => $approver_id,
                    'approver_type' => $approver_type,
                ]);
            }
        }

        // Delete comments that are no longer included in the request
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
        // Return the updated memo resource
        return new MemoResource($memo); // Use fresh() to get the updated instance
    }


}

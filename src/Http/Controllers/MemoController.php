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
use Saidabdulsalam\LaravelMemo\Events\MemoCreated;
use Saidabdulsalam\LaravelMemo\Events\MemoRejected;
use Saidabdulsalam\LaravelMemo\Events\MemoUpdated;
use Saidabdulsalam\LaravelMemo\Models\Comment;

class MemoController extends Controller
{
    public function index(Request $request)
    {
        $memos = Memo::latest()->paginate(config('memo.pagination_length'));
        return MemoResource::collection($memos);
    }

    public function members(Request $request){
       
            // Get models from config
            $models = config('memo.models');
            $names = config('memo.name');
            $mergedData = [];
    
            foreach ($models as $key=> $model) {
                $name = $names[$key];
                $data = $model::selectRaw("$name as full_name, id, '$model' as approver_type ")->paginate(config('memo.pagination_length'));
    
                $mergedData = array_merge($mergedData, $data->items());
            }
    
            return response()->json([
                'data' => $mergedData,
                'meta' => $data->meta
            ]);
        
    }

    public function createOrUpdateMemo(MemoRequest $request)
    {
        $id =  $request->id;
        $memo = $id ? Memo::findOrFail($id) : new Memo();
        $owner = auth()->user(); 
        if ($memo->status === MemoStatus::APPROVED) {
            abort(422, 'Not allowed to update an approved memo.');
        }

        $data = $request->validated();
        $data['owner_id'] = $owner->id;
        $data['owner_type'] = get_class($owner);
        $memo->fill($data);
        $memo->save();

        $this->manageApprovers($memo, $request->input('approvers', []));

        $memo = $memo->fresh();

        if ($id) {
            event(new MemoUpdated($memo));
        } else {
            event(new MemoCreated($memo));
        }

        return new MemoResource($memo);
    }

    protected function manageApprovers(Memo $memo, array $approvers)
    {
        
        $existingApprovers = $memo->approvers()
            ->get(['approver_id', 'approver_type'])
            ->map(function ($approver) {
                return ['id' => $approver->approver_id, 'type' => $approver->approver_type];
            })
            ->toArray();

        foreach ($approvers as $approver) {
            if (!in_array(['id'=>$approver['id'], 'approver_type'=>$approver['approver_type']], $existingApprovers, true) && class_exists($approver['approver_type'])) {
                $memo->approvers()->create([
                    'approver_id' => $approver['id'],
                    'approver_type' => $approver['type'],
                ]);
            }
        }
    
        foreach ($existingApprovers as $existingApprover) {
            if (!in_array($existingApprover, $approvers, true)) {
                $memo->approvers()
                    ->where('approver_id', $existingApprover['id'])
                    ->where('approver_type', $existingApprover['type'])
                    ->delete();
            }
        }
    }

    public function memoStatus()
    {
        return response()->json(MemoStatus::getKeys());
    }

    public function memoTypes()
    {
        return response()->json(MemoType::getKeys());
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
        $approver_id = auth()->id();
        $approver_type = get_class(auth()->user());

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
            event(new MemoApproved($memo,auth()->user()));
        }else{
            event(new MemoRejected($memo,auth()->user()));
        }
        // Return the updated memo resource
        return new MemoResource($memo); // Use fresh() to get the updated instance
    }


}

<?php

namespace Saidabdulsalam\LaravelMemo\Http\Controllers;

use Saidabdulsalam\LaravelMemo\Models\Memo;
use Saidabdulsalam\LaravelMemo\Http\Requests\MemoRequest;
use Saidabdulsalam\LaravelMemo\Http\Resources\MemoResource;
use Saidabdulsalam\LaravelMemo\Enums\MemoStatus;
use Saidabdulsalam\LaravelMemo\Enums\MemoType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MemoController extends Controller
{
    public function index(Request $request)
    {
        $memos = Memo::latest()->paginate(config('memo.pagination_length'));
        return MemoResource::collection($memos);
    }

    public function createOrUpdateMemo(MemoRequest $request, $id = null)
    {
        $memo = $id ? Memo::findOrFail($id) : new Memo();

        if ($memo->status === MemoStatus::APPROVED) {
            abort(422, 'Not allowed to update an approved memo.');
        }

        $data = $request->validated();
        $memo->fill($data);
        $memo->save();

        $this->manageApprovers($memo, $request->input('approvers', []));

        return new MemoResource($memo);
    }

    protected function manageApprovers(Memo $memo, array $approvers)
    {
        $existingApprovers = $memo->approvers()->pluck('approver_id')->toArray();

        foreach ($approvers as $approverId) {
            if (!in_array($approverId, $existingApprovers)) {
                $memo->approvers()->create(['approver_id' => $approverId]);
            }
        }

        foreach ($existingApprovers as $existingApprover) {
            if (!in_array($existingApprover, $approvers)) {
                $memo->approvers()->where('approver_id', $existingApprover)->delete();
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
}

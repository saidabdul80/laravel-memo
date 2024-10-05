<?php

namespace Saidabdulsalam\LaravelMemo\Models;

use Illuminate\Database\Eloquent\Model;

class MemoApprover extends Model
{
    protected $fillable = ['memo_id', 'approver_id', 'status', 'approver_type'];
    protected $with = ['approver'];
    protected $appends = ['comments'];

    public function approver()
    {
        return $this->morphTo();
    }

    public function memos()
    {
        return $this->morphedByMany(Memo::class, 'approver', 'memo_approvers');
    }

    public function getCommentsAttribute(){
        return Comment::where([
            'memo_id'=>$this->memo_id,
            'approver_id'=>$this->approver_id,
            'approver_type'=>$this->approver_type,
        ])->get();
    }

}

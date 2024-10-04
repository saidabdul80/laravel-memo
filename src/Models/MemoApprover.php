<?php

namespace Saidabdulsalam\LaravelMemo\Models;

use Illuminate\Database\Eloquent\Model;

class MemoApprover extends Model
{
    protected $fillable = ['memo_id', 'approver_id', 'status'];

    public function approver()
    {
        return $this->belongsTo(Staffer::class, 'approver_id'); 
    }
}

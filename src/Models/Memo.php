<?php

namespace Saidabdulsalam\LaravelMemo\Models;

use Illuminate\Database\Eloquent\Model;

class Memo extends Model
{
    protected $fillable = ['title', 'type', 'content', 'status', 'user_id'];

    public function approvers()
    {
        return $this->hasMany(MemoApprover::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace Saidabdulsalam\LaravelMemo\Models;

use Illuminate\Database\Eloquent\Model;


use Saidabdulsalam\LaravelMemo\Traits\Filterable;
class Comment extends Model
{
    use Filterable;
    protected $table = "memo_comments";
    protected $fillable = [
        'memo_id',
        'comment',
        'approver_id',
        'approver_type',
        'status',
    ];

    public function memo()
    {
        return $this->belongsTo(Memo::class);
    }

    public function approver()
    {
        return $this->morphTo(); 
    }
}

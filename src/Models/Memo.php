<?php

namespace Saidabdulsalam\LaravelMemo\Models;

use Illuminate\Database\Eloquent\Model;


use Saidabdulsalam\LaravelMemo\Traits\Filterable;
class Memo extends Model
{
    use Filterable;
    protected $fillable = ['title', 'type', 'content', 'status', 'owner_id', 'owner_type'];

    
    public function approvers()
    {
        return $this->hasMany(MemoApprover::class);
    }

    public function owner()
    {
        return $this->morphTo();
    }
}

<?php

namespace Saidabdulsalam\LaravelMemo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Saidabdulsalam\LaravelMemo\Casts\ArrayCast;
use Saidabdulsalam\LaravelMemo\Traits\DateTime;
use Saidabdulsalam\LaravelMemo\Traits\Filterable;
class Comment extends Model
{
    use Filterable, DateTime;
    protected $table = "memo_comments";
    protected $fillable = [
        'memo_id',
        'comment',
        'approver_id',
        'approver_type',
        'department_id',
        'type',
        'status',
    ];
    protected $casts =[
        'department_id'=>ArrayCast::class
    ];

    protected $appends = ['time_at','departments', 'is_owner','full_name'];

    public function memo()
    {
        return $this->belongsTo(Memo::class);
    }

    public function approver()
    {
        return $this->morphTo(); 
    }

    public function getIsOwnerAttribute(){
        $memo = DB::table('memos')->where('id', $this->memo_id)->first();
        return  $memo?->owner_id == $this->approver_id && $memo?->owner_type == $this->approver_type; 
    }

    public function getDepartmentsAttribute(){
        $model = config('memo.department_model');
        if(class_exists($model)){
            return $model::whereIn('id', $this->department_id??[])->pluck('name');
        }
        return [];
    }
 
    public function getFullNameAttribute(){
        return $this->approver->full_name;
    }
}

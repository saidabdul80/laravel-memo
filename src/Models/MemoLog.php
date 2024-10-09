<?php

namespace Saidabdulsalam\LaravelMemo\Models;

use App\Enums\MemoStatus;
use App\Enums\MemoType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Saidabdulsalam\LaravelMemo\Traits\DateTime;

class MemoLog extends Model
{
    use DateTime;
    protected $fillable = ['memo_id', 'approver_id', 'status', 'approver_type'];

    protected $appends = ['time_at', 'full_name'];

    public function approver()
    {
        return $this->morphTo();
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: function($val){
                return MemoStatus::getKey($val);
            },
            set: fn ($value) => $value,
        );
    }
    // public function memos()
    // {
    //     return $this->morphedByMany(Memo::class, 'approver', 'memo_approvers');
    // }

    public function getFullNameAttribute(){
        return $this->approver?->full_name;
    }

}

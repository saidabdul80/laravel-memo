<?php

namespace Saidabdulsalam\LaravelMemo\Models;

use App\Enums\MemoStatus;
use Illuminate\Database\Eloquent\Model;
use Saidabdulsalam\LaravelMemo\Casts\ArrayCast;
use Saidabdulsalam\LaravelMemo\Events\MemoApproved;
use Saidabdulsalam\LaravelMemo\Traits\Filterable;
class Memo extends Model
{
    use Filterable;
    protected $fillable = ['title', 'type', 'content', 'status', 'owner_id', 'owner_type','department_id'];

    protected $casts =[
        'department_id'=>ArrayCast::class
    ];
    protected $appends = ['departments'];

    public static function boot()
    {
        parent::boot();
        static::retrieved(function ($model) {
            $model->updateStatus(true);
            // Add your logic here
            // This code will run whenever a Memo model is retrieved from the database
        });
    
        static::saving(function ($model) {
            $model->updateStatus(false);
            // We need to check if we are updating an existing model
            // if ($model->exists) {
            //     $approvedCount = 0;
            //     $rejectedCount = 0;
    
            //     foreach ($model->approvers as $approver) {
            //         if ($approver->status == MemoStatus::APPROVED) {
            //             $approvedCount++;
            //         } elseif ($approver->status == MemoStatus::REJECTED) {
            //             $rejectedCount++;
            //         }
            //     }
            //     // Determine the status based on approvers' statuses
            //     if ($approvedCount > 0 && $rejectedCount == 0 && $approvedCount + $rejectedCount == count($model->approvers)) {
            //         $model->status = MemoStatus::APPROVED;
            //     } elseif ($rejectedCount > 0 && $approvedCount == 0 && $approvedCount + $rejectedCount == count($model->approvers)) {
            //         $model->status = MemoStatus::REJECTED;
            //     } else {
            //         $model->status = MemoStatus::SUBMITTED;
            //     }
            // }
        });
    }
    
    public function updateStatus($save)
    {
        if ($this->exists) {
            $approvedCount = 0;
            $rejectedCount = 0;

            foreach ($this->approvers as $approver) {
                if ($approver->status == MemoStatus::APPROVED) {
                    $approvedCount++;
                } elseif ($approver->status == MemoStatus::REJECTED) {
                    $rejectedCount++;
                }
            }

            // Determine the status based on approvers' statuses
            if ($approvedCount > 0 && $rejectedCount == 0 && $approvedCount + $rejectedCount == count($this->approvers)) {
                $this->status = MemoStatus::APPROVED;
            } elseif ($rejectedCount > 0 && $approvedCount == 0 && $approvedCount + $rejectedCount == count($this->approvers)) {
                $this->status = MemoStatus::REJECTED;
            } else {
                $this->status = MemoStatus::SUBMITTED;
            }
            if($save){
                $this->save();
            }
        }
    }

    public function approvers()
    {
        return $this->hasMany(MemoApprover::class);
    }

    public function owner()
    {
        return $this->morphTo();
    }

    public function getDepartmentsAttribute(){
        $model = config('memo.department_model');
        if(class_exists($model)){
            return $model::whereIn('id', $this->department_id??[])->pluck('name');
        }
        return [];
    }
}

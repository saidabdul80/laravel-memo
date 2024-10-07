<?php
namespace Saidabdulsalam\LaravelMemo\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Saidabdulsalam\LaravelMemo\Enums\MemoStatus;
use Saidabdulsalam\LaravelMemo\Enums\MemoType;

trait Filterable
{
    public function scopeFilter(Builder $query, $filters)
    {
        

        foreach ($filters as $filter => $value) {
            if (is_null($value)) {
                continue;
            }

            $method = 'filter' . ucfirst(Str::camel($filter));

            if (method_exists($this, $method)) {
                $this->$method($query, $value);
            }
        }

        return $query;
    }

    protected function filterTitle(Builder $query, $title)
    {
        return $query->where('title', 'LIKE', "%$title%");
    }

    protected function filterStatus(Builder $query, $value)
    {
        return $query->where('status', MemoStatus::getValue($value));
    }

    protected function filterMyMemo(Builder $query, $value)
    {
        if (is_array($value)) {
            if ($value[2]) {
                // Not sent by me, but I was assigned to approve, or it is a circular memo
                return $query->where(function ($q) use ($value) {
                    $q->where(function ($subQuery) use ($value) {
                        $subQuery->where('owner_id', '!=', $value[0])
                                 ->where('owner_type', '!=', $value[1]);
                    })
                    ->where('type', MemoType::CIRCULAR)
                    ->orWhereHas('approvers', function ($subQuery) use ($value) {
                        $subQuery->where('approver_id', $value[0])
                                 ->where('approver_type', $value[1]);
                    });
                });
            } else {
                // Memos sent by me
                return $query->where('owner_id', $value[0])
                             ->where('owner_type', $value[1]);
            }
        }
    }
    

    protected function filterContent(Builder $query, $content)
    {
        return $query->where('content', 'LIKE', "%$content%");
    }

    protected function filterOwnerType(Builder $query, $ownerType)
    {
        return $query->where('owner_type', $ownerType);
    }

    protected function filterOwnerId(Builder $query, $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }

    public function filterSent(Builder $query,$val){
        return $query->where('owner_type', $val["owner_type"])->where('owner_id', $val['owner_id']);
    }

    public function filterInbox(Builder $query, $val){
        
        return $query->whereNot(function($q1) use($val){
                        $q1->where('owner_type', $val["owner_type"])
                        ->where('owner_id', $val['owner_id']);
                    })
                    ->where(function($query)use ($val){
                        $query->where("type", MemoType::CIRCULAR)->orWhereHas('approvers',function($q) use($val){
                            $q->where('approver_type', $val["owner_type"])->where('approver_id', $val['owner_id']);
                        });
                    });
    }
}

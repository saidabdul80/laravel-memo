<?php

namespace Saidabdulsalam\LaravelMemo\Http\Resources;

use Saidabdulsalam\LaravelMemo\Enums\MemoType;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'department_id' => $this->department_id,
            'departments' => $this->departments,
            'full_name'=>$this->full_name,
            'is_owner'=>$this->is_owner,
            'type' => MemoType::getKey($this->type),
            'comment' => $this->comment,
            'time_at'=>$this->time_at,
            'approver_id'=>$this->approver_id,
            'approver_type'=>$this->approver_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

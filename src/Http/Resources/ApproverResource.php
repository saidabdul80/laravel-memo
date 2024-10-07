<?php

namespace Saidabdulsalam\LaravelMemo\Http\Resources;

use Saidabdulsalam\LaravelMemo\Enums\MemoStatus;
use Saidabdulsalam\LaravelMemo\Enums\MemoType;
use Illuminate\Http\Resources\Json\JsonResource;

class ApproverResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'approver_id' => $this->approver_id,
            'approver_type' => $this->approver_type,
            'status' => MemoStatus::getKey($this->status),
            'full_name' =>  $this->approver?->full_name,
            'memo_id' => $this->memo_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

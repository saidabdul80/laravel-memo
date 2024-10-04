<?php

namespace Saidabdulsalam\LaravelMemo\Http\Resources;

use Saidabdulsalam\LaravelMemo\Enums\MemoStatus;
use Saidabdulsalam\LaravelMemo\Enums\MemoType;
use Illuminate\Http\Resources\Json\JsonResource;

class MemoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'type' => MemoType::getKey($this->type),
            'content' => $this->content,
            'status' => MemoStatus::getKey($this->status),
            'approvers' => $this->approvers,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

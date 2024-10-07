<?php

namespace Saidabdulsalam\LaravelMemo\Http\Requests;

use Saidabdulsalam\LaravelMemo\Enums\MemoStatus;
use Saidabdulsalam\LaravelMemo\Enums\MemoType;
use Illuminate\Foundation\Http\FormRequest;

class MemoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'department_id'=>'nullable',
            //'type' => 'sometimes|in:' . implode(',', MemoType::getKeys()),
            'content' => 'required|string',
            //'status' => 'sometimes|in:' . implode(',', MemoStatus::getKeys()),
            'approvers' => 'array|nullable'
        ];
    }
}

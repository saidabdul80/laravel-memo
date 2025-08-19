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
        $rules = [
            'department_id' => 'nullable|array',
            'office_id' => 'nullable',
            'approvers' => 'array|nullable'
        ];

    // always validate title/content when present; require them on create
    $rules['title'] = $this->filled('id') ? 'sometimes|required|string|max:255' : 'required|string|max:255';
    $rules['content'] = $this->filled('id') ? 'sometimes|required|string' : 'required|string';

        return $rules;
    }
}

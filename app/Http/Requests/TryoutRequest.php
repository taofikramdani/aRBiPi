<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TryoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['title' => ['required', 'string', 'max:150'], 'subject_id' => ['nullable', 'exists:subjects,id'], 'description' => ['nullable', 'string'], 'duration_minutes' => ['required', 'integer', 'min:5', 'max:300'], 'question_ids' => ['required', 'array', 'min:1'], 'question_ids.*' => ['exists:questions,id'], 'is_published' => ['nullable', 'boolean']];
    }
}

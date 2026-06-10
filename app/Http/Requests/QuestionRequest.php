<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class QuestionRequest extends FormRequest
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
        return ['subject_id' => ['required', 'exists:subjects,id'], 'difficulty' => ['required', 'in:easy,medium,hard'], 'question_text' => ['required', 'string'], 'explanation' => ['nullable', 'string'], 'options' => ['required', 'array', 'size:4'], 'options.*' => ['required', 'string'], 'correct_answer' => ['required', 'in:A,B,C,D']];
    }
}

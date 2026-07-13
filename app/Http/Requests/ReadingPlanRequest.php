<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReadingPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'book_id' => ['required',
                'exists:books,id',
                'integer',
                Rule::unique('reading_plans', 'book_id')->where('user_id', auth()->id()), ],
            'target_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => '書籍を選択してください。',
            'book_id.exists' => '選択された書籍が存在しません。',
            'book_id.unique' => 'この書籍の読書計画はすでに登録されています。',
            'target_date.required' => '期日は必須です。',
            'target_date.date' => '期日は正しい日付形式で入力してください。',
            'target_date.after_or_equal' => '期日は本日以降の日付を指定してください。',
        ];
    }
}

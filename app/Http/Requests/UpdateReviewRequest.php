<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
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
            'rating' => 'sometimes|integer|min:1|max:5',
            'review' => 'sometimes|nullable|string|max:1000',
        ];
    }
    public function messages(): array
    {
        return [
            'rating.integer' => 'Rating must be a number.',
            'rating.min' => 'Minimum rating is 1.',
            'rating.max' => 'Maximum rating is 5.',
            'review.max' => 'Review cannot exceed 1000 characters.',
        ];
    }
}

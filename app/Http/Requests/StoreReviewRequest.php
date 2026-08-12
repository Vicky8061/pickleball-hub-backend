<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
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
            'court_id' => 'required|exists:courts,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ];
    }
    public function messages(): array
    {
        return [

            'court_id.required' => 'Court is required.',
            'court_id.exists' => 'Selected court does not exist.',

            'rating.required' => 'Rating is required.',
            'rating.integer' => 'Rating must be a number.',
            'rating.min' => 'Minimum rating is 1.',
            'rating.max' => 'Maximum rating is 5.',

            'review.string' => 'Review must be text.',
            'review.max' => 'Review cannot exceed 1000 characters.',
        ];
    }
}

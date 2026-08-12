<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCourtImageRequest extends FormRequest
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
            'court_id'=>'required|exists:courts,id',
            'images'=>'required|array|max:5',
            'images.*'=>'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_primary'=>'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return[
            'court_id.required'=> 'Court is required',
            'court_id.exists'=>'Selected court does not exist.',
            'images.required'=> 'Please upload at least one image.',
            'images.array'=> 'Images must be array.',
            'images.max' => 'Maximum 5 images are allowed',
            'images.*.image'=>'Each file must be an image',
            'images.*.mimes'=>'Only JPG,JPEG,PNG and WEBP images are allowed.',
            'images.*.max'=>'Each image size must not exceed 2MB',
        ];
    }
}

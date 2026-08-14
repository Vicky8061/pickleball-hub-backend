<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOwnerApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'business_name' => [
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
                'required',
                'string',
                'max:20',
            ],

            'address' => [
                'required',
                'string',
                'max:1000',
            ],

            'city' => [
                'required',
                'string',
                'max:100',
            ],

            'state' => [
                'required',
                'string',
                'max:100',
            ],

            'pincode' => [
                'required',
                'string',
                'max:10',
            ],

            'experience' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'description' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'document' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'business_name.required' => 'Business name is required.',
            'business_name.string' => 'Business name must be a valid text.',
            'business_name.max' => 'Business name cannot exceed 255 characters.',

            'phone.required' => 'Phone number is required.',
            'phone.max' => 'Phone number cannot exceed 20 characters.',

            'address.required' => 'Business address is required.',
            'address.max' => 'Address cannot exceed 1000 characters.',

            'city.required' => 'City is required.',
            'state.required' => 'State is required.',
            'pincode.required' => 'Pincode is required.',

            'experience.max' => 'Experience cannot exceed 2000 characters.',
            'description.max' => 'Description cannot exceed 3000 characters.',

            'document.required' => 'Verification document is required.',
            'document.file' => 'The document must be a valid file.',
            'document.mimes' => 'Document must be a PDF, JPG, JPEG, or PNG file.',
            'document.max' => 'Document size cannot exceed 5 MB.',
        ];
    }
}

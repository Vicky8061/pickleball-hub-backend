<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTimeSlotRequest extends FormRequest
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
            'start_time'=>'required|date_format:H:i',
            'end_time'=>'required|date_format:H:i|after:start_time',
        ];

    }

    public function messages(): array
    {
        return[
            'court_id.required'=>'Court is required',
            'court_id.exists'=> 'Selected court does not exist.',
            'start_time.required'=>'Start time is required',
            'start_time.date_format'=>'Start time must be in HH:MM format',
            'end_time.required'=>'End time is required',
            'end_time.date_format'=>'End time must be in HH:MM format',
            'end_time.after'=>'End time must be after start time',

        ];
    }
}

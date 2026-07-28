<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTimeSlotRequest extends FormRequest
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
            'court_id'=> 'required|exists:courts,id',
            'start_time'=> 'required|date_format:H:i',
            'end_time'=> 'required|date_format:H:i|after:start_time',
            'status'=> 'required|in:active,inactive',
        ];
    }
}

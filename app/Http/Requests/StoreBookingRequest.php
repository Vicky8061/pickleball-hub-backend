<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
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
            'time_slot_id'=> 'required|exists:time_slots,id',
            'booking_date'=> 'required|date|after_or_equal:today',
        ];
    }
    public function messages(): array
    {
        return [
            'court_id.required'=> 'Court is required',
            'court_id.exists'=> 'Court does not exist',
            'time_slot_id.required'=> 'Time slot is required',
            'time_slot_id.exists'=> 'Selected Time slot does not exist',
            'booking_date.required'=> 'Booking date is required',
            'booking_date.date'=> 'Booking date must be a valid date',
            'booking_date.after_or_equal'=> 'Booking date must be today or a future date',
        ];
    }
}

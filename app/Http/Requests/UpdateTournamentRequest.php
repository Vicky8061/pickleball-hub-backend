<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTournamentRequest extends FormRequest
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
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'banner' => 'sometimes|nullable|string|max:255',
            'tournament_date' => 'sometimes|date|after_or_equal:today',
            'registration_last_date'
            => 'sometimes|date|before_or_equal:tournament_date',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
            'entry_fee' => 'sometimes|numeric|min:0',
            'max_participants' => 'sometimes|integer|min:2',
            'prize' => 'sometimes|nullable|string|max:255',
            'status' => 'sometimes|in:upcoming,ongoing,completed,cancelled',
        ];
    }
    public function messages(): array
    {
        return [

            'title.max' => 'Title cannot exceed 255 characters.',

            'tournament_date.after_or_equal' =>
            'Tournament date cannot be in the past.',

            'registration_last_date.before_or_equal' =>
            'Registration last date must be before or on the tournament date.',

            'start_time.date_format' =>
            'Start time must be in HH:MM format.',

            'end_time.date_format' =>
            'End time must be in HH:MM format.',

            'end_time.after' =>
            'End time must be after start time.',

            'entry_fee.min' =>
            'Entry fee cannot be negative.',

            'max_participants.min' =>
            'At least 2 participants are required.',

            'status.in' =>
            'Invalid tournament status.',
        ];
    }
}

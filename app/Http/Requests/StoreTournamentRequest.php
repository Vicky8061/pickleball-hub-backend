<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTournamentRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'banner' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'tournament_date' => 'required|date|after_or_equal:today',
            'registration_last_date' =>
            'required|date|after_or_equal:today|before_or_equal:tournament_date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'entry_fee' => 'required|numeric|min:0',
            'max_participants' => 'required|integer|min:2',
            'prize' => 'nullable|string|max:255',
        ];
    }
    public function messages(): array
    {
        return [
            'court_id.required' => 'Court is required.',
            'court_id.exists' => 'Selected court does not exist.',

            'title.required' => 'Tournament title is required.',

            'tournament_date.after_or_equal' =>
            'Tournament date cannot be in the past.',

            'registration_last_date.before_or_equal' =>
            'Registration last date must be before or on the tournament date.',

            'start_time.date_format' =>
            'Start time must be in HH:MM format.',

            'end_time.date_format' =>
            'End time must be in HH:MM format.',

            'end_time.after' =>
            'End time must be after the start time.',

            'max_participants.min' =>
            'At least 2 participants are required.',
        ];
    }
}

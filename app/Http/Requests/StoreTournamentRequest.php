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
            'court_id'=> 'required|exists:courts,id',
            'title'=> 'required|string|max:255',
            'description'=> 'nullable|string',
            'banner'=> 'nullable|string',
            'tournament_date'=> 'required|date',
            'registration_last_date'=> 'required|date|before_or_equal:tournament_date',
            'start_time'=> 'required',
            'end_time'=> 'required|after:start_time',
            'entry_fee'=> 'required|numeric|min:0',
            'max_participants'=> 'required|integer|min:2',
            'prize'=> 'nullable|string|max:255',
        ];
    }
}

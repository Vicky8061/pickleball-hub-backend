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
            'title'=> 'sometimes|string|max:255',
            'description'=> 'nullable|string',
            'banner'=> 'nullable|string',
            'tournament_date'=> 'sometimes|date',
            'registration_last_date'=> 'sometimes|date',
            'start_time'=> 'sometimes',
            'end_time'=> 'sometimes|after:start_time',
            'entry_fee'=> 'sometimes|numeric|min:0',
            'max_participants'=> 'sometimes|integer|min:2',
            'prize'=> 'nullable|string|max:255',
            'status'=> 'sometimes|in:upcoming,ongoing,completed,cancelled',
        ];
    }
}

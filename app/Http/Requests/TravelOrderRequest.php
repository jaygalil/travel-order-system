<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TravelOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'workflow_template_id' => 'nullable|exists:workflow_templates,id',
            'date_of_travel_from' => 'required|date|after_or_equal:today',
            'date_of_travel_to' => 'required|date|after_or_equal:date_of_travel_from',
            'source_of_fund' => 'required|string|max:255',
            'official_vehicle' => 'nullable|string|max:255',
            'purpose' => 'required|string',
            'destination' => 'required|string',
            'farthest_destination' => 'required|string|max:255',
            'approx_distance' => 'required|numeric|min:0|max:9999.99',
            
            // Participants validation
            'participants' => 'required|array|min:1',
            'participants.*.employee_name' => 'required|string|max:255',
            'participants.*.position' => 'required|string|max:255',
            'participants.*.division_agency' => 'required|string|max:255',
            'participants.*.employee_id' => 'nullable|string|max:100',
            'participants.*.phone' => 'nullable|string|max:20',
            'participants.*.email' => 'nullable|email|max:255',
            'participants.*.is_primary' => 'required|boolean',
            'participants.*.user_id' => 'nullable|exists:users,id',
            'participants.*.special_requirements' => 'nullable|string',
        ];
    }

    /**
     * Get the validation messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            'date_of_travel_from.after_or_equal' => 'Travel start date must be today or a future date.',
            'date_of_travel_to.after_or_equal' => 'Travel end date must be on or after the start date.',
            'approx_distance.numeric' => 'Approximate distance must be a valid number.',
            'approx_distance.max' => 'Approximate distance cannot exceed 9999.99 km.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'date_of_travel_from' => 'travel start date',
            'date_of_travel_to' => 'travel end date',
            'employee_name' => 'employee name',
            'division_agency' => 'division/agency',
            'source_of_fund' => 'source of fund',
            'official_vehicle' => 'official vehicle',
            'farthest_destination' => 'farthest destination',
            'approx_distance' => 'approximate distance',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\TravelOrder;

class StoreTravelOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'purpose' => 'required|string|max:1000',
            'destination' => 'required|string|max:255',
            'farthest_destination' => 'nullable|string|max:255',
            'date_of_travel_from' => 'required|date|after_or_equal:today',
            'date_of_travel_to' => 'required|date|after_or_equal:date_of_travel_from',
            'approx_distance' => 'nullable|numeric|min:0|max:99999.99',
            'source_of_fund' => 'nullable|string|max:255',
            'official_vehicle' => 'nullable|in:Yes,No',
            'employee_name' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'division_agency' => 'nullable|string|max:255',
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages()
    {
        return [
            'purpose.required' => 'The purpose of travel is required.',
            'purpose.max' => 'The purpose cannot exceed 1000 characters.',
            'destination.required' => 'The destination is required.',
            'date_of_travel_from.required' => 'The departure date is required.',
            'date_of_travel_from.after_or_equal' => 'The departure date cannot be in the past.',
            'date_of_travel_to.required' => 'The return date is required.',
            'date_of_travel_to.after_or_equal' => 'The return date must be on or after the departure date.',
            'approx_distance.numeric' => 'The distance must be a valid number.',
            'approx_distance.max' => 'The distance cannot exceed 99,999.99 km.',
            'official_vehicle.in' => 'Please select Yes or No for official vehicle.',
        ];
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation()
    {
        // Auto-fill employee details if not provided
        if (auth()->check()) {
            $user = auth()->user();
            
            $this->merge([
                'employee_name' => $this->employee_name ?? $user->name,
                'position' => $this->position ?? $user->position,
                'division_agency' => $this->division_agency ?? $user->division_agency,
                'user_id' => $user->id,
                'prepared_by_user_id' => $user->id,
            ]);
        }
    }

    /**
     * Get validated data with defaults
     */
    public function getValidatedDataWithDefaults()
    {
        $validated = $this->validated();
        
        // Add auto-generated fields
        $validated['local_travel_order_no'] = TravelOrder::generateTravelOrderNumber();
        $validated['status'] = TravelOrder::STATUS_DRAFT;
        $validated['user_id'] = auth()->id();
        $validated['prepared_by_user_id'] = auth()->id();
        
        // Set defaults for optional fields
        $validated['source_of_fund'] = $validated['source_of_fund'] ?? 'MOOE';
        $validated['official_vehicle'] = $validated['official_vehicle'] ?? 'No';
        $validated['approx_distance'] = $validated['approx_distance'] ?? 0;
        
        // Auto-fill employee details
        $user = auth()->user();
        $validated['employee_name'] = $validated['employee_name'] ?? $user->name;
        $validated['position'] = $validated['position'] ?? $user->position ?? 'Employee';
        $validated['division_agency'] = $validated['division_agency'] ?? $user->division_agency ?? 'DICT';
        
        return $validated;
    }
}

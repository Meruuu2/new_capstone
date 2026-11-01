<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFacilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                => 'required|string|max:255',
            'location'            => 'required|string|max:255',
            'owner_type'          => 'required|in:gsu,college,org',
            'owner_college'       => 'nullable|string|max:255',
            'description'         => 'nullable|string',
            'is_active'           => 'boolean',
            'availability_status' => 'nullable|in:available,unavailable,maintenance',
        ];
    }
}

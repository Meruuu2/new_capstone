<?php

namespace App\Http\Requests;

use App\Rules\OperatingHours;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'facility_id'   => 'required|exists:facilities,id',
            'date_activity' => 'required|date',
            'start_time'    => ['required', 'date_format:H:i', new OperatingHours],
            'end_time'      => ['required', 'date_format:H:i', 'after:start_time', new OperatingHours],
            'purpose'       => 'required|string|max:500',
            'reason'        => 'required|string|max:500',
            'qty_monobloc'  => 'nullable|integer|min:0',
            'qty_table'     => 'nullable|integer|min:0',
            'qty_fan'       => 'nullable|integer|min:0',
            'qty_rostrum'   => 'nullable|integer|min:0',
            'qty_flag'      => 'nullable|integer|min:0',
            'qty_sound'     => 'nullable|integer|min:0',
            'qty_led'       => 'nullable|integer|min:0',
        ];
    }
}

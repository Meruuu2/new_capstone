<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Booking;
use App\Models\Facility;
use App\Rules\OperatingHours;
use App\Rules\PersonName;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreFacilitiesUtilizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->routeIs('public.request.store') && $this->user()) {
            $this->merge(['last_name' => $this->user()->last_name]);
        }

        if (! $this->exists('facility_ids') && $this->filled('facility_id')) {
            $this->merge(['facility_ids' => [$this->input('facility_id')]]);
        }
    }

    public function rules(): array
    {
        return [
            'last_name' => ['required', 'string', 'max:120', new PersonName],
            'date_activity' => 'required|date|after_or_equal:' . now()->addDays(30)->toDateString(),
            // Optional last day for multi-day events (same start/end time every day), up to 14 days in total
            'date_end'      => [
                'nullable',
                'date',
                'after_or_equal:date_activity',
                function ($attribute, $value, $fail) {
                    $start = $this->input('date_activity');
                    if ($start && $value && Carbon::parse($start)->diffInDays(Carbon::parse($value)) > 13) {
                        $fail('A multi-day event can last at most 14 days.');
                    }
                },
            ],
            'start_time'    => ['required', 'date_format:H:i', new OperatingHours],
            'end_time'      => ['required', 'date_format:H:i', 'after:start_time', new OperatingHours],
            'facility_ids.*' => ['required', 'integer', 'distinct', 'exists:facilities,id'],
            'facility_ids' => [
                'required',
                'array',
                'min:1',
                function ($attribute, $value, $fail) {
                    if (! is_array($value) || count($value) !== count(array_unique($value))) {
                        return;
                    }

                    $facilities = Facility::whereIn('id', $value)->get();
                    if ($facilities->count() !== count($value)) {
                        return;
                    }

                    foreach ($facilities as $facility) {
                        if (! $facility->isAvailable()) {
                            $fail($facility->name . ' is currently unavailable for requests.');
                            return;
                        }
                    }
                }
            ],
            'purpose'       => 'required|string|max:500',
            'qty_monobloc'  => 'nullable|integer|min:0',
            'qty_table'     => 'nullable|integer|min:0',
            'qty_fan'       => 'nullable|integer|min:0',
            'qty_rostrum'   => 'nullable|integer|min:0',
            'qty_flag'      => 'nullable|integer|min:0',
            'qty_sound'     => 'nullable|integer|min:0',
            'qty_led'       => 'nullable|integer|min:0',
            'venue_others'  => 'nullable|string|max:255',

            // Noted by (signatory)
            'noted_signatory_id' => 'nullable|string',
            'noted_signatory_custom' => 'nullable|string|max:255',

            // Receipt and supporting documents
            'payment_attachment' => [
                'nullable',
                Rule::prohibitedIf(fn () => $this->routeIs('public.request.store')),
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'mimetypes:image/jpeg,image/png,application/pdf',
                'max:10240',
            ],
            'attachments' => 'nullable|array|max:10',
            'attachments.*' => [
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'mimetypes:image/jpeg,image/png,application/pdf',
                'max:10240',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'last_name.required' => 'Your account needs a separately recorded last name before you can submit a request. Ask your unit administrator to update your account.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $data = $validator->getData();
            foreach (['date_activity', 'date_end', 'start_time', 'end_time', 'facility_ids'] as $field) {
                if ($validator->errors()->has($field)) {
                    return;
                }
            }
            foreach ($validator->errors()->keys() as $errorKey) {
                if (str_starts_with($errorKey, 'facility_ids.')) {
                    return;
                }
            }

            $facilityIds = array_map('intval', $data['facility_ids']);
            $startDay = Carbon::parse($data['date_activity'])->startOfDay();
            $endDay = Carbon::parse($data['date_end'] ?? $data['date_activity'])->startOfDay();

            for ($day = $startDay->copy(); $day->lte($endDay); $day->addDay()) {
                $slotStart = Carbon::parse($day->toDateString() . ' ' . $data['start_time']);
                $slotEnd = Carbon::parse($day->toDateString() . ' ' . $data['end_time']);
                $conflict = Booking::query()
                    ->whereHas('facilities', fn ($query) => $query->whereIn('facilities.id', $facilityIds))
                    ->whereIn('status', ['reserved', 'rescheduled', 'booked'])
                    ->where('start_time', '<', $slotEnd)
                    ->where('end_time', '>', $slotStart)
                    ->with('facilities:id,name')
                    ->first();

                if ($conflict) {
                    $facility = $conflict->facilities->first(fn ($facility) => in_array((int) $facility->id, $facilityIds, true));
                    $facilityName = $facility?->name ?? 'A selected facility';
                    $validator->errors()->add(
                        'start_time',
                        "{$facilityName} is reserved from {$conflict->start_time->format('g:i A')} to {$conflict->end_time->format('g:i A')} on {$day->format('M j, Y')}. Choose a time that does not overlap."
                    );
                    return;
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'date_activity' => 'date of activity',
            'date_end'      => 'end date',
            'payment_attachment' => 'payment receipt',
            'attachments.*' => 'supporting file',
        ];
    }
}
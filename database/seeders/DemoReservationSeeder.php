<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Facility;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DemoReservationSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            [
                'code' => 'DEMO-CCIS-001',
                'email' => 'college@ccis.com',
                'facility' => 'ICT AVR',
                'title' => 'Student research symposium',
                'status' => 'pending',
                'days_after_month' => 0,
                'start' => '09:00',
                'end' => '12:00',
            ],
            [
                'code' => 'DEMO-CEA-001',
                'email' => 'college@cea.com',
                'facility' => 'CEA AVR',
                'title' => 'Engineering design exhibit',
                'status' => 'approved',
                'days_after_month' => 2,
                'start' => '13:00',
                'end' => '16:00',
            ],
            [
                'code' => 'DEMO-CMG-001',
                'email' => 'college@cmg.com',
                'facility' => 'CBA AVR',
                'title' => 'Business leadership seminar',
                'status' => 'reserved',
                'days_after_month' => 5,
                'start' => '09:00',
                'end' => '12:00',
                'booking_status' => 'reserved',
            ],
            [
                'code' => 'DEMO-GSU-001',
                'email' => 'adminA@example.com',
                'facility' => 'BUSALIAN HALL',
                'title' => 'University-wide orientation',
                'status' => 'reserved',
                'days_after_month' => 8,
                'start' => '08:00',
                'end' => '12:00',
                'booking_status' => 'reserved',
            ],
            [
                'code' => 'DEMO-PCCIS-001',
                'email' => 'provincial.ccis@example.com',
                'facility' => 'E-HUB',
                'title' => 'Provincial technology meet-up',
                'status' => 'pending_payment',
                'days_after_month' => 11,
                'start' => '13:00',
                'end' => '16:00',
            ],
            [
                'code' => 'DEMO-PSITS-001',
                'email' => 'provincial.psits@example.com',
                'facility' => 'GRAND STAND',
                'title' => 'PSITS sports and fellowship day',
                'status' => 'cancelled',
                'days_after_month' => 14,
                'start' => '08:00',
                'end' => '11:00',
                'booking_status' => 'cancelled',
            ],
            [
                'code' => 'DEMO-CCIS-002',
                'email' => 'staff.ccis@example.com',
                'facility' => 'ICT AVR',
                'title' => 'Cybersecurity awareness workshop',
                'status' => 'disapproved',
                'days_after_month' => 17,
                'start' => '13:00',
                'end' => '16:00',
            ],
            [
                'code' => 'DEMO-CEA-002',
                'email' => 'staff.cea@example.com',
                'facility' => 'CEA AVR',
                'title' => 'Student organization planning meeting',
                'status' => 'cancelled',
                'days_after_month' => 20,
                'start' => '09:00',
                'end' => '11:00',
            ],
            [
                'code' => 'DEMO-CMG-002',
                'email' => 'staff.cmg@example.com',
                'facility' => 'CBA AVR',
                'title' => 'Entrepreneurship training workshop',
                'status' => 'reserved',
                'days_after_month' => 23,
                'start' => '13:00',
                'end' => '16:00',
                'booking_status' => 'rescheduled',
            ],
            [
                'code' => 'DEMO-GSU-002',
                'email' => 'adminB@example.com',
                'facility' => 'COVERED GYM',
                'title' => 'Inter-college cultural festival',
                'status' => 'reserved',
                'days_after_month' => 26,
                'start' => '08:00',
                'end' => '16:00',
                'booking_status' => 'reserved',
            ],
            [
                'code' => 'DEMO-CMG-003',
                'email' => 'college@cmg.com',
                'facility' => 'CBA AVR',
                'title' => 'Completed sample faculty consultation',
                'status' => 'reserved',
                'days_after_month' => null,
                'days_before_today' => 7,
                'start' => '10:00',
                'end' => '12:00',
                'booking_status' => 'completed',
            ],
        ];

        $users = User::whereIn('email', array_column($events, 'email'))->get()->keyBy('email');
        $facilities = Facility::whereIn('name', array_unique(array_column($events, 'facility')))
            ->get()
            ->keyBy('name');

        foreach ($events as $event) {
            $user = $users->get($event['email']);
            $facility = $facilities->get($event['facility']);

            if (! $user || ! $facility) {
                throw new RuntimeException("Demo reservation {$event['code']} is missing its seeded requester or facility.");
            }

            $activityDate = isset($event['days_before_today'])
                ? now()->subDays($event['days_before_today'])->toDateString()
                : now()->startOfDay()->addMonth()->addDays($event['days_after_month'])->toDateString();
            $unit = $user->college_name ?? $user->organization_name ?? 'GSU';
            $requesterType = match ($user->role) {
                'college_staff' => 'college',
                'org_staff' => 'org',
                default => 'admin',
            };
            $bookingCode = 'BK-' . $event['code'];

            $payload = [
                'control_no' => $event['code'],
                'date_request' => now()->toDateString(),
                'requester_name' => $user->name,
                'requester_unit' => $unit,
                'date_activity' => $activityDate,
                'date_end' => null,
                'time_range' => ['start' => $event['start'], 'end' => $event['end']],
                'facility_id' => $facility->id,
                'facility_ids' => [$facility->id],
                'facility_name' => $facility->name,
                'facility_names' => [$facility->name],
                'venue_others' => '',
                'purpose' => $event['title'],
                'equipment' => [
                    'monobloc_chair' => 0,
                    'table' => 0,
                    'electric_fan' => 0,
                    'rostrum' => 0,
                    'flag' => 0,
                    'sound' => 0,
                    'led' => 0,
                ],
            ];

            if (isset($event['booking_status'])) {
                $payload['booking_code'] = $bookingCode;
            }

            $submission = FormSubmission::query()
                ->where('type', 'facilities_utilization')
                ->where('requester_id', $user->id)
                ->get()
                ->first(fn (FormSubmission $item) => ($item->payload['control_no'] ?? null) === $event['code']);

            $submission ??= new FormSubmission;
            $submission->fill([
                'type' => 'facilities_utilization',
                'requester_id' => $user->id,
                'requester_type' => $requesterType,
                'requester_unit' => $unit,
                'status' => $event['status'],
                'payload' => $payload,
                'payment_attachment' => null,
                'payment_status' => $event['status'] === 'pending_payment' ? 'pending_payment' : 'not_required',
            ]);
            $submission->save();

            if (! isset($event['booking_status'])) {
                continue;
            }

            $startTime = "{$activityDate} {$event['start']}:00";
            $endTime = "{$activityDate} {$event['end']}:00";
            $booking = Booking::updateOrCreate(
                ['booking_code' => $bookingCode],
                [
                    'requester_id' => $user->id,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'requester_type' => $requesterType,
                    'requester_unit' => $unit,
                    'status' => $event['booking_status'],
                    'request_method' => 'online form',
                    'purpose' => $event['title'],
                    'additional_details' => ['demo_seed' => true],
                    'requested_at' => now(),
                    'approved_at' => in_array($event['booking_status'], ['reserved', 'rescheduled', 'completed'], true)
                        ? now()
                        : null,
                ]
            );
            $booking->facilities()->sync([$facility->id]);
        }
    }
}

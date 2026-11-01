<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FormSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',           // e.g. 'facilities_utilization', 'repair_maintenance'
        'requester_id',
        'requester_type', // 'college', 'org', 'admin'
        'requester_unit', // e.g. college_name or organization_name
        'status',         // 'pending', 'approved', 'disapproved', 'cancelled', 'reserved', 'pending_payment'
        'payload',        // JSON string
        'payment_attachment',
        'payment_status', // 'not_required', 'pending_payment', 'payment_uploaded', 'payment_verified'
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isFacilitiesUtilization(): bool
    {
        return $this->type === 'facilities_utilization';
    }

    public function markApproved(): void
    {
        $this->status = 'approved';
        $this->save();
    }

    public function markDisapproved(): void
    {
        $this->status = 'disapproved';
        $this->save();
    }

    public function markBooked(): void
    {
        $this->status = 'reserved';
        $this->save();
    }

    // Backward-compat (if older code still references converted)
    public function markConverted(): void
    {
        $this->status = 'reserved';
        $this->save();
    }


    public function markCancelled(): void
    {
        $this->status = 'cancelled';
        $this->save();
    }

    public function isCancellableOnAccountDeactivation(Carbon $at): bool
    {
        if (in_array($this->status, ['cancelled', 'disapproved', 'completed', 'failed'], true)) {
            return false;
        }

        $payload = $this->payload ?? [];
        $date = $payload['date_end'] ?? $payload['date_activity'] ?? null;
        if (! is_string($date) || ! Carbon::hasFormat($date, 'Y-m-d')) {
            return false;
        }

        $endTime = $payload['time_range']['end'] ?? null;
        if ($endTime === null || $endTime === '') {
            return Carbon::parse($date)->endOfDay()->gt($at);
        }
        if (! is_string($endTime) || ! Carbon::hasFormat($endTime, 'H:i')) {
            return false;
        }

        return Carbon::parse($date . ' ' . $endTime)->gt($at);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function requiresPayment(): bool
    {
        return $this->payment_status !== 'not_required';
    }

    public function hasPaymentUploaded(): bool
    {
        return $this->payment_status === 'payment_uploaded' || $this->payment_status === 'payment_verified';
    }

    public function isPaymentVerified(): bool
    {
        return $this->payment_status === 'payment_verified';
    }

    public function markPendingPayment(): void
    {
        $this->payment_status = 'pending_payment';
        $this->status = 'pending_payment';
        $this->save();
    }

    public function markPaymentVerified(): void
    {
        $this->payment_status = 'payment_verified';
        $this->save();
    }

    /** Display values for the facility request ticket (pop-up and downloaded PDF). */
    /** Validation for the free-text "Other equipment" rows every request form submits. */
    public const OTHER_EQUIPMENT_RULES = [
        'other_equipment'        => 'nullable|array|max:10',
        'other_equipment.*.name' => 'nullable|string|max:100',
        'other_equipment.*.qty'  => 'nullable|integer|min:1|max:10000',
    ];

    public const EQUIPMENT_LABELS = [
        'monobloc_chair' => 'Monobloc chair', 'table' => 'Table', 'electric_fan' => 'Electric fan', 'rostrum' => 'Rostrum',
        'flag' => 'Flag & school color', 'sound' => 'Sound system', 'led' => 'LED wall',
    ];

    /**
     * Clean "Other equipment" rows from request input: blank names are dropped, a missing quantity counts as 1.
     *
     * @return array<int, array{name: string, qty: int}>
     */
    public static function otherEquipmentFrom(array $input): array
    {
        return collect($input['other_equipment'] ?? [])
            ->filter(fn ($row) => is_array($row))
            ->map(fn ($row) => ['name' => trim((string) ($row['name'] ?? '')), 'qty' => max(1, (int) ($row['qty'] ?? 1))])
            ->filter(fn ($row) => $row['name'] !== '')
            ->values()
            ->all();
    }

    /**
     * Listed + "other" equipment from a payload or booking details, as [label, qty] rows with qty > 0.
     */
    public static function equipmentItems(array $data): Collection
    {
        $listed = collect($data['equipment'] ?? [])->filter(fn ($q) => is_numeric($q) && (int) $q > 0)
            ->map(fn ($q, $k) => ['label' => self::EQUIPMENT_LABELS[$k] ?? ucfirst(str_replace('_', ' ', $k)), 'qty' => (int) $q]);
        $others = collect($data['equipment_others'] ?? [])
            ->map(fn ($row) => ['label' => (string) ($row['name'] ?? ''), 'qty' => (int) ($row['qty'] ?? 0)])
            ->filter(fn ($row) => $row['label'] !== '' && $row['qty'] > 0);

        return $listed->values()->concat($others->values());
    }

    public function ticketData(): array
    {
        $p = $this->payload ?? [];
        // Ticket code: FM-<year submitted>-<request no.>, the format the landing page tracker takes
        $ticketNo = 'FM-' . ($this->created_at?->format('Y') ?? now()->format('Y')) . '-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);

        $statusMeta = [
            'pending'         => ['Pending review',         'is-warn', 'GSU will review your request. You\'ll get a notification when it\'s approved.'],
            'pending_payment' => ['Payment being verified', 'is-warn', 'GSU is checking your payment receipt before reviewing the request.'],
            'approved'        => ['Approved',               'is-good', 'GSU approved your request. It will appear on the calendar once reserved.'],
            'reserved'        => ['Reserved',               'is-good', 'Your venue is reserved and on the calendar.'],
            'disapproved'     => ['Disapproved',            'is-bad',  'GSU did not approve this request.'],
            'cancelled'       => ['Cancelled',              'is-muted', 'This request was cancelled.'],
        ];
        [$statusLabel, $statusTone, $statusNote] = $statusMeta[$this->status] ?? [ucfirst(str_replace('_', ' ', (string) $this->status)), 'is-muted', ''];

        $parse = function ($d) { try { return $d ? Carbon::parse($d) : null; } catch (\Throwable $e) { return null; } };
        $start = $parse($p['date_activity'] ?? null);
        $end   = $parse($p['date_end'] ?? null);
        if ($start && $end && $end->gt($start)) {
            $days      = $start->diffInDays($end) + 1;
            $dateLabel = $start->format('D, M j') . ' → ' . $end->format('D, M j, Y');
            $dateSub   = $days . ' days · same time each day';
        } else {
            $dateLabel = $start ? $start->format('l, F j, Y') : '—';
            $dateSub   = null;
        }
        $clock = function ($t) { try { return $t ? Carbon::createFromFormat('H:i', substr($t, 0, 5))->format('g:i A') : ''; } catch (\Throwable $e) { return $t; } };
        $tr        = $p['time_range'] ?? [];
        $timeLabel = trim($clock($tr['start'] ?? '') . ' – ' . $clock($tr['end'] ?? ''), ' –') ?: '—';

        $venue = collect($p['facility_names'] ?? [$p['facility_name'] ?? null, $p['facility_name_custom'] ?? null])
            ->filter()
            ->unique()
            ->values()
            ->join(', ') ?: '—';

        $equipment = self::equipmentItems($p)->map(fn ($item) => $item['qty'] . ' × ' . $item['label']);

        $requester = trim(($p['requester_name'] ?? '') . (!empty($p['requester_unit']) ? ' · ' . $p['requester_unit'] : '')) ?: '—';
        $notedBy = ($p['noted_signatory_name'] ?? '') ? $p['noted_signatory_name'] . (!empty($p['noted_signatory_position']) ? ' · ' . $p['noted_signatory_position'] : '') : '—';

        return compact('p', 'ticketNo', 'statusLabel', 'statusTone', 'statusNote', 'dateLabel', 'dateSub', 'timeLabel', 'venue', 'equipment', 'requester', 'notedBy');
    }
}
 
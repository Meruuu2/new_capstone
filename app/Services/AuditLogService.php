<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    private const REDACTED_FIELDS = [
        'address',
        'body',
        'contact_number',
        'email',
        'issue_description',
        'password',
        'payment_attachment',
        'remember_token',
        'staff_signature',
    ];

    private const REDACTED_PAYLOAD_FIELDS = [
        'address',
        'client_name',
        'contact_number',
        'email',
        'last_name',
        'noted_signatory_name',
        'requester_last_name',
        'requester_name',
    ];

    public function record(
        string $action,
        Model $subject,
        array $oldValues = [],
        array $newValues = [],
        ?int $actorId = null
    ): void {
        $request = request();
        $isPublicRequest = $request->routeIs('public.request.*');

        AuditLog::create([
            'actor_id' => $actorId ?? ($isPublicRequest ? null : Auth::id()),
            'action' => $action,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'old_values' => $this->sanitize($oldValues),
            'new_values' => $this->sanitize($newValues),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'route' => $request->route()?->getName(),
        ]);
    }

    private function sanitize(array $values): array
    {
        $sanitized = [];

        foreach ($values as $key => $value) {
            $field = strtolower((string) $key);
            if ($field === 'payload') {
                $payload = is_string($value) ? json_decode($value, true) : $value;
                $sanitized[$key] = is_array($payload) ? $this->sanitizePayload($payload) : '[REDACTED]';
            } else {
                $sanitized[$key] = in_array($field, self::REDACTED_FIELDS, true) ? '[REDACTED]' : $value;
            }
        }

        return $sanitized;
    }

    private function sanitizePayload(array $payload): array
    {
        foreach ($payload as $key => $value) {
            $field = strtolower((string) $key);
            if (in_array($field, self::REDACTED_PAYLOAD_FIELDS, true)) {
                $payload[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $payload[$key] = $this->sanitizePayload($value);
            }
        }

        return $payload;
    }
}

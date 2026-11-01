<?php

namespace App\Support;

class VenueRates
{
    public static function all(): array
    {
        return config('venue-rates', []);
    }

    public static function estimatedSeatingCapacityFor(string $facilityName): ?string
    {
        $normalize = static fn (string $name): string => preg_replace('/[^a-z0-9]+/', '', strtolower(trim($name)));
        $normalizedName = $normalize($facilityName);

        foreach (self::all() as $venue) {
            if ($normalize($venue['name']) !== $normalizedName) {
                continue;
            }

            $capacity = trim((string) ($venue['capacity'] ?? ''));

            return $capacity === '' ? null : ($capacity === '-' ? 'No fixed seating capacity' : $capacity);
        }

        return null;
    }
}

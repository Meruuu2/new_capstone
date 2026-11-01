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

    /** Upper bound of the listed seating ("500-600 persons" -> 600); 0 when the venue has no fixed seating. */
    public static function seatingCapacityFor(string $facilityName): int
    {
        preg_match_all('/\d+/', (string) self::estimatedSeatingCapacityFor($facilityName), $numbers);

        return $numbers[0] ? max(array_map('intval', $numbers[0])) : 0;
    }

    /**
     * Equipment the venue's package lists (e.g. "10 wooden long tables", "4 Industrial Fan"), keyed by the
     * request form's qty_* inputs. Used only to pre-fill suggestions; the requester can change them.
     */
    public static function suggestedEquipmentFor(string $facilityName): array
    {
        $normalize = static fn (string $name): string => preg_replace('/[^a-z0-9]+/', '', strtolower(trim($name)));
        $venue = collect(self::all())->first(fn ($venue) => $normalize($venue['name']) === $normalize($facilityName));
        // Every venue gets a sound system; the big event venues also get a rostrum
        $rostrumVenues = ['paghiusahall', 'grandstand', 'coveredgym', 'tiripunanhall'];
        $suggested = [
            'qty_table' => 0,
            'qty_fan' => 0,
            'qty_rostrum' => in_array($normalize($facilityName), $rostrumVenues, true) ? 1 : 0,
            'qty_sound' => 1,
        ];

        foreach ($venue['package'] ?? [] as $item) {
            if (preg_match('/^(\d+)\s.*tables?/i', $item, $m)) {
                $suggested['qty_table'] += (int) $m[1];
            } elseif (preg_match('/^(\d+)\s.*fan/i', $item, $m)) {
                $suggested['qty_fan'] += (int) $m[1];
            }
        }

        return $suggested;
    }
}

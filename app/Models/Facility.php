<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'owner_type',
        'owner_college',
        'college_id',
        'description',
        'image_path',
        'is_active',
        'availability_status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** Bundled photos for the original venues, matched on the facility name. */
    private const BUNDLED_PHOTOS = [
        'busalian'   => 'busalian-hall.jpg',
        'paghiusa'   => 'paghiusa-hall.jpg',
        'tiripunan'  => 'tiripunan-hall.jpg',
        'grandstand' => 'grand-stand.jpg',
        'trackoval'  => 'grand-stand.jpg',
        'newavr'     => 'new-avr.jpg',
        'avr'        => 'avr.jpg',
    ];

    /**
     * Uploaded photo if there is one, otherwise the bundled photo for known venues, otherwise null.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }

        $key = preg_replace('/[^a-z]/', '', strtolower((string) $this->name));
        foreach (self::BUNDLED_PHOTOS as $needle => $file) {
            if (str_contains($key, $needle)) {
                return asset('img/facilities/' . $file);
            }
        }

        return null;
    }
 
    /**
     * Determine if this facility is part of the seeded core UA list.
     *
     * These are the official facilities controlled either by GSU or specific colleges.
     */
    public function isCoreFacility(): bool
    {
        $coreNames = [
            'BUSALIAN HALL',
            'PAGHIUSA HALL',
            'E-HUB',
            'BALAY NI JUAN',
            'ICT AVR',
            'CEA AVR',
            'CBA AVR',
            'NEW AVR',
            'GRAND STAND',
            'COVERED GYM',
            'TRACK OVAL',
        ];

        return in_array($this->name, $coreNames, true);
    }

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_facility');
    }

    public function isAvailable(): bool
    {
        return $this->is_active
            && !in_array($this->availability_status, ['unavailable', 'maintenance'], true);
    }

    public function isOwnedByCollege(string $collegeName): bool
    {
        return $this->owner_type === 'college'
            && $this->owner_college === $collegeName;
    }

    public function maintenanceTickets()
    {
        return $this->hasMany(MaintenanceTicket::class);
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function scopeOwnedByCollege($query, ?int $collegeId, ?string $collegeName)
    {
        return $query->where(function ($query) use ($collegeId, $collegeName) {
            $hasCondition = false;

            if ($collegeId) {
                $query->where('college_id', $collegeId);
                $hasCondition = true;
            }

            if ($collegeName) {
                if ($hasCondition) {
                    $query->orWhere('owner_college', $collegeName);
                } else {
                    $query->where('owner_college', $collegeName);
                    $hasCondition = true;
                }
            }

            if (! $hasCondition) {
                $query->whereRaw('0 = 1');
            }
        });
    }
}
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'contact_number',
        'email',
        'password',
        'role',
        'college_name',
        'college_id',
        'organization_name',
        'position',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCollegeStaff(): bool
    {
        return $this->role === 'college_staff';
    }

    public function isOrgStaff(): bool
    {
        return $this->role === 'org_staff';
    }

    public function isGuest(): bool
    {
        return $this->role === 'guest';
    }

    /**
     * Ids of the college staff accounts in this user's college (including this user).
     * Repair tickets are shared within a college. No college set: only this user.
     */
    public function collegeTeamIds(): array
    {
        if (! $this->isCollegeStaff() || (! $this->college_id && ! $this->college_name)) {
            return [$this->id];
        }

        return static::where('role', 'college_staff')
            ->where(fn ($q) => $this->college_id
                ? $q->where('college_id', $this->college_id)
                : $q->where('college_name', $this->college_name))
            ->pluck('id')
            ->push($this->id)
            ->unique()
            ->values()
            ->all();
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'requester_id');
    }

    public function maintenanceTickets()
    {
        return $this->hasMany(MaintenanceTicket::class, 'requester_id');
    }

    public function staffAssignments()
    {
        return $this->hasMany(StaffAssignment::class, 'staff_id');
    }

    public function maintenanceLogs()
    {
        return $this->hasMany(MaintenanceLog::class, 'staff_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }
}

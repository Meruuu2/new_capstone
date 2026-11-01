<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signatory extends Model
{
    protected $fillable = [
        'type',
        'name',
        'unit',
        'college_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function scopeForCollege($query, ?int $collegeId, ?string $collegeName)
    {
        return $query->where(function ($query) use ($collegeId, $collegeName) {
            $hasCondition = false;

            if ($collegeId) {
                $query->where('college_id', $collegeId);
                $hasCondition = true;
            }

            if ($collegeName) {
                if ($hasCondition) {
                    $query->orWhere('unit', $collegeName);
                } else {
                    $query->where('unit', $collegeName);
                    $hasCondition = true;
                }
            }

            if (! $hasCondition) {
                $query->whereRaw('0 = 1');
            }
        });
    }
}

<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ManagesUnitUsers;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserController extends Controller
{
    use ManagesUnitUsers;

    protected function unitRole(): string
    {
        return 'college_staff';
    }

    protected function unitPrefix(): string
    {
        return 'college';
    }

    protected function headTitle(): string
    {
        return 'College Admin';
    }

    protected function isHead(User $user, ?int $primaryId = null): bool
    {
        return $user->isCollegeStaff() && $user->id === ($primaryId ?? $this->primaryId());
    }

    protected function nameRules(): array
    {
        return [
            'required',
            'string',
            'max:255',
            function ($attribute, $value, $fail) {
                $parts = preg_split('/\s+/u', trim((string) $value), -1, PREG_SPLIT_NO_EMPTY);
                if (count($parts) < 2) {
                    $fail('Enter the user\'s full name, including at least a first and last name.');
                }
            },
        ];
    }

    protected function scopeToUnit(Builder $query): Builder
    {
        $me = auth()->user();

        if ($me->college_id) {
            return $query->where('college_id', $me->college_id);
        }

        return $me->college_name
            ? $query->where('college_name', $me->college_name)
            : $query->whereKey($me->id); // no college set: only yourself
    }

    protected function unitAttributes(): array
    {
        $me = auth()->user();

        return ['college_id' => $me->college_id, 'college_name' => $me->college_name];
    }
}

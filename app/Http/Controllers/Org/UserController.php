<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ManagesUnitUsers;
use Illuminate\Database\Eloquent\Builder;

class UserController extends Controller
{
    use ManagesUnitUsers;

    protected function unitRole(): string
    {
        return 'org_staff';
    }

    protected function unitPrefix(): string
    {
        return 'org';
    }

    protected function headTitle(): string
    {
        return 'Governor';
    }

    protected function scopeToUnit(Builder $query): Builder
    {
        $me = auth()->user();

        return $me->organization_name
            ? $query->where('organization_name', $me->organization_name)
            : $query->whereKey($me->id); // no organization set: only yourself
    }

    protected function unitAttributes(): array
    {
        return ['organization_name' => auth()->user()->organization_name];
    }
}

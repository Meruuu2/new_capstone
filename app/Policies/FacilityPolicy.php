<?php

namespace App\Policies;

use App\Models\Facility;
use App\Models\User;

class FacilityPolicy
{
    public function manageCollegeFacility(User $user, Facility $facility): bool
    {
        return $user->isCollegeStaff()
            && $facility->isOwnedByCollege($user->college_name);
    }
}

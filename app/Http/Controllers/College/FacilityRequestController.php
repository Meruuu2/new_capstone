<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;     // Make sure to import your User model
use App\Models\Facility; // Make sure to import your Facility model

class FacilityRequestController extends Controller
{
    public function create()
    {
        // 1. Fetch the data for your dropdowns (adjust 'role' to match your database columns)
        $presidents = User::where('role', 'org_president')->get();
        $advisers = User::where('role', 'org_adviser')->get();

        // 2. Fetch the facilities if your form dropdown needs them too
        $coreFacilities = Facility::all();

        // 3. Pass ALL these variables into the view suitcase
        return view('org.requests.facilities_create', compact(
            'presidents',
            'advisers',
            'coreFacilities'
        ));
    }
}

<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FacilityController extends Controller
{
    /**
     * Display a listing of facilities owned by the college.
     */
    public function index()
    {
        $user = Auth::user();
        $collegeName = $user->college_name;
        $collegeId = $user->college_id;

        $facilities = Facility::where('owner_type', 'college')
            ->ownedByCollege($collegeId, $collegeName)
            ->orderBy('name')
            ->paginate(10);

        return view('college.facilities.index', compact('facilities', 'collegeName'));
    }
 
    /**
     * Show the form for creating a new facility.
     */
    public function create()
    {
        $collegeName = Auth::user()->college_name;
        return view('college.facilities.create', compact('collegeName'));
    }

    /**
     * Store a newly created facility in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $collegeName = $user->college_name;
        $collegeId = $user->college_id;

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'location'            => 'required|string|max:255',
            'description'         => 'nullable|string',
            'is_active'           => 'boolean',
            'availability_status' => 'nullable|in:available,unavailable,maintenance',
        ]);

        $validated['owner_type'] = 'college';
        $validated['owner_college'] = $collegeName;
        $validated['college_id'] = $collegeId;

        // New facilities from colleges must be verified by GSU:
        // start as inactive and unavailable until an admin approves.
        $validated['is_active'] = false;
        $validated['availability_status'] = 'unavailable';

        Facility::create($validated);

        return redirect()->route('college.facilities.index')
            ->with('status', 'Facility created successfully.');
    }

    /**
     * Show the form for editing the specified facility.
     */
    public function edit(Facility $facility)
    {
        $user = Auth::user();

        if (! $this->facilityBelongsToCollege($facility, $user->college_id, $user->college_name)) {
            abort(403, 'Unauthorized access.');
        }

        $collegeName = $user->college_name;
        return view('college.facilities.edit', compact('facility', 'collegeName'));
    }

    /**
     * Update the specified facility in storage.
     */
    public function update(Request $request, Facility $facility)
    {
        // Ensure the facility belongs to the user's college
        if (! $this->facilityBelongsToCollege($facility, Auth::user()->college_id, Auth::user()->college_name)) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'location'            => 'required|string|max:255',
            'description'         => 'nullable|string',
            'is_active'           => 'boolean',
            'availability_status' => 'nullable|in:available,unavailable,maintenance',
        ]);

        $facility->update($validated);

        return redirect()->route('college.facilities.index')
            ->with('status', 'Facility updated successfully.');
    }

    public function deactivate(Facility $facility)
    {
        if (! $this->facilityBelongsToCollege($facility, Auth::user()->college_id, Auth::user()->college_name)) {
            abort(403, 'Unauthorized access.');
        }

        $facility->update(['is_active' => false]);

        return redirect()->route('college.facilities.index')
            ->with('status', 'Facility deactivated successfully.');
    }

    protected function facilityBelongsToCollege(Facility $facility, ?int $collegeId, ?string $collegeName): bool
    {
        if ($facility->owner_type !== 'college') {
            return false;
        }

        if ($collegeId && $facility->college_id === $collegeId) {
            return true;
        }

        return $facility->owner_college === $collegeName;
    }
}

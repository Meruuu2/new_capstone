<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFacilityRequest;
use App\Models\Facility;

class FacilityController extends Controller
{
    public function index()
    {
        $facilities = Facility::orderBy('name')->paginate(10);
        return view('admin.facilities.index', compact('facilities'));
    }

    public function create()
    {
        return view('admin.facilities.create');
    }

    public function store(StoreFacilityRequest $request)
    {
        Facility::create($request->validated());

        return redirect()->route('admin.facilities.index')
            ->with('status', 'Facility created successfully.');
    }

    public function edit(Facility $facility)
    {
        return view('admin.facilities.edit', compact('facility'));
    }

    public function update(StoreFacilityRequest $request, Facility $facility)
    {
        $validated = $request->validated();

        if ($facility->isCoreFacility()) {
            unset($validated['owner_type'], $validated['owner_college']);
        }

        if ($facility->owner_type === 'college') {
            unset($validated['owner_type'], $validated['owner_college'], $validated['availability_status']);
        }


        $facility->update($validated);

        return redirect()->route('admin.facilities.index')
            ->with('status', 'Facility updated successfully.');
    }

    public function deactivate(Facility $facility)
    {
        $facility->update(['is_active' => false]);

        return redirect()->route('admin.facilities.index')
            ->with('status', 'Facility deactivated successfully.');
    }

    public function activate(Facility $facility)
    {
        $facility->update(['is_active' => true]);

        return redirect()->route('admin.facilities.index')
            ->with('status', 'Facility activated successfully.');
    }
}

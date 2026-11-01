<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFacilityRequest;
use App\Models\Facility;
use Illuminate\Support\Facades\Storage;

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
        $validated = $request->safe()->except(['image', 'remove_image']);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('facilities', 'public');
        }

        Facility::create($validated);

        return redirect()->route('admin.facilities.index')
            ->with('status', 'Facility created successfully.');
    }

    public function edit(Facility $facility)
    {
        return view('admin.facilities.edit', compact('facility'));
    }

    public function update(StoreFacilityRequest $request, Facility $facility)
    {
        $validated = $request->safe()->except(['image', 'remove_image']);

        if ($request->hasFile('image') || $request->boolean('remove_image')) {
            $oldImage = $facility->image_path;
            $validated['image_path'] = $request->hasFile('image')
                ? $request->file('image')->store('facilities', 'public')
                : null;
        }

        if ($facility->isCoreFacility()) {
            unset($validated['owner_type'], $validated['owner_college']);
        }

        if ($facility->owner_type === 'college') {
            unset($validated['owner_type'], $validated['owner_college'], $validated['availability_status']);
        }


        $facility->update($validated);

        // Delete the replaced file only after the new path is saved.
        if (! empty($oldImage)) {
            Storage::disk('public')->delete($oldImage);
        }

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

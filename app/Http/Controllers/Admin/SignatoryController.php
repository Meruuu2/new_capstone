<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Signatory;
use Illuminate\Http\Request;

class SignatoryController extends Controller
{
    public function index()
    {
        $signatories = Signatory::orderBy('unit')
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.signatories.index', compact('signatories'));
    }

    public function create()
    {
        return view('admin.signatories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'      => 'required|in:dean,program_head,org_president,org_adviser,gsu_head',
            'name'      => 'required|string|max:255',
            'unit'      => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        Signatory::create($data);

        return redirect()->route('admin.signatories.index')
            ->with('status', 'Signatory created.');
    }

    public function edit(Signatory $signatory)
    {
        return view('admin.signatories.edit', compact('signatory'));
    }

    public function update(Request $request, Signatory $signatory)
    {
        $data = $request->validate([
            'type'      => 'required|in:dean,program_head,org_president,org_adviser,gsu_head',
            'name'      => 'required|string|max:255',
            'unit'      => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $signatory->update($data);

        return redirect()->route('admin.signatories.index')
            ->with('status', 'Signatory updated.');
    }

    public function destroy(Signatory $signatory)
    {
        $signatory->delete();

        return redirect()->route('admin.signatories.index')
            ->with('status', 'Signatory deleted.');
    }
}

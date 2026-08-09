<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccommodationType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccommodationTypeController extends Controller
{
    public function index(): View
    {
        $types = AccommodationType::query()->latest()->paginate(20);

        return view('admin.types.index', compact('types'));
    }

    public function create(): View
    {
        return view('admin.types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
        $data['slug'] = Str::slug($data['name']);
        AccommodationType::query()->create($data);

        return redirect()->route('admin.types.index')->with('success', 'Type created.');
    }

    public function edit(AccommodationType $type): View
    {
        return view('admin.types.edit', compact('type'));
    }

    public function update(Request $request, AccommodationType $type): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
        $data['slug'] = Str::slug($data['name']);
        $type->update($data);

        return redirect()->route('admin.types.index')->with('success', 'Type updated.');
    }

    public function destroy(AccommodationType $type): RedirectResponse
    {
        $type->delete();

        return back()->with('success', 'Type deleted.');
    }
}

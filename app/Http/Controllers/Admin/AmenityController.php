<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AmenityController extends Controller
{
    public function index(Request $request): View
    {
        $query = Amenity::query()->latest();

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('icon', 'like', "%{$q}%");
            });
        }

        $amenities = $query->paginate(20)->withQueryString();

        return view('admin.amenities.index', compact('amenities'));
    }

    public function create(): View
    {
        return view('admin.amenities.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Amenity::query()->create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]));

        return redirect()->route('admin.amenities.index')->with('success', 'Amenity created.');
    }

    public function edit(Amenity $amenity): View
    {
        return view('admin.amenities.edit', compact('amenity'));
    }

    public function update(Request $request, Amenity $amenity): RedirectResponse
    {
        $amenity->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]));

        return redirect()->route('admin.amenities.index')->with('success', 'Amenity updated.');
    }

    public function destroy(Amenity $amenity): RedirectResponse
    {
        $amenity->delete();

        return back()->with('success', 'Amenity deleted.');
    }
}

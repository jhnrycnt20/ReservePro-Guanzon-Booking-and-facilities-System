<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationType;
use App\Models\Amenity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccommodationController extends Controller
{
    public function index(): View
    {
        $accommodations = Accommodation::query()->with('type')->latest()->paginate(20);

        return view('admin.accommodations.index', compact('accommodations'));
    }

    public function create(): View
    {
        return view('admin.accommodations.create', [
            'types' => AccommodationType::query()->orderBy('name')->get(),
            'amenities' => Amenity::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('accommodations', 'public');
        }
        $data['is_active'] = $request->boolean('is_active', true);

        $accommodation = Accommodation::query()->create(collect($data)->except('amenities')->all());
        $accommodation->amenities()->sync($request->input('amenities', []));

        return redirect()->route('admin.accommodations.index')->with('success', 'Accommodation created.');
    }

    public function edit(Accommodation $accommodation): View
    {
        $accommodation->load('amenities');

        return view('admin.accommodations.edit', [
            'accommodation' => $accommodation,
            'types' => AccommodationType::query()->orderBy('name')->get(),
            'amenities' => Amenity::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Accommodation $accommodation): RedirectResponse
    {
        $data = $this->validated($request, $accommodation->id);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('accommodations', 'public');
        }
        $data['is_active'] = $request->boolean('is_active', true);

        $accommodation->update(collect($data)->except('amenities')->all());
        $accommodation->amenities()->sync($request->input('amenities', []));

        return redirect()->route('admin.accommodations.index')->with('success', 'Accommodation updated.');
    }

    public function destroy(Accommodation $accommodation): RedirectResponse
    {
        $accommodation->delete();

        return back()->with('success', 'Accommodation deleted.');
    }

    protected function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'accommodation_type_id' => ['required', 'exists:accommodation_types,id'],
            'name' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:50', 'unique:accommodations,number,'.($id ?? 'NULL')],
            'description' => ['nullable', 'string'],
            'capacity' => ['required', 'integer', 'min:1'],
            'rate' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:available,reserved,occupied,maintenance,inactive'],
            'image' => ['nullable', 'image', 'max:5120'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['exists:amenities,id'],
        ]);
    }
}

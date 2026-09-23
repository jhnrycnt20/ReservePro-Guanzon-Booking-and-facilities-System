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
    public function index(Request $request): View
    {
        $query = Accommodation::query()
            ->with(['type', 'amenities'])
            ->latest();

        if ($request->filled('type')) {
            $query->where('accommodation_type_id', $request->integer('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('active')) {
            $query->where('is_active', $request->input('active') === '1');
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('number', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        return view('admin.accommodations.index', [
            'accommodations' => $query->paginate(20)->withQueryString(),
            'types' => AccommodationType::query()->orderBy('name')->get(),
            'amenities' => Amenity::query()->orderBy('name')->get(),
        ]);
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
        $data['gallery'] = $this->storeGalleryUploads($request);

        $accommodation = Accommodation::query()->create(collect($data)->except('amenities')->all());
        $accommodation->amenities()->sync($request->input('amenities', []));

        return redirect()->route('admin.accommodations.index')->with('success', 'Accommodation created.');
    }

    public function show(Accommodation $accommodation): View
    {
        $accommodation->load(['type', 'amenities']);

        return view('admin.accommodations.show', [
            'accommodation' => $accommodation,
        ]);
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
        $statusLocked = $accommodation->isStatusLocked();
        $data = $this->validated($request, $accommodation->id, $statusLocked);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('accommodations', 'public');
        }
        $data['is_active'] = $request->boolean('is_active', true);

        if ($statusLocked) {
            // Keep system-managed status while booked or checked in.
            unset($data['status']);
        }

        $remove = collect($request->input('remove_gallery', []));
        $remaining = collect($accommodation->gallery ?? [])->reject(fn ($path) => $remove->contains($path));
        $data['gallery'] = $remaining->merge($this->storeGalleryUploads($request))->values()->all();

        $accommodation->update(collect($data)->except(['amenities', 'remove_gallery'])->all());
        $accommodation->amenities()->sync($request->input('amenities', []));

        $message = $statusLocked
            ? 'Accommodation updated. Status was not changed because this room is booked or checked in.'
            : 'Accommodation updated.';

        return redirect()->route('admin.accommodations.index')->with('success', $message);
    }

    public function destroy(Accommodation $accommodation): RedirectResponse
    {
        $accommodation->delete();

        return back()->with('success', 'Accommodation deleted.');
    }

    protected function validated(Request $request, ?int $id = null, bool $statusLocked = false): array
    {
        return $request->validate([
            'accommodation_type_id' => ['required', 'exists:accommodation_types,id'],
            'name' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:50', 'unique:accommodations,number,'.($id ?? 'NULL')],
            'description' => ['nullable', 'string'],
            'capacity' => ['required', 'integer', 'min:1'],
            'rate' => ['required', 'numeric', 'min:0'],
            'status' => $statusLocked
                ? ['nullable', 'string']
                : ['required', 'in:'.implode(',', \App\Enums\AccommodationStatus::manualValues())],
            'image' => ['nullable', 'image', 'max:5120'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'max:5120'],
            'remove_gallery' => ['nullable', 'array'],
            'remove_gallery.*' => ['string'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['exists:amenities,id'],
        ]);
    }

    /**
     * @return list<string>
     */
    protected function storeGalleryUploads(Request $request): array
    {
        if (! $request->hasFile('gallery')) {
            return [];
        }

        return collect($request->file('gallery'))
            ->map(fn ($file) => $file->store('accommodations', 'public'))
            ->all();
    }
}

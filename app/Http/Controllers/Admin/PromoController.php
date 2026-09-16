<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Promo;
use App\Services\PromoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoController extends Controller
{
    public function __construct(protected PromoService $promoService)
    {
    }

    public function index(Request $request): View
    {
        $query = Promo::query()
            ->with(['accommodations:id,name'])
            ->withCount('accommodations')
            ->latest();

        if ($request->filled('active')) {
            $query->where('is_active', $request->input('active') === '1');
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($builder) use ($q) {
                $builder->where('code', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%");
            });
        }

        $promos = $query->paginate(20)->withQueryString();

        return view('admin.promos.index', compact('promos'));
    }

    public function create(): View
    {
        $accommodations = Accommodation::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'number', 'rate']);

        return view('admin.promos.create', [
            'accommodations' => $accommodations,
            'generatedCode' => $this->promoService->generateCode(),
        ]);
    }

    public function preview(Request $request): JsonResponse
    {
        $data = $request->validate([
            'discount_percent' => ['required', 'numeric', 'min:1', 'max:100'],
            'applies_to_all' => ['nullable', 'boolean'],
            'accommodation_ids' => ['nullable', 'array'],
            'accommodation_ids.*' => ['integer', 'exists:accommodations,id'],
        ]);

        $appliesToAll = $request->boolean('applies_to_all');
        $ids = collect($data['accommodation_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();

        $accommodations = Accommodation::query()
            ->where('is_active', true)
            ->when(! $appliesToAll, fn ($q) => $q->whereIn('id', $ids->all() ?: [0]))
            ->orderBy('name')
            ->get(['id', 'name', 'number', 'rate']);

        return response()->json([
            'preview' => $this->promoService->previewPrices([
                'discount_percent' => $data['discount_percent'],
            ], $accommodations),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32', 'unique:promos,code'],
            'discount_percent' => ['required', 'numeric', 'min:1', 'max:100'],
            'applies_to_all' => ['nullable', 'boolean'],
            'accommodation_ids' => ['nullable', 'array'],
            'accommodation_ids.*' => ['integer', 'exists:accommodations,id'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $appliesToAll = $request->boolean('applies_to_all');
        $ids = collect($data['accommodation_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();

        if (! $appliesToAll && $ids->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['accommodation_ids' => 'Select at least one accommodation, or choose all accommodations.']);
        }

        $code = $this->promoService->normalizeCode($data['code'] ?? null);
        if ($code === '') {
            $code = $this->promoService->generateCode();
        }

        $promo = Promo::query()->create([
            'code' => $code,
            'name' => $data['name'] ?? null,
            'discount_percent' => $data['discount_percent'],
            'applies_to_all' => $appliesToAll,
            'is_active' => $request->boolean('is_active', true),
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'usage_limit' => $data['usage_limit'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        if (! $appliesToAll) {
            $promo->accommodations()->sync($ids->all());
        }

        return redirect()
            ->route('admin.promos.index')
            ->with('success', "Promo {$promo->code} created.");
    }

    public function show(Promo $promo): View
    {
        $promo->load(['accommodations:id,name,number,rate', 'creator']);
        $preview = $this->promoService->previewPrices(
            $promo,
            $promo->applies_to_all
                ? Accommodation::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'number', 'rate'])
                : $promo->accommodations
        );

        return view('admin.promos.show', compact('promo', 'preview'));
    }

    public function edit(Promo $promo): View
    {
        $promo->load('accommodations:id');
        $accommodations = Accommodation::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'number', 'rate']);

        return view('admin.promos.edit', compact('promo', 'accommodations'));
    }

    public function update(Request $request, Promo $promo): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:32', 'unique:promos,code,'.$promo->id],
            'discount_percent' => ['required', 'numeric', 'min:1', 'max:100'],
            'applies_to_all' => ['nullable', 'boolean'],
            'accommodation_ids' => ['nullable', 'array'],
            'accommodation_ids.*' => ['integer', 'exists:accommodations,id'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $appliesToAll = $request->boolean('applies_to_all');
        $ids = collect($data['accommodation_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();

        if (! $appliesToAll && $ids->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['accommodation_ids' => 'Select at least one accommodation, or choose all accommodations.']);
        }

        $promo->update([
            'code' => $this->promoService->normalizeCode($data['code']),
            'name' => $data['name'] ?? null,
            'discount_percent' => $data['discount_percent'],
            'applies_to_all' => $appliesToAll,
            'is_active' => $request->boolean('is_active', true),
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'usage_limit' => $data['usage_limit'] ?? null,
        ]);

        if ($appliesToAll) {
            $promo->accommodations()->sync([]);
        } else {
            $promo->accommodations()->sync($ids->all());
        }

        return redirect()
            ->route('admin.promos.show', $promo)
            ->with('success', 'Promo updated.');
    }

    public function destroy(Promo $promo): RedirectResponse
    {
        $promo->delete();

        return redirect()
            ->route('admin.promos.index')
            ->with('success', 'Promo deleted.');
    }

    public function toggle(Promo $promo): RedirectResponse
    {
        $promo->update(['is_active' => ! $promo->is_active]);

        return back()->with('success', $promo->is_active ? 'Promo activated.' : 'Promo deactivated.');
    }
}

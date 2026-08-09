<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Pricing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PricingController extends Controller
{
    public function index(): View
    {
        $pricing = Pricing::query()->with('accommodation')->latest()->paginate(20);

        return view('admin.pricing.index', compact('pricing'));
    }

    public function create(): View
    {
        return view('admin.pricing.create', [
            'accommodations' => Accommodation::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'accommodation_id' => ['required', 'exists:accommodations,id'],
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        Pricing::query()->create($data);

        return redirect()->route('admin.pricing.index')->with('success', 'Pricing created.');
    }

    public function edit(Pricing $pricing): View
    {
        return view('admin.pricing.edit', [
            'pricing' => $pricing,
            'accommodations' => Accommodation::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Pricing $pricing): RedirectResponse
    {
        $data = $request->validate([
            'accommodation_id' => ['required', 'exists:accommodations,id'],
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $pricing->update($data);

        return redirect()->route('admin.pricing.index')->with('success', 'Pricing updated.');
    }

    public function destroy(Pricing $pricing): RedirectResponse
    {
        $pricing->delete();

        return back()->with('success', 'Pricing deleted.');
    }
}

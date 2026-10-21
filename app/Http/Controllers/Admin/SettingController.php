<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): RedirectResponse|View
    {
        if (! request()->boolean('page')) {
            return redirect()
                ->route('admin.dashboard')
                ->with('open_modal', 'settings');
        }

        $settings = SystemSetting::query()->pluck('value', 'key');

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'resort_name' => ['required', 'string', 'max:255'],
            'resort_subtitle' => ['nullable', 'string', 'max:255'],
            'resort_address' => ['nullable', 'string', 'max:500'],
            'resort_email' => ['required', 'email', 'max:255'],
            'resort_phone' => ['required', 'string', 'max:50'],
            'resort_phone_landline' => ['nullable', 'string', 'max:50'],
            'currency' => ['required', 'string', 'size:3', 'alpha:ascii'],
            'check_in_time' => ['required', 'date_format:H:i'],
            'check_out_time' => ['required', 'date_format:H:i'],
            'gcash_number' => ['nullable', 'string', 'max:50'],
            'gcash_name' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
        ]);

        foreach ($data as $key => $value) {
            SystemSetting::setValue($key, $value);
        }

        return back()->with('success', 'Settings updated.');
    }
}

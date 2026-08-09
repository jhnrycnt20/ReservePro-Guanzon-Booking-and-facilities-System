<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = SystemSetting::query()->pluck('value', 'key');

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'resort_name' => ['required', 'string', 'max:255'],
            'resort_email' => ['required', 'email'],
            'resort_phone' => ['required', 'string', 'max:50'],
            'currency' => ['required', 'string', 'max:10'],
            'check_in_time' => ['required', 'string', 'max:10'],
            'check_out_time' => ['required', 'string', 'max:10'],
        ]);

        foreach ($data as $key => $value) {
            SystemSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Settings updated.');
    }
}

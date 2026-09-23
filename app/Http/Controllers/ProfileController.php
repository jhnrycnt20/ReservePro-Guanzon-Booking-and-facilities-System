<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        if ($user->hasRole('guest')) {
            return view('guest.profile.edit', [
                'user' => $user,
                'guest' => $user->guest,
            ]);
        }

        return view('profile.edit', ['user' => $user]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $isGuest = $user->hasRole('guest');

        $rules = [
            'phone' => [
                'required',
                'string',
                'max:20',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $normalized = preg_replace('/[\s\-()]/', '', (string) $value) ?? '';
                    if (! preg_match('/^(?:\+?63|0)9\d{9}$/', $normalized)) {
                        $fail('Enter a valid PH mobile number, e.g. 09171234567.');
                    }
                },
            ],
        ];

        if (! $isGuest) {
            $rules['name'] = [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[\p{L}]+(?:[ \'\-.][\p{L}]+)*$/u',
            ];
            $rules['email'] = ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)];
        }

        $data = $request->validateWithBag('profile', $rules, [
            'name.required' => 'Please enter your full name.',
            'name.regex' => 'Full name may only include letters, spaces, hyphens, and apostrophes.',
            'email.unique' => 'This email is already in use.',
            'phone.required' => 'Please enter your contact number.',
        ]);

        $phone = preg_replace('/[\s\-()]/', '', (string) $data['phone']) ?? '';
        if (preg_match('/^(?:\+?63)9(\d{9})$/', $phone, $matches)) {
            $phone = '09'.$matches[1];
        }

        $userUpdate = ['phone' => $phone];
        if (! $isGuest) {
            $userUpdate['name'] = trim($data['name']);
            $userUpdate['email'] = strtolower(trim($data['email']));
        }
        $user->update($userUpdate);

        if ($isGuest && $user->guest) {
            $user->guest->update(['contact_number' => $phone]);
        }

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ], [
            'current_password.current_password' => 'The current password is incorrect.',
        ]);

        $request->user()->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return back()->with('success', 'Password updated.');
    }
}

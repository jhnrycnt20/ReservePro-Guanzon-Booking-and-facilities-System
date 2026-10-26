<?php

namespace App\Http\Controllers;

use App\Rules\GmailAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $request->boolean('page')) {
            if ($user->hasRole('guest')) {
                $fallback = route('accommodations.browse');
                $previous = url()->previous();
                $target = $this->safeInternalRedirectUrl($previous, $fallback);

                return redirect()
                    ->to($target)
                    ->with('open_modal', 'profile');
            }

            return redirect()
                ->to(\App\Helpers\RoleRedirect::dashboardRoute())
                ->with('open_modal', 'profile');
        }

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
        $email = strtolower(trim((string) $request->input('email', '')));
        $emailChanged = $email !== strtolower(trim((string) $user->email));
        $changingPassword = $request->filled('password')
            || $request->filled('password_confirmation');
        $emailRules = ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)];
        if ($emailChanged) {
            $emailRules[] = new GmailAddress;
        }

        $rules = [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[\p{L}]+(?:[ \'\-.][\p{L}]+)*$/u',
            ],
            'email' => $emailRules,
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
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
            'current_password' => ($emailChanged || $changingPassword)
                ? ['required', 'current_password']
                : ['nullable', 'current_password'],
            'password' => $changingPassword
                ? ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()]
                : ['nullable'],
        ];

        $data = $request->validateWithBag('profile', $rules, [
            'name.required' => 'Please enter your full name.',
            'name.regex' => 'Full name may only include letters, spaces, hyphens, and apostrophes.',
            'email.unique' => 'This email is already in use.',
            'phone.required' => 'Please enter your contact number.',
            'avatar.image' => 'Profile photo must be an image.',
            'avatar.max' => 'Profile photo must be 2MB or smaller.',
            'current_password.current_password' => 'The current password is incorrect.',
        ]);

        $phone = preg_replace('/[\s\-()]/', '', (string) $data['phone']) ?? '';
        if (preg_match('/^(?:\+?63)9(\d{9})$/', $phone, $matches)) {
            $phone = '09'.$matches[1];
        }

        $name = trim($data['name']);
        $email = strtolower(trim($data['email']));
        $avatarPath = $user->avatar;
        $avatarChanged = false;

        if ($request->boolean('remove_avatar') && $avatarPath) {
            Storage::disk('public')->delete($avatarPath);
            $avatarPath = null;
            $avatarChanged = true;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $avatarChanged = true;
        }

        $profileChanged = $name !== (string) $user->name
            || $email !== strtolower((string) $user->email)
            || $phone !== (string) ($user->phone ?? '')
            || $avatarChanged
            || $changingPassword;

        if (! $profileChanged) {
            $redirect = back();
            if ($request->input('_modal') === 'profile') {
                $redirect->with('open_modal', 'profile');
            }

            return $redirect;
        }

        $payload = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'avatar' => $avatarPath,
        ];

        if ($changingPassword) {
            $payload['password'] = $data['password'];
        }

        $user->update($payload);

        if ($user->guest) {
            $user->guest->update(['contact_number' => $phone]);
        }

        return back()->with('success_popup', 'Successfully changed.');
    }

    /**
     * Only allow same-app redirects to avoid open-redirect via Referer.
     */
    protected function safeInternalRedirectUrl(?string $candidate, string $fallback): string
    {
        if (! is_string($candidate) || $candidate === '' || $candidate === url()->current()) {
            return $fallback;
        }

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $candidateHost = parse_url($candidate, PHP_URL_HOST);
        $candidateScheme = parse_url($candidate, PHP_URL_SCHEME);

        // Relative paths are always internal.
        if ($candidateHost === null && str_starts_with($candidate, '/')) {
            return $candidate;
        }

        if (
            in_array($candidateScheme, ['http', 'https'], true)
            && is_string($appHost)
            && is_string($candidateHost)
            && strcasecmp($appHost, $candidateHost) === 0
        ) {
            return $candidate;
        }

        return $fallback;
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

        return back()->with('success_popup', 'Successfully changed.');
    }
}

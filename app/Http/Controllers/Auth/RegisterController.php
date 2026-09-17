<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Helpers\RoleRedirect;
use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/guest/bookings';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function redirectTo()
    {
        return RoleRedirect::dashboardPath();
    }

    public function register(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->getMessages())->errorBag('register');
        }

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        return $this->registered($request, $user) ?: redirect($this->redirectPath());
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[\p{L}]+(?:[ \'\-.][\p{L}]+)*$/u',
            ],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
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
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'address' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Please enter your full name.',
            'name.min' => 'Full name must be at least 2 characters.',
            'name.regex' => 'Full name may only include letters, spaces, hyphens, and apostrophes.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Enter a valid email address (e.g. you@email.com).',
            'email.unique' => 'This email is already registered. Try logging in instead.',
            'phone.required' => 'Please enter your contact number.',
            'password.required' => 'Please create a password.',
            'password.confirmed' => 'Password confirmation does not match.',
            'address.max' => 'Address must not exceed 1000 characters.',
        ]);
    }

    protected function create(array $data)
    {
        $phone = preg_replace('/[\s\-()]/', '', (string) ($data['phone'] ?? '')) ?? '';
        if (preg_match('/^(?:\+?63)9(\d{9})$/', $phone, $matches)) {
            $phone = '09'.$matches[1];
        }

        return DB::transaction(function () use ($data, $phone) {
            $guestRole = Role::query()->where('slug', UserRole::Guest->value)->firstOrFail();

            $user = User::query()->create([
                'role_id' => $guestRole->id,
                'name' => trim((string) $data['name']),
                'email' => strtolower(trim((string) $data['email'])),
                'phone' => $phone,
                'password' => Hash::make($data['password']),
                'is_active' => true,
            ]);

            Guest::query()->create([
                'user_id' => $user->id,
                'contact_number' => $phone,
                'address' => filled($data['address'] ?? null) ? trim((string) $data['address']) : null,
            ]);

            return $user;
        });
    }
}

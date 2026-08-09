<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Helpers\RoleRedirect;
use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/guest/dashboard';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function redirectTo()
    {
        return RoleRedirect::dashboardPath();
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    protected function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $guestRole = Role::query()->where('slug', UserRole::Guest->value)->firstOrFail();

            $user = User::query()->create([
                'role_id' => $guestRole->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'is_active' => true,
            ]);

            Guest::query()->create([
                'user_id' => $user->id,
                'contact_number' => $data['phone'],
                'address' => $data['address'] ?? null,
            ]);

            return $user;
        });
    }
}

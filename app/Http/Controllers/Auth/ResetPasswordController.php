<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    protected $redirectTo = '/?login=1';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $user = User::query()
                        ->where('email', (string) request('email'))
                        ->first();

                    if ($user && Hash::check((string) $value, $user->password)) {
                        $fail('New password must be different from your current password.');
                    }
                },
            ],
        ];
    }

    protected function validationErrorMessages()
    {
        return [
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters.',
        ];
    }

    protected function resetPassword($user, $password)
    {
        $this->setUserPassword($user, $password);

        $user->setRememberToken(Str::random(60));
        $user->save();

        event(new PasswordReset($user));

        // Do not auto-login — send the guest back to the login form.
    }

    protected function sendResetResponse(Request $request, $response)
    {
        return redirect('/?login=1')->with(
            'success',
            'You have successfully reset your password. Please log in with your new password.'
        );
    }
}

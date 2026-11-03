<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\RoleRedirect;
use App\Http\Controllers\Controller;
use App\Services\GmailExistenceChecker;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    protected function redirectTo()
    {
        return RoleRedirect::dashboardPath();
    }

    public function showLoginForm()
    {
        return redirect('/?login=1');
    }

    protected function validateLogin(Request $request)
    {
        $this->validateWithBag('login', $request, [
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * Block guest logins that used a non-existent Gmail (e.g. created on Render
     * before HTTPS mailbox verification was enabled).
     */
    protected function authenticated(Request $request, $user)
    {
        if (! $user->isGuestRole()) {
            return null;
        }

        $email = strtolower(trim((string) $user->email));
        if ($email === '' || ! str_ends_with($email, '@gmail.com')) {
            return null;
        }

        try {
            if (! app(GmailExistenceChecker::class)->exists($email)) {
                $this->guard()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => ['This Gmail does not exist. Please enter a real Gmail address.'],
                ])->errorBag('login')->redirectTo(url('/?login=1'));
            }
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            $this->guard()->logout();
            throw ValidationException::withMessages([
                'email' => ['This Gmail does not exist. Please enter a real Gmail address.'],
            ])->errorBag('login')->redirectTo(url('/?login=1'));
        }

        return null;
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ])->errorBag('login');
    }

    protected function loggedOut(Request $request)
    {
        return redirect('/');
    }
}

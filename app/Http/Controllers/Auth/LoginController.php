<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\RoleRedirect;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

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

    protected function sendFailedLoginResponse(Request $request)
    {
        throw \Illuminate\Validation\ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ])->errorBag('login');
    }

    protected function loggedOut(Request $request)
    {
        return redirect('/');
    }
}

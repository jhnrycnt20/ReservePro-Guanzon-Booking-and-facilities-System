<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showLinkRequestForm()
    {
        return redirect('/?forgot=1');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        $user = User::query()
            ->where('email', (string) $request->input('email'))
            ->first();

        if ($user && ! $user->isActive()) {
            return $this->sendResetLinkFailedResponse($request, Password::INVALID_USER);
        }

        try {
            $response = $this->broker()->sendResetLink(
                $this->credentials($request)
            );
        } catch (\Throwable $exception) {
            report($exception);

            return redirect('/?forgot=1')
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'We could not send the reset email right now. Please try again later.',
                ]);
        }

        return $response == Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);
    }

    protected function sendResetLinkResponse(Request $request, $response)
    {
        if ($request->wantsJson()) {
            return new JsonResponse(['message' => trans($response)], 200);
        }

        return redirect('/?forgot=1')
            ->with('status', trans($response))
            ->with('password_reset_sent', true);
    }

    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        if ($request->wantsJson()) {
            return new JsonResponse(['email' => trans($response)], 422);
        }

        return redirect('/?forgot=1')
            ->withInput($request->only('email'))
            ->withErrors(['email' => trans($response)]);
    }
}

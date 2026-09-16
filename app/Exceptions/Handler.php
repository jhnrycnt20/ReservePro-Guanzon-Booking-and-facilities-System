<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (TokenMismatchException $e, $request) {
            return $this->handleExpiredPage($request);
        });

        $this->renderable(function (HttpException $e, $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            return $this->handleExpiredPage($request);
        });
    }

    protected function handleExpiredPage($request)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Page expired. Please refresh and try again.'], 419);
        }

        $fallback = route('login');
        if ($request->is('register') || $request->routeIs('register')) {
            $fallback = route('register');
        } elseif ($request->is('contact') || $request->routeIs('contact') || $request->routeIs('contact.store')) {
            $fallback = route('contact');
        } elseif ($request->headers->get('referer')) {
            $fallback = url()->previous() ?: $fallback;
        }

        return redirect()
            ->to($fallback)
            ->withInput($request->except('password', 'password_confirmation', '_token'))
            ->with('error', 'Your session expired. Please try again.');
    }
}

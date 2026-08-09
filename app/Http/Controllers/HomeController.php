<?php

namespace App\Http\Controllers;

use App\Helpers\RoleRedirect;
use Illuminate\Http\RedirectResponse;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active']);
    }

    public function index(): RedirectResponse
    {
        return redirect()->to(RoleRedirect::dashboardRoute());
    }
}

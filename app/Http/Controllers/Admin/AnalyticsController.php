<?php

namespace App\Http\Controllers\Admin;

use Illuminate\View\View;

class AnalyticsController extends DashboardController
{
    public function index(): View
    {
        return view('admin.analytics', $this->dashboardData());
    }
}

<?php

namespace App\Http\Controllers\Admin;

use Illuminate\View\View;

class AnalyticsController extends DashboardController
{
    public function index(): View
    {
        return parent::index();
    }
}

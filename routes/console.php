<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\FrontDeskAutomationService;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('frontdesk:process-automation', function (FrontDeskAutomationService $automation) {
    $automation->runDueActions();
    $this->info('Front desk automation finished (no-shows + due check-outs).');
})->purpose('Mark missed arrivals as no-show and auto check-out due stays');

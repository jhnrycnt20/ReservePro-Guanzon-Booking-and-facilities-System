<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('production')) {
            config([
                'database.default' => 'sqlite',
                'database.connections.sqlite.database' => env(
                    'DB_DATABASE',
                    database_path('database.sqlite')
                ),
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');

            if (config('session.secure') === null) {
                config(['session.secure' => true]);
            }
        }

        view()->composer('*', function ($view) {
            static $resortSettings = null;

            if ($resortSettings === null) {
                try {
                    $resortSettings = [
                        'resort_name' => \App\Models\SystemSetting::getValue('resort_name', 'Guanzon Beach'),
                        'resort_subtitle' => \App\Models\SystemSetting::getValue('resort_subtitle', 'Bluepool Waterpark'),
                        'resort_address' => \App\Models\SystemSetting::getValue('resort_address', 'Philippines'),
                        'resort_email' => \App\Models\SystemSetting::getValue('resort_email', 'info@guanzonresort.com'),
                        'resort_phone' => \App\Models\SystemSetting::getValue('resort_phone', '09190644054'),
                        'resort_phone_landline' => \App\Models\SystemSetting::getValue('resort_phone_landline', '265-7942'),
                        'check_in_time' => \App\Models\SystemSetting::getValue('check_in_time', '14:00'),
                        'check_out_time' => \App\Models\SystemSetting::getValue('check_out_time', '12:00'),
                        'gcash_number' => \App\Models\SystemSetting::getValue('gcash_number', '09505584607'),
                        'gcash_name' => \App\Models\SystemSetting::getValue('gcash_name', 'Guanzon Beach'),
                        'bank_name' => \App\Models\SystemSetting::getValue('bank_name', 'BDO'),
                        'bank_account_name' => \App\Models\SystemSetting::getValue('bank_account_name', 'Guanzon Beach'),
                        'bank_account_number' => \App\Models\SystemSetting::getValue('bank_account_number', '0000-0000-0000'),
                    ];
                } catch (\Throwable $e) {
                    $resortSettings = [
                        'resort_name' => 'Guanzon Beach',
                        'resort_subtitle' => 'Bluepool Waterpark',
                        'resort_address' => 'Philippines',
                        'resort_email' => 'info@guanzonresort.com',
                        'resort_phone' => '09190644054',
                        'resort_phone_landline' => '265-7942',
                        'check_in_time' => '14:00',
                        'check_out_time' => '12:00',
                        'gcash_number' => '09505584607',
                        'gcash_name' => 'Guanzon Beach',
                        'bank_name' => 'BDO',
                        'bank_account_name' => 'Guanzon Beach',
                        'bank_account_number' => '0000-0000-0000',
                    ];
                }
            }

            $view->with('resortSettings', $resortSettings);
            $view->with('paymongoEnabled', app(\App\Services\PayMongoService::class)->isConfigured());
        });
    }
}

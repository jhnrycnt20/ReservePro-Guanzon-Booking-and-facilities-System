<?php

namespace App\Providers;

use App\Models\Accommodation;
use App\Models\Booking;
use App\Models\IncidentReport;
use App\Models\Payment;
use App\Policies\AccommodationPolicy;
use App\Policies\BookingPolicy;
use App\Policies\IncidentReportPolicy;
use App\Policies\PaymentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Booking::class => BookingPolicy::class,
        Payment::class => PaymentPolicy::class,
        IncidentReport::class => IncidentReportPolicy::class,
        Accommodation::class => AccommodationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}

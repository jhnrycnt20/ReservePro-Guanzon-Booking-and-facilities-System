<?php

use App\Http\Controllers\Admin\AccommodationController as AdminAccommodationController;
use App\Http\Controllers\Admin\AccommodationTypeController;
use App\Http\Controllers\Admin\AmenityController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\Admin\PricingController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\FrontDesk\CheckInController;
use App\Http\Controllers\FrontDesk\CheckOutController;
use App\Http\Controllers\FrontDesk\DashboardController as FrontDeskDashboardController;
use App\Http\Controllers\FrontDesk\IncidentResolutionController;
use App\Http\Controllers\FrontDesk\PaymentVerificationController;
use App\Http\Controllers\FrontDesk\ReservationController;
use App\Http\Controllers\FrontDesk\WalkInController;
use App\Http\Controllers\Guest\AccommodationBrowseController;
use App\Http\Controllers\Guest\BookingController as GuestBookingController;
use App\Http\Controllers\Guest\DashboardController as GuestDashboardController;
use App\Http\Controllers\Guest\FeedbackController as GuestFeedbackController;
use App\Http\Controllers\Guest\IncidentReportController as GuestIncidentReportController;
use App\Http\Controllers\Guest\NotificationController;
use App\Http\Controllers\Guest\PaymentController as GuestPaymentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Security\DashboardController as SecurityDashboardController;
use App\Http\Controllers\Security\InvestigationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/blog', function () {
    return view('blog');
})->name('blog');

Route::get('/gallery', function () {
    return view('gallery');
})->name('gallery');

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/accommodations', [AccommodationBrowseController::class, 'index'])->name('accommodations.browse');
Route::get('/accommodations/{accommodation}', [AccommodationBrowseController::class, 'show'])->name('accommodations.show');
Route::get('/accommodations/{accommodation}/availability', [AccommodationBrowseController::class, 'availability'])->name('accommodations.availability');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read_all');

    Route::middleware(['role:guest'])->prefix('guest')->name('guest.')->group(function () {
        Route::get('/dashboard', [GuestDashboardController::class, 'index'])->name('dashboard');

        Route::get('/bookings', [GuestBookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/create', [GuestBookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [GuestBookingController::class, 'store'])->name('bookings.store');
        Route::get('/bookings/{booking}', [GuestBookingController::class, 'show'])->name('bookings.show');
        Route::post('/bookings/{booking}/cancel', [GuestBookingController::class, 'cancel'])->name('bookings.cancel');

        Route::get('/payments', [GuestPaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/create/{booking}', [GuestPaymentController::class, 'create'])->name('payments.create');
        Route::post('/payments/{booking}', [GuestPaymentController::class, 'store'])->name('payments.store');

        Route::get('/incidents', [GuestIncidentReportController::class, 'index'])->name('incidents.index');
        Route::get('/incidents/create', [GuestIncidentReportController::class, 'create'])->name('incidents.create');
        Route::post('/incidents', [GuestIncidentReportController::class, 'store'])->name('incidents.store');
        Route::get('/incidents/{incident}', [GuestIncidentReportController::class, 'show'])->name('incidents.show');

        Route::get('/feedback', [GuestFeedbackController::class, 'index'])->name('feedback.index');
        Route::get('/bookings/{booking}/feedback/create', [GuestFeedbackController::class, 'create'])->name('feedback.create');
        Route::post('/bookings/{booking}/feedback', [GuestFeedbackController::class, 'store'])->name('feedback.store');
    });

    Route::middleware(['role:front_desk'])->prefix('front-desk')->name('front_desk.')->group(function () {
        Route::get('/dashboard', [FrontDeskDashboardController::class, 'index'])->name('dashboard');

        Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
        Route::get('/reservations/{booking}', [ReservationController::class, 'show'])->name('reservations.show');
        Route::post('/reservations/{booking}/approve', [ReservationController::class, 'approve'])->name('reservations.approve');
        Route::post('/reservations/{booking}/reject', [ReservationController::class, 'reject'])->name('reservations.reject');
        Route::post('/reservations/{booking}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');

        Route::get('/walk-ins/create', [WalkInController::class, 'create'])->name('walkins.create');
        Route::post('/walk-ins', [WalkInController::class, 'store'])->name('walkins.store');

        Route::get('/check-ins', [CheckInController::class, 'index'])->name('checkins.index');
        Route::get('/check-ins/{booking}', [CheckInController::class, 'show'])->name('checkins.show');
        Route::post('/check-ins/{booking}', [CheckInController::class, 'store'])->name('checkins.store');

        Route::get('/check-outs', [CheckOutController::class, 'index'])->name('checkouts.index');
        Route::get('/check-outs/{booking}', [CheckOutController::class, 'show'])->name('checkouts.show');
        Route::post('/check-outs/{booking}', [CheckOutController::class, 'store'])->name('checkouts.store');

        Route::get('/payments', [PaymentVerificationController::class, 'index'])->name('payments.index');
        Route::post('/payments/{payment}/verify', [PaymentVerificationController::class, 'verify'])->name('payments.verify');
        Route::post('/payments/{payment}/reject', [PaymentVerificationController::class, 'reject'])->name('payments.reject');
        Route::get('/payments/{payment}/receipt', [PaymentVerificationController::class, 'receipt'])->name('payments.receipt');

        Route::get('/incidents', [IncidentResolutionController::class, 'index'])->name('incidents.index');
        Route::get('/incidents/{incident}', [IncidentResolutionController::class, 'show'])->name('incidents.show');
        Route::post('/incidents/{incident}/progress', [IncidentResolutionController::class, 'progress'])->name('incidents.progress');
        Route::post('/incidents/{incident}/resolve', [IncidentResolutionController::class, 'resolve'])->name('incidents.resolve');
    });

    Route::middleware(['role:security'])->prefix('security')->name('security.')->group(function () {
        Route::get('/dashboard', [SecurityDashboardController::class, 'index'])->name('dashboard');
        Route::get('/incidents', [InvestigationController::class, 'index'])->name('incidents.index');
        Route::get('/incidents/{incident}', [InvestigationController::class, 'show'])->name('incidents.show');
        Route::post('/incidents/{incident}/verify', [InvestigationController::class, 'verify'])->name('incidents.verify');
        Route::post('/incidents/{incident}/invalidate', [InvestigationController::class, 'invalidate'])->name('incidents.invalidate');
    });

    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('accommodations', AdminAccommodationController::class)->except(['show']);
        Route::resource('types', AccommodationTypeController::class)
            ->parameters(['types' => 'type'])
            ->except(['show']);
        Route::resource('amenities', AmenityController::class)->except(['show']);
        Route::resource('pricing', PricingController::class)->except(['show']);

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/{incident}', [ReportController::class, 'show'])->name('reports.show');
        Route::get('/feedback', [AdminFeedbackController::class, 'index'])->name('feedback.index');
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    });
});

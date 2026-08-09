<a href="{{ route('guest.dashboard') }}" class="{{ request()->routeIs('guest.dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>
<div class="nav-section">Resort</div>
<a href="{{ route('accommodations.browse') }}" class="{{ request()->routeIs('accommodations.*') ? 'active' : '' }}">
    <i class="bi bi-buildings"></i> Browse Rooms
</a>
<a href="{{ route('guest.bookings.index') }}" class="{{ request()->routeIs('guest.bookings.*') ? 'active' : '' }}">
    <i class="bi bi-calendar-check"></i> My Reservations
</a>
<a href="{{ route('guest.payments.index') }}" class="{{ request()->routeIs('guest.payments.*') ? 'active' : '' }}">
    <i class="bi bi-credit-card"></i> Payments
</a>
<div class="nav-section">Support</div>
<a href="{{ route('guest.incidents.index') }}" class="{{ request()->routeIs('guest.incidents.*') ? 'active' : '' }}">
    <i class="bi bi-exclamation-triangle"></i> Reports
</a>
<a href="{{ route('guest.feedback.index') }}" class="{{ request()->routeIs('guest.feedback.*') ? 'active' : '' }}">
    <i class="bi bi-star"></i> Feedback
</a>
<a href="{{ route('notifications.index') }}" class="{{ request()->routeIs('notifications.*') ? 'active' : '' }}">
    <i class="bi bi-bell"></i> Notifications
</a>

<a href="{{ route('front_desk.dashboard') }}" class="{{ request()->routeIs('front_desk.dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>
<div class="nav-section">Reservations</div>
<a href="{{ route('front_desk.reservations.index') }}" class="{{ request()->routeIs('front_desk.reservations.*') ? 'active' : '' }}">
    <i class="bi bi-journal-text"></i> Reservations
</a>
<a href="{{ route('front_desk.walkins.create') }}" class="{{ request()->routeIs('front_desk.walkins.*') ? 'active' : '' }}">
    <i class="bi bi-person-walking"></i> Walk-in
</a>
<a href="{{ route('front_desk.checkins.index') }}" class="{{ request()->routeIs('front_desk.checkins.*') ? 'active' : '' }}">
    <i class="bi bi-box-arrow-in-right"></i> Check-in
</a>
<a href="{{ route('front_desk.checkouts.index') }}" class="{{ request()->routeIs('front_desk.checkouts.*') ? 'active' : '' }}">
    <i class="bi bi-box-arrow-right"></i> Check-out
</a>
<div class="nav-section">Operations</div>
<a href="{{ route('front_desk.payments.index') }}" class="{{ request()->routeIs('front_desk.payments.*') ? 'active' : '' }}">
    <i class="bi bi-cash-coin"></i> Payments
</a>
<a href="{{ route('front_desk.incidents.index') }}" class="{{ request()->routeIs('front_desk.incidents.*') ? 'active' : '' }}">
    <i class="bi bi-clipboard2-pulse"></i> Incident Reports
</a>
<a href="{{ route('notifications.index') }}">
    <i class="bi bi-bell"></i> Notifications
</a>

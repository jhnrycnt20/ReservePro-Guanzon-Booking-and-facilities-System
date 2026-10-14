<div class="rp-nav-group is-open" data-rp-nav-group>
    <button type="button" class="rp-nav-group-toggle" data-rp-nav-group-toggle aria-expanded="true">
        <span>Resort</span>
        <i class="bi bi-chevron-down" aria-hidden="true"></i>
    </button>
    <div class="rp-nav-group-panel" data-rp-nav-group-panel>
        <a href="{{ route('accommodations.browse') }}" class="{{ request()->routeIs('accommodations.*') ? 'active' : '' }}">
            <i class="bi bi-buildings"></i> Browse Rooms
        </a>
        <a href="{{ route('guest.bookings.index') }}" class="{{ request()->routeIs('guest.bookings.*') ? 'active' : '' }}">
            <i class="bi bi-calendar-check"></i> My Reservations
        </a>
        <a href="{{ route('guest.payments.index') }}" class="{{ request()->routeIs('guest.payments.*') ? 'active' : '' }}">
            <i class="bi bi-credit-card"></i> Payments
        </a>
    </div>
</div>

<div class="rp-nav-group is-open" data-rp-nav-group>
    <button type="button" class="rp-nav-group-toggle" data-rp-nav-group-toggle aria-expanded="true">
        <span>Support</span>
        <i class="bi bi-chevron-down" aria-hidden="true"></i>
    </button>
    <div class="rp-nav-group-panel" data-rp-nav-group-panel>
        <a href="{{ route('guest.incidents.index') }}" class="{{ request()->routeIs('guest.incidents.*') ? 'active' : '' }}">
            <i class="bi bi-exclamation-triangle"></i> Reports
        </a>
        <a href="{{ route('guest.feedback.index') }}" class="{{ request()->routeIs('guest.feedback.*') ? 'active' : '' }}">
            <i class="bi bi-star"></i> Feedback
        </a>
    </div>
</div>

<div class="rp-nav-group is-open" data-rp-nav-group>
    <button type="button" class="rp-nav-group-toggle" data-rp-nav-group-toggle aria-expanded="true">
        <span>My Account</span>
        <i class="bi bi-chevron-down" aria-hidden="true"></i>
    </button>
    <div class="rp-nav-group-panel" data-rp-nav-group-panel>
        <a href="#rpProfileModal" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}" data-bs-toggle="modal" data-bs-target="#rpProfileModal">
            <i class="bi bi-person-circle"></i> Manage Profile
        </a>
    </div>
</div>

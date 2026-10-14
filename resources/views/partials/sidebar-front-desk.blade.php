<a href="{{ route('front_desk.dashboard') }}" class="{{ request()->routeIs('front_desk.dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>
<a href="{{ route('front_desk.reservations.index') }}" class="{{ request()->routeIs('front_desk.reservations.*') ? 'active' : '' }}">
    <i class="bi bi-journal-text"></i> Reservations
</a>

<div class="rp-nav-group is-open" data-rp-nav-group>
    <button type="button" class="rp-nav-group-toggle" data-rp-nav-group-toggle aria-expanded="true">
        <span>Operations</span>
        <i class="bi bi-chevron-down" aria-hidden="true"></i>
    </button>
    <div class="rp-nav-group-panel" data-rp-nav-group-panel>
        <a href="{{ route('front_desk.incidents.index') }}" class="{{ request()->routeIs('front_desk.incidents.*') ? 'active' : '' }}">
            <i class="bi bi-clipboard2-pulse"></i> Incident Reports
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

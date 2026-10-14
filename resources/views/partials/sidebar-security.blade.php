<a href="{{ route('security.dashboard') }}" class="{{ request()->routeIs('security.dashboard') ? 'active' : '' }}">
    <i class="bi bi-shield-shaded"></i> Dashboard
</a>

<div class="rp-nav-group is-open" data-rp-nav-group>
    <button type="button" class="rp-nav-group-toggle" data-rp-nav-group-toggle aria-expanded="true">
        <span>Investigations</span>
        <i class="bi bi-chevron-down" aria-hidden="true"></i>
    </button>
    <div class="rp-nav-group-panel" data-rp-nav-group-panel>
        <a href="{{ route('security.incidents.index', ['status' => 'pending']) }}" class="{{ request('status') === 'pending' ? 'active' : '' }}">
            <i class="bi bi-hourglass-split"></i> Pending Reports
        </a>
        <a href="{{ route('security.incidents.index') }}" class="{{ request()->routeIs('security.incidents.*') && !request('status') ? 'active' : '' }}">
            <i class="bi bi-list-ul"></i> All Reports
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

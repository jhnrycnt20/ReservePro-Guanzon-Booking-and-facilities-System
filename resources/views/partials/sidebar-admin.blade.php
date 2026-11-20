<a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    Dashboard
</a>

<div class="rp-nav-group is-open" data-rp-nav-group>
    <button type="button" class="rp-nav-group-toggle" data-rp-nav-group-toggle aria-expanded="true">
        <span>Management</span>
        <i class="bi bi-chevron-down" aria-hidden="true"></i>
    </button>
    <div class="rp-nav-group-panel" data-rp-nav-group-panel>
        <a href="{{ route('admin.accommodations.index') }}" class="{{ request()->routeIs('admin.accommodations.*') ? 'active' : '' }}">
            Accommodations
        </a>
        <a href="{{ route('admin.promos.index') }}" class="{{ request()->routeIs('admin.promos.*') ? 'active' : '' }}">
            Promo Codes
        </a>
        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            Users
        </a>
    </div>
</div>

<div class="rp-nav-group is-open" data-rp-nav-group>
    <button type="button" class="rp-nav-group-toggle" data-rp-nav-group-toggle aria-expanded="true">
        <span>Insights</span>
        <i class="bi bi-chevron-down" aria-hidden="true"></i>
    </button>
    <div class="rp-nav-group-panel" data-rp-nav-group-panel>
        <a href="{{ route('admin.analytics.index') }}" class="{{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
            Analytics
        </a>
        <a href="{{ route('admin.contact-messages.index') }}" class="{{ request()->routeIs('admin.contact-messages.*') ? 'active' : '' }}">
            Contact Messages
        </a>
        <a href="{{ route('admin.feedback.index') }}" class="{{ request()->routeIs('admin.feedback.*') ? 'active' : '' }}">
            Feedback
        </a>
        <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
            Incident Reports
        </a>
    </div>
</div>

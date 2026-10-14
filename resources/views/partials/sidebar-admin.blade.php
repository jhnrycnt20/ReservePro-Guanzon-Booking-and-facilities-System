<a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>

<div class="rp-nav-group is-open" data-rp-nav-group>
    <button type="button" class="rp-nav-group-toggle" data-rp-nav-group-toggle aria-expanded="true">
        <span>Management</span>
        <i class="bi bi-chevron-down" aria-hidden="true"></i>
    </button>
    <div class="rp-nav-group-panel" data-rp-nav-group-panel>
        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Users
        </a>
        <a href="{{ route('admin.accommodations.index') }}" class="{{ request()->routeIs('admin.accommodations.*') ? 'active' : '' }}">
            <i class="bi bi-building"></i> Accommodations
        </a>
        <a href="{{ route('admin.promos.index') }}" class="{{ request()->routeIs('admin.promos.*') ? 'active' : '' }}">
            <i class="bi bi-ticket-perforated"></i> Promo Codes
        </a>
    </div>
</div>

<div class="rp-nav-group is-open" data-rp-nav-group>
    <button type="button" class="rp-nav-group-toggle" data-rp-nav-group-toggle aria-expanded="true">
        <span>Insights</span>
        <i class="bi bi-chevron-down" aria-hidden="true"></i>
    </button>
    <div class="rp-nav-group-panel" data-rp-nav-group-panel>
        <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
            <i class="bi bi-clipboard-data"></i> Incident Reports
        </a>
        <a href="{{ route('admin.analytics.index') }}" class="{{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
            <i class="bi bi-graph-up"></i> Analytics
        </a>
        <a href="{{ route('admin.feedback.index') }}" class="{{ request()->routeIs('admin.feedback.*') ? 'active' : '' }}">
            <i class="bi bi-chat-quote"></i> Feedback
        </a>
        <a href="{{ route('admin.contact-messages.index') }}" class="{{ request()->routeIs('admin.contact-messages.*') ? 'active' : '' }}">
            <i class="bi bi-envelope"></i> Contact Messages
        </a>
        <a href="#rpSettingsModal" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" data-bs-toggle="modal" data-bs-target="#rpSettingsModal">
            <i class="bi bi-gear"></i> Settings
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

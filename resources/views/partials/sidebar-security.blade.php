<a href="{{ route('security.dashboard') }}" class="{{ request()->routeIs('security.dashboard') ? 'active' : '' }}">
    <i class="bi bi-shield-shaded"></i> Dashboard
</a>
<div class="nav-section">Investigations</div>
<a href="{{ route('security.incidents.index', ['status' => 'pending']) }}" class="{{ request('status') === 'pending' ? 'active' : '' }}">
    <i class="bi bi-hourglass-split"></i> Pending Reports
</a>
<a href="{{ route('security.incidents.index') }}" class="{{ request()->routeIs('security.incidents.*') && !request('status') ? 'active' : '' }}">
    <i class="bi bi-list-ul"></i> All Reports
</a>
<a href="{{ route('notifications.index') }}">
    <i class="bi bi-bell"></i> Notifications
</a>

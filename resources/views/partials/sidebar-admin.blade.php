<a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>
<div class="nav-section">Management</div>
<a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
    <i class="bi bi-people"></i> Users & Staff
</a>
<a href="{{ route('admin.accommodations.index') }}" class="{{ request()->routeIs('admin.accommodations.*') ? 'active' : '' }}">
    <i class="bi bi-building"></i> Accommodations
</a>
<div class="nav-section">Insights</div>
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
<a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
    <i class="bi bi-gear"></i> Settings
</a>

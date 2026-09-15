<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Total Reservations</div><div class="value">{{ $stats['total_reservations'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Today</div><div class="value">{{ $stats['today_reservations'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Total Guests</div><div class="value">{{ $stats['total_guests'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Revenue</div><div class="value" style="font-size:1.3rem;">₱{{ number_format($stats['total_revenue'] ?? 0, 0) }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Available</div><div class="value">{{ $stats['available'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Occupied</div><div class="value">{{ $stats['occupied'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Pending Reports</div><div class="value">{{ $stats['pending_reports'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Resolved</div><div class="value">{{ $stats['resolved_reports'] ?? 0 }}</div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="rp-card">
            <h2 class="h5 mb-3">Reservations per Month</h2>
            <canvas id="reservationsChart" height="180"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="rp-card">
            <h2 class="h5 mb-3">Revenue per Month</h2>
            <canvas id="revenueChart" height="180"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="rp-card">
            <h2 class="h5 mb-3">Incident Reports by Type</h2>
            <canvas id="incidentTypeChart" height="180"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="rp-card">
            <h2 class="h5 mb-3">Report Status Distribution</h2>
            <canvas id="incidentStatusChart" height="180"></canvas>
        </div>
    </div>
</div>

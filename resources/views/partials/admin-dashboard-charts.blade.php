@php
    $months = collect($charts['months'] ?? []);
    $reservationMonths = collect($charts['reservations'] ?? []);
    $revenueMonths = collect($charts['revenue'] ?? []);
    $incidentTypes = $charts['incident_types'] ?? [];
    $incidentStatuses = $charts['incident_statuses'] ?? [];
    $incidentTypeTotal = array_sum($incidentTypes);
    $incidentStatusTotal = array_sum($incidentStatuses);
@endphp

<div class="rp-analytics-page">
    <div class="rp-analytics-intro">
        <div><h2>Overview</h2></div>
        <span><i class="bi bi-calendar3" aria-hidden="true"></i> {{ now()->format('M j, Y') }}</span>
    </div>

    <section class="rp-analytics-kpis" aria-label="Key figures">
        <div class="rp-analytics-kpi rp-analytics-kpi-primary"><span>Verified revenue</span><strong>₱{{ number_format($stats['total_revenue'] ?? 0, 0) }}</strong><small>From verified payments · all time</small></div>
        <div class="rp-analytics-kpi"><span>Reservations</span><strong>{{ number_format($stats['total_reservations'] ?? 0) }}</strong><small><b>{{ $stats['today_reservations'] ?? 0 }}</b> today</small></div>
        <div class="rp-analytics-kpi"><span>Occupancy</span><strong>{{ number_format($charts['occupancy_rate'] ?? 0, 1) }}%</strong><small><b>{{ $stats['occupied'] ?? 0 }}</b> occupied · <b>{{ $stats['available'] ?? 0 }}</b> available</small></div>
        <div class="rp-analytics-kpi"><span>Guests</span><strong>{{ number_format($stats['total_guests'] ?? 0) }}</strong><small>Registered guest profiles</small></div>
    </section>

    <section class="rp-analytics-section" aria-labelledby="rpAnalyticsTrendsTitle">
        <div class="rp-analytics-section-head"><div><h2 id="rpAnalyticsTrendsTitle">Monthly trends</h2></div><span>{{ $months->first() }} – {{ $months->last() }}</span></div>
        <div class="rp-analytics-chart-grid">
            <article class="rp-analytics-panel">
                <div class="rp-analytics-panel-head"><div><h3>Reservations</h3><p>Bookings created each month</p></div><strong>{{ number_format($reservationMonths->sum()) }} <small>in period</small></strong></div>
                @if($reservationMonths->sum() > 0)<div class="rp-analytics-chart"><canvas id="reservationsChart" role="img" aria-label="Monthly reservation totals"></canvas></div>
                @else<p class="rp-analytics-empty">No reservations in this period.</p>@endif
                <details class="rp-analytics-data"><summary>View monthly data</summary><table><thead><tr><th scope="col">Month</th><th scope="col">Reservations</th></tr></thead><tbody>
                    @foreach($months as $index => $month)<tr><td>{{ $month }}</td><td>{{ number_format($reservationMonths[$index] ?? 0) }}</td></tr>@endforeach
                </tbody></table></details>
            </article>
            <article class="rp-analytics-panel">
                <div class="rp-analytics-panel-head"><div><h3>Verified revenue</h3><p>Payments recorded each month</p></div><strong>₱{{ number_format($revenueMonths->sum(), 0) }} <small>in period</small></strong></div>
                @if($revenueMonths->sum() > 0)<div class="rp-analytics-chart"><canvas id="revenueChart" role="img" aria-label="Monthly verified revenue in pesos"></canvas></div>
                @else<p class="rp-analytics-empty">No verified payments in this period.</p>@endif
                <details class="rp-analytics-data"><summary>View monthly data</summary><table><thead><tr><th scope="col">Month</th><th scope="col">Revenue</th></tr></thead><tbody>
                    @foreach($months as $index => $month)<tr><td>{{ $month }}</td><td>₱{{ number_format($revenueMonths[$index] ?? 0, 0) }}</td></tr>@endforeach
                </tbody></table></details>
            </article>
        </div>
    </section>

    <section class="rp-analytics-section" aria-labelledby="rpAnalyticsReportsTitle">
        <div class="rp-analytics-section-head"><div><h2 id="rpAnalyticsReportsTitle">Incident reports</h2></div><a href="{{ route('admin.reports.index') }}">View reports <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>
        <div class="rp-analytics-breakdown-grid">
            <article class="rp-analytics-panel">
                <div class="rp-analytics-panel-head"><div><h3>By type</h3><p>{{ number_format($incidentTypeTotal) }} reports total</p></div></div>
                @forelse($incidentTypes as $type => $count)
                    <div class="rp-analytics-breakdown-row"><div><span>{{ str_replace('_', ' ', ucfirst($type)) }}</span><strong>{{ number_format($count) }}</strong></div><span class="rp-analytics-track"><span style="width: {{ $incidentTypeTotal ? ($count / $incidentTypeTotal) * 100 : 0 }}%"></span></span></div>
                @empty<p class="rp-analytics-breakdown-empty">No reports yet.</p>@endforelse
            </article>
            <article class="rp-analytics-panel">
                <div class="rp-analytics-panel-head"><div><h3>By status</h3><p>Where reports stand now</p></div></div>
                <div class="rp-analytics-report-summary"><span><strong>{{ $stats['pending_reports'] ?? 0 }}</strong> Pending</span><span><strong>{{ $stats['resolved_reports'] ?? 0 }}</strong> Resolved</span></div>
                @forelse($incidentStatuses as $status => $count)
                    <div class="rp-analytics-breakdown-row"><div><span>{{ str_replace('_', ' ', ucfirst($status)) }}</span><strong>{{ number_format($count) }}</strong></div><span class="rp-analytics-track"><span style="width: {{ $incidentStatusTotal ? ($count / $incidentStatusTotal) * 100 : 0 }}%"></span></span></div>
                @empty<p class="rp-analytics-breakdown-empty">No reports yet.</p>@endforelse
            </article>
        </div>
    </section>
</div>

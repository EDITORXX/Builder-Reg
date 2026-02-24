<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h2 class="card-title">{{ $sectionLabel }}</h2>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('tenant.reports.index', $tenant->slug) }}" style="margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-end;">
            <div style="min-width: 140px;">
                <label for="date_from" style="display: block; font-size: 0.875rem; margin-bottom: 0.25rem;">From date</label>
                <input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);">
            </div>
            <div style="min-width: 140px;">
                <label for="date_to" style="display: block; font-size: 0.875rem; margin-bottom: 0.25rem;">To date</label>
                <input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);">
            </div>
            <div style="min-width: 180px;">
                <label for="project_id" style="display: block; font-size: 0.875rem; margin-bottom: 0.25rem;">Project</label>
                <select id="project_id" name="project_id" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);">
                    <option value="">All projects</option>
                    @foreach($projects ?? [] as $p)
                        <option value="{{ optional($p)->id }}" {{ request('project_id') == optional($p)->id ? 'selected' : '' }}>{{ optional($p)->name ?? '' }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary">Apply filters</button>
        </form>

        <h3 style="font-size: 1rem; margin: 0 0 0.75rem 0;">Leads</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div>
                <p style="font-size: 0.875rem; font-weight: 600; margin: 0 0 0.5rem 0;">By project</p>
                @php $byProject = $reportsLeads['by_project'] ?? collect(); @endphp
                @if($byProject->isEmpty())
                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">No data.</p>
                @else
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                                <th style="padding: 0.5rem 0;">Project</th>
                                <th style="padding: 0.5rem 0;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($byProject as $row)
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 0.5rem 0;">{{ $projects->firstWhere('id', $row->project_id)?->name ?? '—' }}</td>
                                    <td style="padding: 0.5rem 0;">{{ $row->total }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            <div>
                <p style="font-size: 0.875rem; font-weight: 600; margin: 0 0 0.5rem 0;">By status</p>
                @php $byStatus = $reportsLeads['by_status'] ?? collect(); @endphp
                @if($byStatus->isEmpty())
                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">No data.</p>
                @else
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                                <th style="padding: 0.5rem 0;">Status</th>
                                <th style="padding: 0.5rem 0;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($byStatus as $row)
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 0.5rem 0;">{{ str_replace('_', ' ', $row->status ?? '—') }}</td>
                                    <td style="padding: 0.5rem 0;">{{ $row->total }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <h3 style="font-size: 1rem; margin: 1rem 0 0.75rem 0;">Active locks</h3>
        @php $locks = $reportsLocks ?? collect(); @endphp
        @if($locks->isEmpty())
            <p style="margin: 0 0 1rem 0; color: var(--text-secondary); font-size: 0.875rem;">No active locks.</p>
        @else
            <div style="overflow-x: auto; margin-bottom: 1.5rem;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                            <th style="padding: 0.5rem 0;">Project</th>
                            <th style="padding: 0.5rem 0;">Customer / Mobile</th>
                            <th style="padding: 0.5rem 0;">End date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($locks as $lock)
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 0.5rem 0;">{{ $lock->project?->name ?? '—' }}</td>
                                <td style="padding: 0.5rem 0;">{{ $lock->lead?->customer?->name ?? $lock->customer_mobile ?? '—' }}</td>
                                <td style="padding: 0.5rem 0;">{{ $lock->end_at?->format('M j, Y') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <h3 style="font-size: 1rem; margin: 1rem 0 0.75rem 0;">Channel partner performance</h3>
        @php $cpPerf = $reportsCpPerformance ?? collect(); @endphp
        @if($cpPerf->isEmpty())
            <p style="margin: 0 0 1rem 0; color: var(--text-secondary); font-size: 0.875rem;">No data.</p>
        @else
            <div style="overflow-x: auto; margin-bottom: 1.5rem;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                            <th style="padding: 0.5rem 0;">Channel partner</th>
                            <th style="padding: 0.5rem 0;">Leads</th>
                            <th style="padding: 0.5rem 0;">Booked</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cpPerf as $row)
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 0.5rem 0;">{{ $row->cp_name ?? '—' }}</td>
                                <td style="padding: 0.5rem 0;">{{ $row->lead_count ?? 0 }}</td>
                                <td style="padding: 0.5rem 0;">{{ $row->booked_count ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <h3 style="font-size: 1rem; margin: 1rem 0 0.75rem 0;">Conversion</h3>
        @php $conv = $reportsConversion ?? []; @endphp
        <div class="stat-grid" style="margin-bottom: 0;">
            <div class="stat-card">
                <div class="stat-label">Visit done</div>
                <div class="stat-value" style="font-size: 1.125rem;">{{ $conv['visit_done_count'] ?? 0 }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Booked</div>
                <div class="stat-value" style="font-size: 1.125rem;">{{ $conv['booked_count'] ?? 0 }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Conversion rate</div>
                <div class="stat-value" style="font-size: 1.125rem;">{{ number_format($conv['conversion_rate_percent'] ?? 0, 1) }}%</div>
            </div>
        </div>
    </div>
</div>

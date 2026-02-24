<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h2 class="card-title">{{ $sectionLabel }}</h2>
    </div>
    <div class="card-body">
        @if(isset($tenant) && $tenant)
            <p style="margin: 0 0 1rem 0; font-size: 0.875rem; color: var(--text-secondary);">Default lock period: <strong>{{ $tenant->default_lock_days ?? 30 }}</strong> days. Visit confirm hone par customer is project par utne din ke liye lock ho jata hai. Lock khatam hone ke baad koi bhi dusra CP us customer ko register kar sakta hai; lead us naye CP ke lead section mein dikhega.</p>
        @endif

        <h3 style="font-size: 1rem; margin: 1rem 0 0.75rem 0;">Lock period project-wise</h3>
        <p style="margin: 0 0 0.75rem 0; font-size: 0.875rem; color: var(--text-secondary);">Har project ke liye lock days set kar sakte hain. Blank = builder default use hoga.</p>
        @php $projectsList = $projects ?? collect(); @endphp
        @if($projectsList->isEmpty())
            <p style="margin: 0 0 1rem 0; font-size: 0.875rem; color: var(--text-secondary);">No projects.</p>
        @else
            <div style="overflow-x: auto; margin-bottom: 1.5rem;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                            <th style="padding: 0.5rem 0;">Project</th>
                            <th style="padding: 0.5rem 0;">Lock days</th>
                            <th style="padding: 0.5rem 0;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projectsList as $p)
                            @php $lockDays = $p->lock_days_override ?? ($tenant->default_lock_days ?? 30); @endphp
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 0.5rem 0;">{{ $p->name ?? '—' }}</td>
                                <td style="padding: 0.5rem 0;">{{ $lockDays }} days {{ $p->lock_days_override ? '' : '(default)' }}</td>
                                <td style="padding: 0.5rem 0;">
                                    <a href="{{ route('tenant.projects.edit', [$tenant->slug, $p]) }}" style="font-size: 0.875rem;">Edit project</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <form method="GET" action="{{ route('tenant.locks.index', $tenant->slug) }}" style="margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-end;">
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

        <h3 style="font-size: 1rem; margin: 0 0 0.75rem 0;">Active locks</h3>
        @php $locksList = $locks ?? collect(); @endphp
        @if($locksList->isEmpty())
            <p style="margin: 0 0 1rem 0; color: var(--text-secondary); font-size: 0.875rem;">No active locks.</p>
        @else
            <div style="overflow-x: auto; margin-bottom: 1.5rem;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                            <th style="padding: 0.5rem 0;">Project</th>
                            <th style="padding: 0.5rem 0;">Customer / Mobile</th>
                            <th style="padding: 0.5rem 0;">Channel partner</th>
                            <th style="padding: 0.5rem 0;">Start date</th>
                            <th style="padding: 0.5rem 0;">End date</th>
                            <th style="padding: 0.5rem 0;">Days remaining</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($locksList as $lock)
                            @php
                                $daysRemaining = $lock->end_at ? max(0, (int) now()->diffInDays($lock->end_at, false)) : '—';
                            @endphp
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 0.5rem 0;">{{ $lock->project?->name ?? '—' }}</td>
                                <td style="padding: 0.5rem 0;">{{ $lock->lead?->customer?->name ?? $lock->customer_mobile ?? '—' }}</td>
                                <td style="padding: 0.5rem 0;">{{ $lock->channelPartner?->user?->name ?? $lock->channelPartner?->firm_name ?? 'Walk-in' }}</td>
                                <td style="padding: 0.5rem 0;">{{ $lock->start_at?->format('M j, Y') ?? '—' }}</td>
                                <td style="padding: 0.5rem 0;">{{ $lock->end_at?->format('M j, Y') ?? '—' }}</td>
                                <td style="padding: 0.5rem 0;">{{ $daysRemaining }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

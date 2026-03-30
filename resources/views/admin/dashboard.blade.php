@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Users</div>
            <div class="stat-value">{{ $stats['total_users'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Events</div>
            <div class="stat-value">{{ $stats['total_activities'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Active Events</div>
            <div class="stat-value">{{ $stats['active_activities'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Upcoming Sessions</div>
            <div class="stat-value">{{ $stats['upcoming_sessions'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Outstanding Fees</div>
            <div class="stat-value">{{ number_format($stats['outstanding_fees'], 2) }}</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        <div class="card">
            <h3 style="margin-bottom: 16px;">Recent Users</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($latest_users as $user)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    @if(!empty($latestUserImageMap[$user->id]))
                                        <img src="{{ $latestUserImageMap[$user->id] }}" alt="User image" style="width: 34px; height: 34px; border-radius: 999px; object-fit: cover;">
                                    @else
                                        <span style="width: 34px; height: 34px; border-radius: 999px; background: #e2e8f0; display: inline-flex; align-items: center; justify-content: center; color: #64748b;">
                                            <i class="fas fa-user"></i>
                                        </span>
                                    @endif
                                    {{ $user->name }} {{ $user->surname }}
                                </div>
                            </td>
                            <td><span class="badge {{ $user->role === 'admin' ? 'badge-danger' : 'badge-info' }}">{{ ucfirst($user->role) }}</span></td>
                            <td><a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline">View</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 16px; text-align: right;">
                <a href="{{ route('admin.users.index') }}" style="font-size: 13px; font-weight: 600; color: var(--primary);">View all users</a>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 16px;">Latest Events</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($latest_activities as $activity)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    @if(!empty($latestActivityImageMap[$activity->id]))
                                        <img src="{{ $latestActivityImageMap[$activity->id] }}" alt="Activity image" style="width: 34px; height: 34px; border-radius: 8px; object-fit: cover;">
                                    @else
                                        <span style="width: 34px; height: 34px; border-radius: 8px; background: #e2e8f0; display: inline-flex; align-items: center; justify-content: center; color: #64748b;">
                                            <i class="fas fa-image"></i>
                                        </span>
                                    @endif
                                    {{ Str::limit($activity->title, 30) }}
                                </div>
                            </td>
                            <td>
                                @if($activity->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-warning">Inactive</span>
                                @endif
                            </td>
                            <td><a href="{{ route('admin.activities.edit', $activity) }}" class="btn btn-sm btn-outline">Edit</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 16px; text-align: right;">
                <a href="{{ route('admin.activities.index') }}" style="font-size: 13px; font-weight: 600; color: var(--primary);">View all events</a>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 24px;">
        <h3 style="margin-bottom: 12px;">Self Check-in via Tablet</h3>
        <p style="color: var(--text-muted); margin-bottom: 14px;">
            Launch today’s event in public tablet mode. The tablet will show only a QR code and instructions for self check-in.
        </p>
        <a href="{{ route('admin.checkin.tablet') }}" class="btn btn-primary">
            <i class="fas fa-tablet-alt"></i> Open Tablet Check-in
        </a>
    </div>

    <div class="card" style="margin-top: 24px;">
        <h3 style="margin-bottom: 12px;">Analytics Insights</h3>
        <p style="color: var(--text-muted); margin-bottom: 14px;">
            View peak days/times, age-group demand, and cancellation rates in one place.
        </p>
        <a href="{{ route('admin.analytics') }}" class="btn btn-primary">
            <i class="fas fa-chart-column"></i> Open Insights Dashboard
        </a>
    </div>

    @if(auth()->user()->role === 'admin')
        <div class="card" style="margin-top: 24px;">
            <h3 style="margin-bottom: 12px;">Blacklist Management</h3>
            <p style="color: var(--text-muted); margin-bottom: 14px;">
                Manage temporary child restrictions to prevent registrations while penalties are active.
            </p>
            <a href="{{ route('admin.blacklist.index') }}" class="btn btn-primary">
                <i class="fas fa-user-slash"></i> Manage Blacklist
            </a>
        </div>
    @endif

    <div class="card" style="margin-top: 24px;">
        <h3 style="margin-bottom: 16px;">One-Click Backup / Restore</h3>
        <p style="color: var(--text-muted); margin-bottom: 16px;">
            Backup exports your current database to `storage/app/private/backups`. Restore will replace data with the latest backup.
        </p>
        <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 14px;">
            <form action="{{ route('admin.system.backup') }}" method="POST">
                @csrf
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-database"></i> Create Backup
                </button>
            </form>
            <form action="{{ route('admin.system.restore_latest') }}" method="POST" onsubmit="return confirm('Restore latest backup? This will overwrite current data.');">
                @csrf
                <button class="btn btn-outline" type="submit">
                    <i class="fas fa-rotate-left"></i> Restore Latest Backup
                </button>
            </form>
        </div>
        @if(!empty($latest_backups))
            <div style="border-top: 1px solid var(--border); padding-top: 12px;">
                <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">Recent backups:</p>
                @foreach($latest_backups as $backup)
                    <div style="font-size: 13px; margin-bottom: 6px;">
                        <strong>{{ $backup['file'] }}</strong> ({{ number_format($backup['size'] / 1024, 1) }} KB) - {{ $backup['modified_at'] }}
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div style="display: flex; justify-content: flex-end; margin-bottom: 24px;">
        <a href="{{ route('admin.stats') }}" class="btn btn-outline">
            <i class="fas fa-file-export"></i> Export System Stats (CSV)
        </a>
    </div>
@endsection

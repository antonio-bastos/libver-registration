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
                            <td>{{ $user->name }} {{ $user->surname }}</td>
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
                            <td>{{ Str::limit($activity->title, 30) }}</td>
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
        <div style="display: flex; justify-content: flex-end; margin-bottom: 24px;">
        <a href="{{ route('admin.stats') }}" class="btn btn-outline">
            <i class="fas fa-file-export"></i> Export System Stats (CSV)
        </a>
    </div>
@endsection

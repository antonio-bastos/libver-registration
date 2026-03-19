@extends('layouts.admin')

@section('title', 'Manage Events')

@section('content')
    <div class="top-bar">
        <h1>Events Management</h1>
        <a href="{{ route('admin.activities.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Event
        </a>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('admin.activities.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; margin-bottom: 24px;">
            <div>
                <label style="font-size: 12px; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 4px;">Search</label>
                <input type="text" name="search" placeholder="Event title..." value="{{ request('search') }}">
            </div>
            <div>
                <label style="font-size: 12px; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 4px;">Status</label>
                <select name="status">
                    <option value="">All Status</option>
                    <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                    <option value="past" {{ request('status') === 'past' ? 'selected' : '' }}>Past</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div>
                <label style="font-size: 12px; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 4px;">Category</label>
                <select name="category">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size: 12px; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 4px;">Venue</label>
                <select name="venue">
                    <option value="">All Venues</option>
                    @foreach($venues as $venue)
                        <option value="{{ $venue }}" {{ request('venue') === $venue ? 'selected' : '' }}>{{ $venue }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display: flex; align-items: flex-end;">
                <button type="submit" class="btn btn-outline" style="width: 100%; justify-content: center;">Filter</button>
            </div>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Event Details</th>
                        <th>Status</th>
                        <th>Capacity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activities as $activity)
                    <tr style="{{ $activity->end_at->isPast() ? 'opacity: 0.7; background: #f9fafb;' : '' }}">
                        <td>
                            <div style="font-weight: 600; font-size: 15px;">{{ $activity->title }}</div>
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                                <i class="far fa-calendar-alt"></i> {{ $activity->start_at->format('M d, Y H:i') }}
                                <span style="margin: 0 4px;">•</span>
                                <i class="fas fa-map-marker-alt"></i> {{ $activity->location }}
                            </div>
                            <div style="font-size: 11px; color: var(--primary); margin-top: 2px; font-weight: 600;">
                                {{ $activity->type }}
                            </div>
                        </td>
                        <td>
                            @if($activity->end_at->isPast())
                                <span class="badge" style="background: #e2e8f0; color: #475569;">Past</span>
                            @elseif($activity->start_at->isFuture())
                                <span class="badge badge-info">Upcoming</span>
                            @else
                                <span class="badge badge-success">Ongoing</span>
                            @endif

                            @if(!$activity->is_active)
                                <span class="badge badge-warning" style="margin-top: 4px;">Draft</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $activity->registrations_count }} / {{ $activity->capacity }}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">Registered</div>
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <a href="{{ route('admin.activities.show_details', $activity) }}" class="btn btn-sm btn-outline" title="Manage Registrations">
                                    <i class="fas fa-users"></i>
                                </a>
                                <a href="{{ route('admin.activities.edit', $activity) }}" class="btn btn-sm btn-outline" title="Edit Event">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.activities.duplicate', $activity) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline" title="Duplicate Event">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.activities.destroy', $activity) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this event? All sessions and registrations will be removed.')" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline" style="color: var(--danger);" title="Delete Event">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 24px;">
            {{ $activities->appends(request()->query())->links() }}
        </div>
    </div>
@endsection

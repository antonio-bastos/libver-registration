@extends('layouts.admin')

@section('title', 'Archived Activities')

@section('content')
    <div class="top-bar">
        <h1>Archived Activities</h1>
        <form method="GET" action="{{ route('admin.activities.archived') }}" style="display:flex; gap:10px;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search archived activities...">
            <button type="submit" class="btn btn-outline">Search</button>
        </form>
    </div>

    <div class="card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Period</th>
                        <th>Archived At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($archivedActivities as $activity)
                        <tr>
                            <td>{{ $activity->title }}</td>
                            <td>{{ $activity->type }}</td>
                            <td>
                                {{ optional($activity->start_at)->format('M d, Y H:i') }}
                                -
                                {{ optional($activity->end_at)->format('M d, Y H:i') }}
                            </td>
                            <td>{{ optional($activity->archived_at)->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted);">No archived activities found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 24px;">
            {{ $archivedActivities->appends(request()->query())->links() }}
        </div>
    </div>
@endsection


@extends('layouts.admin')

@section('title', 'Global Search')

@section('content')
    <div class="top-bar">
        <h1>Global Search</h1>
        <form action="{{ route('admin.search') }}" method="GET" style="display:flex; gap:12px;">
            <input type="text" name="q" value="{{ $query }}" placeholder="Search names, phone, email" style="min-width:260px;">
            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>

    @if($query === '')
        <div class="card">
            <p style="color: var(--text-muted);">Enter at least one keyword to search across parents, children, and attendance history.</p>
        </div>
    @else
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
            <div class="card">
                <h3 style="margin-bottom: 12px;">Children</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Parent</th>
                                <th>Tags</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($children as $child)
                                <tr>
                                    <td>{{ $child->first_name }} {{ $child->last_name }}</td>
                                    <td>{{ $child->parent?->name }} {{ $child->parent?->surname }}</td>
                                    <td>
                                        @foreach(($child->tags ?? []) as $tag)
                                            <span class="badge badge-info">{{ $tag }}</span>
                                        @endforeach
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" style="text-align:center; color: var(--text-muted);">No children matched.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h3 style="margin-bottom: 12px;">Parents / Guardians</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $parent)
                                <tr>
                                    <td>{{ $parent->name }} {{ $parent->surname }}</td>
                                    <td>{{ $parent->email }}</td>
                                    <td>{{ $parent->phone ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" style="text-align:center; color: var(--text-muted);">No guardians matched.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h3 style="margin-bottom: 12px;">Registrations</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Participant</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registrations as $registration)
                                <tr>
                                    <td>{{ $registration->activity?->title }}</td>
                                    <td>{{ $registration->child?->first_name ?? $registration->user?->name }}</td>
                                    <td><span class="badge {{ $registration->status === 'confirmed' ? 'badge-success' : ($registration->status === 'waiting' ? 'badge-warning' : 'badge-danger') }}">{{ ucfirst($registration->status) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" style="text-align:center; color: var(--text-muted);">No registrations matched.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h3 style="margin-bottom: 12px;">Absence History</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Participant</th>
                                <th>Event</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($absences as $absence)
                                <tr>
                                    <td>{{ $absence->child?->first_name }} {{ $absence->child?->last_name }}</td>
                                    <td>{{ $absence->activity?->title }}</td>
                                    <td>{{ optional($absence->activity?->start_at)->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" style="text-align:center; color: var(--text-muted);">No absences matched.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection

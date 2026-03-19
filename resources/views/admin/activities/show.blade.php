@extends('layouts.admin')

@section('title', 'Activity Details')

@section('content')
    <div class="top-bar">
        <h1>{{ $activity->title }} - Management</h1>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('admin.activities.export', $activity) }}" class="btn btn-outline">
                <i class="fas fa-file-export"></i> Export CSV
            </a>
            <a href="{{ route('admin.activities.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to list
            </a>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-bottom: 20px;">Registrations</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Child</th>
                        <th>Parent</th>
                        <th>Status</th>
                        <th>Paid</th>
                        <th>Attended</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($registrations as $reg)
                    <tr>
                        <td>
                            <div style="font-weight: 600;">
                                @if($reg->child)
                                    {{ $reg->child->first_name }} {{ $reg->child->last_name }}
                                @else
                                    {{ $reg->user->name }} {{ $reg->user->surname }} (Self)
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($reg->child)
                                {{ $reg->child->parent->name }}<br>
                                <span style="font-size: 12px; color: var(--text-muted);">{{ $reg->child->parent->email }}</span>
                            @else
                                {{ $reg->user->name }}<br>
                                <span style="font-size: 12px; color: var(--text-muted);">{{ $reg->user->email }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $reg->status === 'confirmed' ? 'badge-success' : ($reg->status === 'waiting' ? 'badge-warning' : 'badge-danger') }}">
                                {{ ucfirst($reg->status) }}
                            </span>
                            @if($reg->position) <span style="font-size: 11px;">#{{ $reg->position }}</span> @endif
                        </td>
                        <td>
                            {{ number_format($reg->amount_paid, 2) }} / {{ number_format($reg->fee_amount, 2) }}
                        </td>
                        <td>
                            @if($reg->attended)
                                <span style="color: var(--success); font-weight: 600;">Yes</span>
                            @else
                                <span style="color: var(--danger); font-weight: 600;">No</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                @if($reg->status === 'waiting' || $reg->status === 'pending_approval')
                                    <form action="{{ route('admin.registrations.promote', $reg) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">Promote</button>
                                    </form>
                                @endif

                                @if($reg->status === 'confirmed' && !$reg->attended)
                                    <form action="{{ route('admin.registrations.absent', $reg) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline" style="color: var(--danger);">Mark Absent</button>
                                    </form>
                                @endif

                                @if($reg->fee_amount > 0)
                                    @if($reg->payment_status !== 'paid')
                                        <form action="{{ route('admin.registrations.mark_paid', $reg) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline" style="color: var(--success);"><i class="fas fa-check"></i> Mark Paid</button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.registrations.mark_unpaid', $reg) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline" style="color: var(--warning);"><i class="fas fa-undo"></i> Mark Unpaid</button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($registrations->isEmpty())
            <p style="text-align: center; color: var(--text-muted); padding: 40px;">No registrations found for this activity.</p>
        @endif
    </div>
@endsection

@extends('layouts.admin')

@section('title', 'User Details')

@section('content')
    <div class="top-bar">
        <h1 style="display: flex; align-items: center; gap: 12px;">
            @if(!empty($userImageUrl))
                <img src="{{ $userImageUrl }}" alt="User image" style="width: 44px; height: 44px; border-radius: 999px; object-fit: cover;">
            @else
                <span style="width: 44px; height: 44px; border-radius: 999px; background: #e2e8f0; display: inline-flex; align-items: center; justify-content: center; color: #64748b;">
                    <i class="fas fa-user"></i>
                </span>
            @endif
            User: {{ $user->name }} {{ $user->surname }}
        </h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to list
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 20px; padding: 15px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
        <div>
            <div class="card">
                <h3 style="margin-bottom: 20px;">Account Settings</h3>
                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Surname</label>
                        <input type="text" name="surname" value="{{ old('surname', $user->surname) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}">
                    </div>
                    <div class="form-group">
                        <label>Card Number</label>
                        <input type="text" name="card_number" value="{{ old('card_number', $user->card_number) }}">
                    </div>
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="dob" value="{{ old('dob', $user->dob ? $user->dob->format('Y-m-d') : '') }}">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Update Account</button>
                </form>

                <hr style="margin: 20px 0; border: 0; border-top: 1px solid var(--border);">

                <form action="{{ route('admin.users.role', $user) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="role">User Role</label>
                        <select name="role" id="role" onchange="this.form.submit()">
                            <option value="parent" {{ $user->role === 'parent' ? 'selected' : '' }}>Parent</option>
                            <option value="instructor" {{ $user->role === 'instructor' ? 'selected' : '' }}>Instructor</option>
                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                    <p style="font-size: 12px; color: var(--text-muted);">
                        Changing the role will take effect immediately.
                    </p>
                </form>
            </div>
        </div>

        <div>
            <div class="card">
                <h3 style="margin-bottom: 20px;">Registrations</h3>
                
                <div style="margin-bottom: 32px; padding-bottom: 24px; border-bottom: 2px solid var(--border);">
                    <h4 style="margin-bottom: 12px; color: var(--primary);">User Self-Registrations</h4>
                    <div class="table-container">
                        <table style="background: var(--bg); border-radius: 8px;">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Status</th>
                                    <th>Attendance</th>
                                    <th>Fee Due</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($user->registrations as $reg)
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            @if(!empty($activityImageMap[$reg->activity_id]))
                                                <img src="{{ $activityImageMap[$reg->activity_id] }}" alt="Activity image" style="width: 28px; height: 28px; border-radius: 6px; object-fit: cover;">
                                            @endif
                                            {{ $reg->activity->title }}
                                        </div>
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.registrations.status', $reg) }}" method="POST">
                                            @csrf
                                            <select name="status" onchange="this.form.submit()" style="font-size: 12px; padding: 4px; border-radius: 4px; border: 1px solid var(--border); min-width: 110px;">
                                                <option value="confirmed" {{ $reg->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                                <option value="waiting" {{ $reg->status === 'waiting' ? 'selected' : '' }}>Waiting</option>
                                                <option value="offer_sent" {{ $reg->status === 'offer_sent' ? 'selected' : '' }}>Offer Sent</option>
                                                <option value="canceled" {{ $reg->status === 'canceled' ? 'selected' : '' }}>Canceled</option>
                                                <option value="pending_approval" {{ $reg->status === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        @if($reg->attended === true)
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span class="badge badge-success">Attended</span>
                                                <form action="{{ route('admin.registrations.unmark_attendance', $reg) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline" title="Clear attendance record" style="padding: 2px 6px; font-size: 10px;">Clear</button>
                                                </form>
                                            </div>
                                        @elseif($reg->attended === false)
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span class="badge badge-danger">Absent</span>
                                                <form action="{{ route('admin.registrations.unmark_attendance', $reg) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline" title="Clear attendance record" style="padding: 2px 6px; font-size: 10px;">Clear</button>
                                                </form>
                                            </div>
                                        @else
                                            <form action="{{ route('admin.registrations.absent', $reg) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline" style="padding: 4px 8px; font-size: 11px;">Mark Absent</button>
                                            </form>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ number_format($reg->amount_paid, 2) }} / {{ number_format($reg->total_due, 2) }}</div>
                                        <div style="font-size: 11px; color: var(--text-muted);">Outstanding: {{ number_format($reg->outstanding_amount, 2) }}</div>
                                        @if($reg->absence_fine > 0)
                                            <div style="font-size: 11px; color: var(--danger);">Absence fine: {{ number_format($reg->absence_fine, 2) }}</div>
                                        @endif
                                        <div style="margin-top: 6px; display: flex; gap: 6px; flex-wrap: wrap;">
                                            @if($reg->payment_status !== 'paid')
                                                <form action="{{ route('admin.registrations.mark_paid', $reg) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline" style="padding: 3px 7px; font-size: 10px; color: var(--success);">Mark Paid</button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.registrations.mark_unpaid', $reg) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline" style="padding: 3px 7px; font-size: 10px; color: var(--warning);">Mark Unpaid</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $reg->created_at->format('M d, Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-muted);">No self-registrations found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <h4 style="margin-bottom: 16px;">Children & Their Registrations</h4>
                @forelse($user->children as $child)
                    <div style="margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid var(--border);">
                        <h4 style="margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                            @if(!empty($childImageMap[$child->id]))
                                <img src="{{ $childImageMap[$child->id] }}" alt="Child image" style="width: 36px; height: 36px; border-radius: 999px; object-fit: cover;">
                            @else
                                <span style="width: 36px; height: 36px; border-radius: 999px; background: #e2e8f0; display: inline-flex; align-items: center; justify-content: center; color: #64748b;">
                                    <i class="fas fa-child"></i>
                                </span>
                            @endif
                            {{ $child->first_name }} {{ $child->last_name }} ({{ $child->dob->age }} yrs)
                        </h4>

                        <div style="display: grid; grid-template-columns: 1fr auto; gap: 12px; margin-bottom: 12px;">
                            <form action="{{ route('admin.children.update', $child) }}" method="POST" style="display: grid; grid-template-columns: 1fr 130px auto; gap: 10px;">
                                @csrf
                                <input type="text" name="tags" placeholder="Tags: allergies, learning support..." value="{{ implode(', ', $child->tags ?? []) }}">
                                <input type="number" name="loyalty_points" min="0" value="{{ $child->loyalty_points }}" placeholder="Points">
                                <button type="submit" class="btn btn-outline">Save Tags/Points</button>
                            </form>
                            <form action="{{ route('admin.children.clear_restriction', $child) }}" method="POST" onsubmit="return confirm('Clear current restriction and reset absence count?');">
                                @csrf
                                <button class="btn btn-outline" type="submit"><i class="fas fa-unlock"></i> Clear Restriction</button>
                            </form>
                        </div>
                        <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 12px;">
                            Current absence count: {{ $child->absence_count }} · Restriction:
                            {{ $child->isRestricted() ? 'until ' . $child->restrictions_until->format('M d, Y') : 'none' }}
                        </p>
                        
                        <div class="table-container">
                            <table style="background: var(--bg); border-radius: 8px;">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Status</th>
                                        <th>Attendance</th>
                                        <th>Fee Due</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($child->registrations as $reg)
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                @if(!empty($activityImageMap[$reg->activity_id]))
                                                    <img src="{{ $activityImageMap[$reg->activity_id] }}" alt="Activity image" style="width: 28px; height: 28px; border-radius: 6px; object-fit: cover;">
                                                @endif
                                                {{ $reg->activity->title }}
                                            </div>
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.registrations.status', $reg) }}" method="POST">
                                                @csrf
                                                <select name="status" onchange="this.form.submit()" style="font-size: 12px; padding: 4px; border-radius: 4px; border: 1px solid var(--border); min-width: 110px;">
                                                    <option value="confirmed" {{ $reg->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                                    <option value="waiting" {{ $reg->status === 'waiting' ? 'selected' : '' }}>Waiting</option>
                                                    <option value="offer_sent" {{ $reg->status === 'offer_sent' ? 'selected' : '' }}>Offer Sent</option>
                                                    <option value="canceled" {{ $reg->status === 'canceled' ? 'selected' : '' }}>Canceled</option>
                                                    <option value="pending_approval" {{ $reg->status === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td>
                                            @if($reg->attended === true)
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <span class="badge badge-success">Attended</span>
                                                    <form action="{{ route('admin.registrations.unmark_attendance', $reg) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline" title="Clear attendance record" style="padding: 2px 6px; font-size: 10px;">Clear</button>
                                                    </form>
                                                </div>
                                            @elseif($reg->attended === false)
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <span class="badge badge-danger">Absent</span>
                                                    <form action="{{ route('admin.registrations.unmark_attendance', $reg) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline" title="Clear attendance record" style="padding: 2px 6px; font-size: 10px;">Clear</button>
                                                    </form>
                                                </div>
                                            @else
                                                <form action="{{ route('admin.registrations.absent', $reg) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline" style="padding: 4px 8px; font-size: 11px;">Mark Absent</button>
                                                </form>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ number_format($reg->amount_paid, 2) }} / {{ number_format($reg->total_due, 2) }}</div>
                                            <div style="font-size: 11px; color: var(--text-muted);">Outstanding: {{ number_format($reg->outstanding_amount, 2) }}</div>
                                            @if($reg->absence_fine > 0)
                                                <div style="font-size: 11px; color: var(--danger);">Absence fine: {{ number_format($reg->absence_fine, 2) }}</div>
                                            @endif
                                            <div style="margin-top: 6px; display: flex; gap: 6px; flex-wrap: wrap;">
                                                @if($reg->payment_status !== 'paid')
                                                    <form action="{{ route('admin.registrations.mark_paid', $reg) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline" style="padding: 3px 7px; font-size: 10px; color: var(--success);">Mark Paid</button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('admin.registrations.mark_unpaid', $reg) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline" style="padding: 3px 7px; font-size: 10px; color: var(--warning);">Mark Unpaid</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $reg->created_at->format('M d, Y') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" style="text-align: center; color: var(--text-muted);">No registrations found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <p style="color: var(--text-muted);">This user has no children registered.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection

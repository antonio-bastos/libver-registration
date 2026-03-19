@extends('layouts.admin')

@section('title', 'User Details')

@section('content')
    <div class="top-bar">
        <h1>User: {{ $user->name }} {{ $user->surname }}</h1>
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
                
                {{-- SELF REGISTRATIONS --}}
                <div style="margin-bottom: 32px; padding-bottom: 24px; border-bottom: 2px solid var(--border);">
                    <h4 style="margin-bottom: 12px; color: var(--primary);">User Self-Registrations</h4>
                    <div class="table-container">
                        <table style="background: var(--bg); border-radius: 8px;">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Status</th>
                                    <th>Attendance</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($user->registrations as $reg)
                                <tr>
                                    <td>{{ $reg->activity->title }}</td>
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
                                    <td>{{ $reg->created_at->format('M d, Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--text-muted);">No self-registrations found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- CHILD REGISTRATIONS --}}
                <h4 style="margin-bottom: 16px;">Children & Their Registrations</h4>
                @forelse($user->children as $child)
                    <div style="margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid var(--border);">
                        <h4 style="margin-bottom: 12px;">{{ $child->first_name }} {{ $child->last_name }} ({{ $child->dob->age }} yrs)</h4>
                        
                        <div class="table-container">
                            <table style="background: var(--bg); border-radius: 8px;">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Status</th>
                                        <th>Attendance</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($child->registrations as $reg)
                                    <tr>
                                        <td>{{ $reg->activity->title }}</td>
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
                                        <td>{{ $reg->created_at->format('M d, Y') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" style="text-align: center; color: var(--text-muted);">No registrations found.</td>
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

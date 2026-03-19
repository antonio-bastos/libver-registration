@extends('layouts.admin')

@section('title', 'User Details')

@section('content')
    <div class="top-bar">
        <h1>User: {{ $user->name }} {{ $user->surname }}</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to list
        </a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
        <div>
            <div class="card">
                <h3 style="margin-bottom: 20px;">Account Settings</h3>
                <div style="font-size: 14px; line-height: 2;">
                    <div><strong>Email:</strong> {{ $user->email }}</div>
                    <div><strong>Phone:</strong> {{ $user->phone ?? 'N/A' }}</div>
                    <div><strong>Card Number:</strong> {{ $user->card_number ?? 'N/A' }}</div>
                    <div><strong>Joined:</strong> {{ $user->created_at->format('M d, Y') }}</div>
                </div>

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
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($user->registrations as $reg)
                                <tr>
                                    <td>{{ $reg->activity->title }}</td>
                                    <td><span class="badge {{ $reg->status === 'confirmed' ? 'badge-success' : ($reg->status === 'waiting' ? 'badge-warning' : 'badge-danger') }}">{{ ucfirst($reg->status) }}</span></td>
                                    <td>{{ $reg->created_at->format('M d, Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" style="text-align: center; color: var(--text-muted);">No self-registrations found.</td>
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
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($child->registrations as $reg)
                                    <tr>
                                        <td>{{ $reg->activity->title }}</td>
                                        <td><span class="badge {{ $reg->status === 'confirmed' ? 'badge-success' : 'badge-warning' }}">{{ ucfirst($reg->status) }}</span></td>
                                        <td>{{ $reg->created_at->format('M d, Y') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" style="text-align: center; color: var(--text-muted);">No registrations found.</td>
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

@extends('layouts.admin')

@section('title', 'Manage Users')

@section('content')
    <div class="top-bar">
        <h1>Users Management</h1>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('admin.users.index') }}" style="display: flex; gap: 16px; margin-bottom: 24px;">
            <div style="flex: 1;">
                <input type="text" name="search" placeholder="Search by name, email..." value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-outline">Search</button>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }} {{ $user->surname }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge {{ $user->role === 'admin' ? 'badge-danger' : ($user->role === 'instructor' ? 'badge-warning' : 'badge-info') }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td>{{ $user->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 24px;">
            {{ $users->links() }}
        </div>
    </div>
@endsection

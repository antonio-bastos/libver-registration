@extends('layouts.admin')

@section('title', 'Blacklist Management')

@section('content')
    <style>
        .blacklist-toolbar {
            display: flex;
            gap: 12px;
            align-items: end;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .blacklist-stat {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #fecaca;
            background: #fff1f2;
            color: #9f1239;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 14px;
        }

        .inline-form {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        .inline-form input[type="date"] {
            width: auto;
            min-width: 110px;
            padding: 8px 10px;
            font-size: 12px;
        }

        .actions-cell {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }
    </style>

    <div class="top-bar" style="margin-bottom: 14px;">
        <div>
            <h1 style="margin-bottom: 8px;">Blacklist Management (Temporary Restriction)</h1>
            <p style="color: var(--text-muted);">
                Restrict children temporarily to prevent registrations while a penalty is active.
            </p>
        </div>
    </div>

    <div class="blacklist-stat">
        <i class="fas fa-user-slash"></i>
        Currently restricted children: {{ $restrictedCount }}
    </div>

    <div class="card">
        <form method="GET" action="{{ route('admin.blacklist.index') }}" class="blacklist-toolbar">
            <div>
                <label for="search">Search</label>
                <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Child or parent">
            </div>
            <div>
                <label for="scope">Scope</label>
                <select id="scope" name="scope">
                    <option value="" {{ request('scope') === null || request('scope') === '' ? 'selected' : '' }}>All</option>
                    <option value="restricted" {{ request('scope') === 'restricted' ? 'selected' : '' }}>Restricted only</option>
                    <option value="clear" {{ request('scope') === 'clear' ? 'selected' : '' }}>Not restricted</option>
                </select>
            </div>
            <button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i> Filter</button>
        </form>
    </div>

    <div class="card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Child</th>
                        <th>Parent</th>
                        <th>Absence Count</th>
                        <th>Restriction Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($children as $child)
                        @php($isRestricted = $child->isRestricted())
                        <tr>
                            <td>
                                <strong>{{ trim($child->first_name . ' ' . $child->last_name) }}</strong><br>
                                <span style="font-size: 12px; color: var(--text-muted);">ID #{{ $child->id }}</span>
                            </td>
                            <td>
                                {{ trim(($child->parent->name ?? '') . ' ' . ($child->parent->surname ?? '')) }}<br>
                                <span style="font-size: 12px; color: var(--text-muted);">{{ $child->parent->email ?? '—' }}</span>
                            </td>
                            <td>{{ (int) $child->absence_count }}</td>
                            <td>
                                @if($isRestricted)
                                    <span class="badge badge-danger">Restricted until {{ optional($child->restrictions_until)->format('M d, Y') }}</span>
                                @else
                                    <span class="badge badge-success">Clear</span>
                                @endif
                            </td>
                            <td>
                                <div class="actions-cell">
                                    <form method="POST" action="{{ route('admin.blacklist.restrict', $child) }}" class="inline-form">
                                        @csrf
                                        <input type="date" name="restrictions_until" title="Restrict until date">
                                        <button class="btn btn-sm btn-outline" type="submit">Set Date</button>
                                    </form>
                                    @if($isRestricted)
                                        <form method="POST" action="{{ route('admin.children.clear_restriction', $child) }}" onsubmit="return confirm('Clear restriction for this child?');">
                                            @csrf
                                            <button class="btn btn-sm btn-outline" type="submit"><i class="fas fa-unlock"></i> Clear</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted);">No children found for this filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 14px;">
            {{ $children->links() }}
        </div>
    </div>
@endsection

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Public Library of Veria</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        
    <style>
        :root {
            --primary: #308bd6;
            --primary-dark: #1d4ed8;
            --bg: #f8fafc;
            --white: #ffffff;
            --text-dark: #000000;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --danger: #ef4444;
            --success: #22c55e;
            --warning: #f59e0b;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg);
            color: var(--text-dark);
            line-height: 1.5;
        }

        header {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 24px;
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 32px;
        }

        .grid-row {
            display: flex;
            gap: 24px;
            margin-bottom: 24px;
            align-items: stretch;
        }

        .grid-col-1 { flex: 1; }
        .grid-col-2 { flex: 2; }

        @media (max-width: 768px) {
            .grid-row { flex-direction: column; }
            .grid-col-1, .grid-col-2 { flex: none; width: 100%; }
        }

        .card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 6px;
            color: var(--text-dark);
        }

        input, select {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
        }

        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-outline { background: transparent; border: 1.5px solid var(--border); color: var(--text-dark); }
        .btn-outline:hover { background: var(--bg); }
        .btn-danger { color: var(--danger); background: #fef2f2; }
        .btn-danger:hover { background: #fee2e2; }

        .registration-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid var(--border);
        }

        .registration-item:last-child { border-bottom: none; }

        .event-info h4 { font-size: 16px; font-weight: 600; margin-bottom: 4px; }
        .event-info p { font-size: 13px; color: var(--text-muted); }

        .status-badge {
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 999px;
            text-transform: capitalize;
        }

        .status-confirmed { background: #dcfce7; color: #166534; }
        .status-waiting { background: #fef3c7; color: #92400e; }
        .status-pending { background: #dbeafe; color: #1e40af; }

        .child-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }
        .child-header:last-of-type { border-bottom: none; }
        .child-name { font-weight: 600; color: var(--primary); }

        /* Modal styling */
        .modal {
            position: fixed;
            inset: 0;
            background: rgba(17, 24, 39, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 1000;
        }
        .modal.active { display: flex; }
        .modal-card {
            background: white;
            width: 100%;
            max-width: 480px;
            border-radius: 16px;
            padding: 32px;
            position: relative;
        }
        .modal-close {
            position: absolute;
            top: 16px;
            right: 16px;
            cursor: pointer;
            color: var(--text-muted);
        }
        </style>

</head>
<body>
    <header>
        @include('components.navbar')
    </header>

    <div class="container">
        <div class="grid-row">
            <!-- Account Info -->
            <div class="grid-col-1">
                <div class="card">
                    <h3 class="card-title"><i class="fas fa-user-circle"></i> My Account</h3>
                    <form action="#" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" value="{{ auth()->user()->name }} {{ auth()->user()->surname }}" readonly>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" value="{{ auth()->user()->email }}" readonly>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Children Management -->
            <div class="grid-col-1">
                <div class="card">
                    <h3 class="card-title"><i class="fas fa-child"></i> Family Members</h3>
                    <div style="flex: 1;">
                        @forelse($children as $card)
                            <div class="child-header">
                                <span class="child-name">{{ $card['child']->first_name }} {{ $card['child']->last_name }}</span>
                                <form action="{{ route('children.destroy', $card['child']->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this family member?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" style="padding: 4px 8px; font-size: 12px;"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        @empty
                            <p style="color: var(--text-muted); text-align: center; padding: 20px 0;">No family members added yet.</p>
                        @endforelse
                    </div>
                    <button onclick="document.getElementById('child-modal').classList.add('active')" class="btn btn-outline" style="width: 100%; margin-top: 16px;">
                        <i class="fas fa-plus"></i> Add Family Member
                    </button>
                </div>
            </div>
        </div>

        <!-- Registrations -->
        <div class="card">
            <h3 class="card-title"><i class="fas fa-calendar-check"></i> Event Registrations</h3>
            @php $hasRegistrations = false; @endphp

            {{-- SELF REGISTRATIONS --}}
            @if(!$selfCard['registrations']->isEmpty())
                @php $hasRegistrations = true; @endphp
                <div style="margin-top: 12px;">
                    <p style="font-size: 13px; font-weight: 700; color: var(--primary); margin-bottom: 12px;">
                        FOR MYSELF ({{ strtoupper($selfCard['user']->name) }})
                    </p>
                    @foreach($selfCard['registrations'] as $item)
                        <div class="registration-item">
                            <div class="event-info">
                                <h4>{{ $item['activity']?->title ?? 'Activity' }}</h4>
                                <p><i class="far fa-clock"></i> {{ $item['activity']?->start_at?->format('M d, Y @ H:i') ?? 'TBA' }}</p>
                                <p><i class="fas fa-map-marker-alt"></i> {{ $item['activity']?->location ?? 'Main Library' }}</p>
                                <span class="status-badge status-{{ $item['status_state'] }}">
                                    {{ $item['status_label'] }}
                                </span>
                            </div>
                            <div class="actions">
                                <form action="{{ route('registrations.cancel', $item['registration']->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this registration?')">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">Cancel</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- CHILD REGISTRATIONS --}}
            @foreach($children as $card)
                @if($card['registrations'] && !$card['registrations']->isEmpty())
                    @php $hasRegistrations = true; @endphp
                    <div style="margin-top: 24px; padding-top: 12px; border-top: 1px dashed var(--border);">
                        <p style="font-size: 13px; font-weight: 700; color: var(--text-muted); margin-bottom: 12px;">
                            FOR {{ strtoupper($card['child']->first_name) }}
                        </p>
                        @foreach($card['registrations'] as $item)
                            <div class="registration-item">
                                <div class="event-info">
                                    <h4>{{ $item['activity']?->title ?? 'Activity' }}</h4>
                                    <p><i class="far fa-clock"></i> {{ $item['activity']?->start_at?->format('M d, Y @ H:i') ?? 'TBA' }}</p>
                                    <p><i class="fas fa-map-marker-alt"></i> {{ $item['activity']?->location ?? 'Main Library' }}</p>
                                    <span class="status-badge status-{{ $item['status_state'] }}">
                                        {{ $item['status_label'] }}
                                    </span>
                                </div>
                                <div class="actions">
                                    <form action="{{ route('registrations.cancel', $item['registration']->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this registration?')">
                                        @csrf
                                        <button type="submit" class="btn btn-danger">Cancel</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endforeach

            @if(!$hasRegistrations)
                <div style="text-align: center; padding: 40px 0;">
                    <i class="fas fa-calendar-times" style="font-size: 48px; color: var(--border); margin-bottom: 16px; display: block;"></i>
                    <p style="color: var(--text-muted);">No active registrations found.</p>
                    <a href="{{ url('/') }}" class="btn btn-primary" style="margin-top: 16px;">Browse Events</a>
                </div>
            @endif
        </div>
    </div>

    <!-- Add Child Modal -->
    <div class="modal" id="child-modal">
        <div class="modal-card">
            <i class="fas fa-times modal-close" onclick="document.getElementById('child-modal').classList.remove('active')"></i>
            <h3 style="margin-bottom: 24px;">Add Family Member</h3>
            <form action="{{ route('children.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" required>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" value="{{ auth()->user()->surname }}" required>
                </div>
                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="date" name="dob" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 16px;">Add Member</button>
            </form>
        </div>
    </div>
</body>
</html>

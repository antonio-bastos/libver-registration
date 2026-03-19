@extends('layouts.admin')

@section('title', 'Activity Details')

@section('content')
    <div class="top-bar">
        <h1>{{ $activity->title }} - Management</h1>
        <div style="display: flex; gap: 12px;">
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
                        <th>Participant</th>
                        <th>Parent/Account</th>
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
                                <span style="color: var(--success); font-weight: 600; display: flex; align-items: center; gap: 4px;">
                                    <i class="fas fa-check-circle"></i> Yes
                                </span>
                                @if($reg->attended_at)
                                    <span style="font-size: 10px; color: var(--text-muted);">{{ $reg->attended_at->format('H:i') }}</span>
                                @endif
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

                                @if($reg->status === 'confirmed')
                                    @if(!$reg->attended)
                                        <form action="{{ route('admin.registrations.mark_attended', ['qr_data' => json_encode(['p' => json_encode(['id' => $reg->id]), 's' => hash_hmac('sha256', json_encode(['id' => $reg->id]), config('app.key'))])]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Mark Present</button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.registrations.absent', $reg) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline" style="color: var(--danger);">Mark Absent</button>
                                        </form>
                                    @endif
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

        <div style="display: flex; gap: 12px;">
            <button onclick="openScanner()" class="btn btn-primary">
                <i class="fas fa-camera"></i> Scan QR Attendance
            </button>
            <a href="{{ route('admin.activities.export', $activity) }}" class="btn btn-outline">
                <i class="fas fa-file-export"></i> Export CSV
            </a>
        </div>

    <!-- Scanner Modal -->
    <div id="scanner-modal" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
        <div style="background: white; padding: 24px; border-radius: 16px; width: 100%; max-width: 500px; text-align: center; position: relative;">
            <button onclick="closeScanner()" style="position: absolute; top: 16px; right: 16px; border: none; background: #f1f5f9; width: 32px; height: 32px; border-radius: 50%; cursor: pointer;">&times;</button>
            <h3 style="margin-bottom: 20px;">Scan Attendee QR Code</h3>
            
            <div id="reader" style="width: 100%; overflow: hidden; border-radius: 12px; background: #f8fafc;"></div>
            
            <div id="scanner-status" style="margin-top: 16px; font-weight: 500; color: var(--text-muted);">
                Waiting for camera...
            </div>
            
            <button onclick="closeScanner()" class="btn btn-outline" style="margin-top: 24px; width: 100%;">Cancel</button>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let html5QrCode = null;

        function openScanner() {
            document.getElementById('scanner-modal').style.display = 'flex';
            document.getElementById('scanner-status').textContent = 'Initialing camera...';
            
            html5QrCode = new Html5Qrcode("reader");
            const config = { fps: 10, qrbox: { width: 250, height: 250 } };

            html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess)
                .then(() => {
                    document.getElementById('scanner-status').textContent = 'Scanning... Align QR code in the box';
                })
                .catch(err => {
                    document.getElementById('scanner-status').textContent = 'Error: ' + err;
                    document.getElementById('scanner-status').style.color = 'var(--danger)';
                });
        }

        function onScanSuccess(decodedText, decodedResult) {
            // Stop scanning once success
            html5QrCode.stop().then(() => {
                document.getElementById('scanner-status').textContent = 'Verifying...';
                
                // Send to server
                fetch("{{ route('admin.registrations.mark_attended') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ qr_data: decodedText })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                        // Restart scanner after short delay if failed
                        setTimeout(openScanner, 2000);
                    }
                })
                .catch(error => {
                    alert('Request failed. Please try again.');
                    setTimeout(openScanner, 2000);
                });
            });
        }

        function closeScanner() {
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().then(() => {
                    document.getElementById('scanner-modal').style.display = 'none';
                });
            } else {
                document.getElementById('scanner-modal').style.display = 'none';
            }
        }
    </script>
@endsection

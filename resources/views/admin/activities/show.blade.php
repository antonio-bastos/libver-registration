@extends('layouts.admin')

@section('title', 'Activity Details')

@section('content')
    <style>
        #scanner-modal {
            align-items: flex-start !important;
            justify-content: center;
            padding: 20px 12px;
            overflow-y: auto;
        }

        #scanner-modal .scanner-modal-card {
            background: white;
            padding: 20px;
            border-radius: 16px;
            width: 100%;
            max-width: 520px;
            text-align: center;
            position: relative;
            margin: auto 0;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }

        #reader {
            width: min(100%, 340px);
            height: min(55vh, 440px);
            min-height: 280px;
            margin: 0 auto;
            overflow: hidden;
            border-radius: 12px;
            background: #0f172a;
        }

        @media (max-height: 760px) {
            #reader {
                width: min(100%, 300px);
                height: min(48vh, 360px);
                min-height: 240px;
            }
        }

        #reader video {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 12px;
            background: #000;
        }
    </style>

    <div class="top-bar">
        <h1>{{ $activity->title }} - Management</h1>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('admin.activities.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to list
            </a>
        </div>
    </div>

    <div class="card">
        <div style="display: flex; gap: 16px; align-items: flex-start;">
            @if(!empty($activityImageUrl))
                <img src="{{ $activityImageUrl }}" alt="Activity image" style="width: 88px; height: 88px; border-radius: 12px; object-fit: cover;">
            @else
                <span style="width: 88px; height: 88px; border-radius: 12px; background: #e2e8f0; display: inline-flex; align-items: center; justify-content: center; color: #64748b;">
                    <i class="fas fa-image" style="font-size: 24px;"></i>
                </span>
            @endif
            <div>
                <div style="font-weight: 700; font-size: 18px;">{{ $activity->title }}</div>
                <div style="font-size: 13px; color: var(--text-muted); margin-top: 5px;">
                    {{ optional($activity->start_at)->format('M d, Y H:i') }} - {{ optional($activity->end_at)->format('H:i') }}
                    · {{ $activity->location }}
                </div>
                <div style="display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap;">
                    @if($activity->first_timers_only)<span class="badge badge-info">First Timers Only</span>@endif
                    @if($activity->requires_selection)<span class="badge badge-warning">Selection Later</span>@endif
                    @if($activity->is_space_booking)<span class="badge badge-success">Space Booking</span>@endif
                </div>
            </div>
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
                            <div style="display: flex; align-items: center; gap: 10px;">
                                @php
                                    $participantImage = $reg->child ? ($childImageMap[$reg->child->id] ?? null) : ($userImageMap[$reg->user->id] ?? null);
                                @endphp
                                @if($participantImage)
                                    <img src="{{ $participantImage }}" alt="Participant image" style="width: 40px; height: 40px; border-radius: 999px; object-fit: cover;">
                                @else
                                    <span style="width: 40px; height: 40px; border-radius: 999px; background: #e2e8f0; display: inline-flex; align-items: center; justify-content: center; color: #64748b;">
                                        <i class="fas fa-user"></i>
                                    </span>
                                @endif
                                <div style="font-weight: 600;">
                                    @if($reg->child)
                                        {{ $reg->child->first_name }} {{ $reg->child->last_name }}
                                    @else
                                        {{ $reg->user->name }} {{ $reg->user->surname }} (Self)
                                    @endif
                                </div>
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
                            <div>{{ number_format($reg->amount_paid, 2) }} / {{ number_format($reg->total_due, 2) }}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">Outstanding: {{ number_format($reg->outstanding_amount, 2) }}</div>
                            @if($reg->absence_fine > 0)
                                <div style="font-size: 11px; color: var(--danger);">Absence fine: {{ number_format($reg->absence_fine, 2) }}</div>
                            @endif
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

    <div class="card">
        <h3 style="margin-bottom: 16px;">Postpone Event & Notify Participants</h3>
        <form action="{{ route('admin.activities.postpone', $activity) }}" method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            @csrf
            <div>
                <label for="start_at">New Start</label>
                <input id="start_at" type="datetime-local" name="start_at" value="{{ old('start_at', optional($activity->start_at)->format('Y-m-d\TH:i')) }}" required>
            </div>
            <div>
                <label for="end_at">New End</label>
                <input id="end_at" type="datetime-local" name="end_at" value="{{ old('end_at', optional($activity->end_at)->format('Y-m-d\TH:i')) }}" required>
            </div>
            <div style="grid-column: 1 / -1;">
                <label for="custom_message_postpone">Custom Email Message (optional)</label>
                <textarea id="custom_message_postpone" name="custom_message_postpone" rows="3" placeholder="Optional explanation that will be sent to all participants.">{{ old('custom_message_postpone') }}</textarea>
            </div>
            <div style="grid-column: 1 / -1; display: flex; justify-content: flex-end;">
                <button class="btn btn-primary" type="submit"><i class="fas fa-envelope"></i> Postpone & Send Email Notifications</button>
            </div>
        </form>
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
    <div id="scanner-modal" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 9999; backdrop-filter: blur(5px);">
        <div class="scanner-modal-card">
            <button onclick="closeScanner()" style="position: absolute; top: 16px; right: 16px; border: none; background: #f1f5f9; width: 32px; height: 32px; border-radius: 50%; cursor: pointer;">&times;</button>
            <h3 style="margin-bottom: 20px;">Scan Attendee QR Code</h3>
            
            <div id="reader"></div>
            
            <div id="scanner-status" style="margin-top: 16px; font-weight: 500; color: var(--text-muted);">
                Waiting for camera...
            </div>
            
            <button onclick="closeScanner()" class="btn btn-outline" style="margin-top: 24px; width: 100%;">Cancel</button>
        </div>
    </div>

    <script src="https://unpkg.com/jsqr@1.4.0/dist/jsQR.js" crossorigin="anonymous"></script>
    <script>
        let scannerOpen = false;
        let processingScan = false;
        let cameraStream = null;
        let scanIntervalId = null;
        let scanInProgress = false;
        let barcodeDetector = null;
        let scannerVideo = null;
        let scanCanvas = null;
        let scanContext = null;

        function setScannerStatus(message, isError = false) {
            const status = document.getElementById('scanner-status');
            status.textContent = message;
            status.style.color = isError ? 'var(--danger)' : 'var(--text-muted)';
        }

        function ensureScannerVideo() {
            if (scannerVideo) {
                return scannerVideo;
            }

            const reader = document.getElementById('reader');
            scannerVideo = document.createElement('video');
            scannerVideo.setAttribute('playsinline', 'true');
            scannerVideo.setAttribute('autoplay', 'true');
            scannerVideo.setAttribute('muted', 'true');
            scannerVideo.muted = true;

            reader.innerHTML = '';
            reader.appendChild(scannerVideo);

            return scannerVideo;
        }

        async function ensureDetector() {
            if (barcodeDetector !== null) {
                return barcodeDetector;
            }

            if (!('BarcodeDetector' in window)) {
                barcodeDetector = false;
                return null;
            }

            try {
                const supportedFormats = await BarcodeDetector.getSupportedFormats();
                if (!supportedFormats.includes('qr_code')) {
                    barcodeDetector = false;
                    return null;
                }

                barcodeDetector = new BarcodeDetector({ formats: ['qr_code'] });
                return barcodeDetector;
            } catch (_) {
                barcodeDetector = false;
                return null;
            }
        }

        async function startCamera() {
            const video = ensureScannerVideo();

            const candidates = [
                { video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } }, audio: false },
                { video: { facingMode: 'environment' }, audio: false },
                { video: true, audio: false },
            ];

            let stream = null;
            for (const constraints of candidates) {
                try {
                    stream = await navigator.mediaDevices.getUserMedia(constraints);
                    break;
                } catch (_) {}
            }

            if (!stream) {
                throw new Error('Camera access failed.');
            }

            cameraStream = stream;
            video.srcObject = stream;
            await video.play();
            setScannerStatus('Scanning... align the QR code in the box.');
        }

        function stopScanLoop() {
            if (scanIntervalId) {
                clearInterval(scanIntervalId);
                scanIntervalId = null;
            }
        }

        async function stopCamera() {
            stopScanLoop();
            scanInProgress = false;

            if (scannerVideo) {
                try {
                    scannerVideo.pause();
                } catch (_) {}
                scannerVideo.srcObject = null;
            }

            if (cameraStream) {
                for (const track of cameraStream.getTracks()) {
                    track.stop();
                }
                cameraStream = null;
            }
        }

        async function scanOnce() {
            if (!scannerOpen || processingScan || scanInProgress || !scannerVideo) {
                return;
            }

            if (scannerVideo.readyState < 2) {
                return;
            }

            scanInProgress = true;
            try {
                const width = scannerVideo.videoWidth;
                const height = scannerVideo.videoHeight;
                if (!width || !height) {
                    return;
                }

                const scale = Math.min(1, 960 / Math.max(width, height));
                const frameWidth = Math.max(1, Math.floor(width * scale));
                const frameHeight = Math.max(1, Math.floor(height * scale));

                if (!scanCanvas) {
                    scanCanvas = document.createElement('canvas');
                    scanContext = scanCanvas.getContext('2d', { willReadFrequently: true });
                }

                if (!scanContext) {
                    throw new Error('Unable to initialize scanner canvas.');
                }

                if (scanCanvas.width !== frameWidth || scanCanvas.height !== frameHeight) {
                    scanCanvas.width = frameWidth;
                    scanCanvas.height = frameHeight;
                }

                scanContext.drawImage(scannerVideo, 0, 0, frameWidth, frameHeight);

                let decodedText = '';
                const detector = await ensureDetector();
                if (detector) {
                    const codes = await detector.detect(scanCanvas);
                    if (codes.length && codes[0].rawValue) {
                        decodedText = codes[0].rawValue;
                    }
                }

                if (!decodedText && typeof jsQR === 'function') {
                    const imageData = scanContext.getImageData(0, 0, frameWidth, frameHeight);
                    const qrResult = jsQR(imageData.data, frameWidth, frameHeight, { inversionAttempts: 'attemptBoth' });
                    if (qrResult && qrResult.data) {
                        decodedText = qrResult.data;
                    }
                }

                if (!detector && typeof jsQR !== 'function') {
                    setScannerStatus('Scanner engine unavailable. Please refresh.', true);
                }

                if (decodedText) {
                    await onScanSuccess(decodedText);
                }
            } finally {
                scanInProgress = false;
            }
        }

        function startScanLoop() {
            stopScanLoop();
            scanIntervalId = setInterval(() => {
                scanOnce().catch(() => {});
            }, 180);
        }

        async function openScanner() {
            if (scannerOpen) {
                return;
            }

            scannerOpen = true;
            processingScan = false;
            document.getElementById('scanner-modal').style.display = 'flex';
            setScannerStatus('Initializing camera...');

            try {
                await startCamera();
                startScanLoop();
            } catch (error) {
                scannerOpen = false;
                const message = error && error.message ? error.message : String(error);
                setScannerStatus('Unable to start camera: ' + message, true);
            }
        }

        async function onScanSuccess(decodedText) {
            if (processingScan) {
                return;
            }

            processingScan = true;
            setScannerStatus('Verifying...');

            await stopCamera();

            try {
                const response = await fetch("{{ route('admin.registrations.mark_attended') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ qr_data: decodedText })
                });

                const data = await response.json().catch(() => ({
                    success: false,
                    message: 'Unexpected server response while verifying QR code.'
                }));

                if (response.ok && data.success) {
                    alert(data.message);
                    window.location.reload();
                    return;
                }

                alert('Error: ' + (data.message || 'Unable to verify QR code.'));
                processingScan = false;
                if (scannerOpen) {
                    try {
                        await startCamera();
                        startScanLoop();
                    } catch (error) {
                        const message = error && error.message ? error.message : String(error);
                        setScannerStatus('Unable to restart camera: ' + message, true);
                    }
                }
            } catch (_) {
                alert('Request failed. Please try again.');
                processingScan = false;
                if (scannerOpen) {
                    try {
                        await startCamera();
                        startScanLoop();
                    } catch (error) {
                        const message = error && error.message ? error.message : String(error);
                        setScannerStatus('Unable to restart camera: ' + message, true);
                    }
                }
            }
        }

        async function closeScanner() {
            scannerOpen = false;
            processingScan = false;

            await stopCamera();

            document.getElementById('scanner-modal').style.display = 'none';
            setScannerStatus('Waiting for camera...');
        }
    </script>
@endsection

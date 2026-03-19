@extends('layouts.admin')

@section('title', 'QR Check-in')

@section('content')
    <style>
        .scanner-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }

        .scanner-panel {
            min-height: 460px;
        }

        #scanner-root {
            width: 100%;
            min-height: 320px;
            border-radius: 12px;
            border: 1px dashed var(--border);
            background: #020617;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .scan-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 16px;
            gap: 12px;
        }

        .camera-select {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1.5px solid var(--border);
            font-size: 14px;
        }

        .result-card {
            border: 1.5px solid var(--border);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 16px;
            transition: border-color 0.2s, background 0.2s;
        }

        .result-card--idle { background: #f8fafc; border-color: var(--border); }
        .result-card--pending { background: #eef2ff; border-color: #c7d2fe; }
        .result-card--success { background: #ecfdf5; border-color: #bbf7d0; }
        .result-card--error { background: #fef2f2; border-color: #fecaca; }

        .result-card h3 { font-size: 18px; margin-bottom: 6px; }
        .result-card p { font-size: 14px; color: var(--text-muted); }

        .manual-entry form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .manual-entry input {
            flex: 1 1 220px;
        }

        .tips-list {
            margin-top: 12px;
            padding-left: 20px;
            color: var(--text-muted);
            font-size: 14px;
        }

        .tips-list li { margin-bottom: 6px; }

        .scan-status-text {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 8px;
        }
    </style>

    <div class="top-bar">
        <div>
            <h1>QR Check-in Scanner</h1>
            <p style="color: var(--text-muted);">Use your device camera to confirm arrivals instantly.</p>
        </div>
        <a href="{{ route('admin.activities.index') }}" class="btn btn-outline">
            <i class="fas fa-calendar"></i> View Activities
        </a>
    </div>

    <div class="scanner-grid">
        <div class="card scanner-panel">
            <h2 style="margin-bottom: 12px;">Live Scanner</h2>
            <div id="scanner-root">
                <div style="text-align: center; color: #94a3b8;">
                    <i class="fas fa-mobile-alt" style="font-size: 42px; margin-bottom: 8px;"></i>
                    <p>Camera preview will appear here.</p>
                </div>
            </div>
            <div class="scan-meta">
                <select id="camera-select" class="camera-select"></select>
                <button class="btn btn-outline btn-sm" type="button" id="refresh-camera">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
            <p class="scan-status-text" id="scan-status">Initializing camera…</p>
        </div>

        <div class="card">
            <h2 style="margin-bottom: 12px;">Latest Result</h2>
            <div class="result-card result-card--idle" id="result-card">
                <h3 id="result-title">No scans yet</h3>
                <p id="result-body">Point the camera at any attendee QR code to get started.</p>
            </div>

            <div class="manual-entry">
                <h3 style="margin-bottom: 8px;">Manual Token Entry</h3>
                <form id="manual-form">
                    <input type="text" id="manual-token" placeholder="Paste a token or QR value" autocomplete="off" required>
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-check"></i> Check In
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Scan Tips</h2>
        <ul class="tips-list">
            <li>Works best on https or a trusted localhost connection so the camera can activate.</li>
            <li>Ask families to brighten their screens so the QR pattern stays crisp.</li>
            <li>A manual entry backup is available if a screen is cracked or unreadable.</li>
            <li>Each code is single-use; we will regenerate a fresh token if attendance is reset.</li>
        </ul>
    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/minified/html5-qrcode.min.js" crossorigin="anonymous"></script>
    <script>
        (function() {
            const scanEndpoint = '{{ route('admin.checkin.scan-json') }}';
            const csrfToken = '{{ csrf_token() }}';
            const scannerRootId = 'scanner-root';
            const cameraSelect = document.getElementById('camera-select');
            const refreshButton = document.getElementById('refresh-camera');
            const scanStatus = document.getElementById('scan-status');
            const resultCard = document.getElementById('result-card');
            const resultTitle = document.getElementById('result-title');
            const resultBody = document.getElementById('result-body');
            const manualForm = document.getElementById('manual-form');
            const manualToken = document.getElementById('manual-token');

            let qrCodeReader = null;
            let currentCameraId = null;
            let isProcessing = false;

            const setResult = (state, title, message) => {
                resultCard.className = 'result-card result-card--' + state;
                resultTitle.textContent = title;
                resultBody.textContent = message;
            };

            const setStatus = (message) => {
                scanStatus.textContent = message;
            };

            const normalizeToken = (raw) => {
                if (!raw) {
                    return '';
                }

                let value = raw.trim();
                try {
                    const parsed = new URL(value);
                    const parts = parsed.pathname.split('/').filter(Boolean);
                    return parts.pop() || value;
                } catch (_) {
                    // Not a URL, fall through
                }

                const hashIndex = value.indexOf('#');
                if (hashIndex !== -1) {
                    value = value.substring(0, hashIndex);
                }

                const segments = value.split('/').filter(Boolean);
                return segments.pop() || value;
            };

            const submitToken = async (token) => {
                if (!token || isProcessing) {
                    return;
                }

                isProcessing = true;
                setStatus('Submitting token…');
                setResult('pending', 'Checking token…', 'Hold tight while we confirm attendance.');

                try {
                    const response = await fetch(scanEndpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ token })
                    });

                    const payload = await response.json();
                    if (!response.ok) {
                        throw new Error(payload.message || 'Invalid or expired token.');
                    }

                    setResult('success', payload.participant, payload.activity + ' • ' + payload.message);
                } catch (error) {
                    setResult('error', 'Unable to check in', error.message || 'Please try another code.');
                } finally {
                    setTimeout(() => { isProcessing = false; }, 1500);
                    setStatus('Ready to scan again.');
                }
            };

            const handleScan = (decodedText) => {
                const token = normalizeToken(decodedText);
                if (!token) {
                    return;
                }
                submitToken(token);
            };

            const startScanner = async (cameraId) => {
                if (!cameraId || typeof Html5Qrcode === 'undefined') {
                    setStatus('Camera not available in this browser.');
                    return;
                }

                setStatus('Opening camera feed…');

                if (!qrCodeReader) {
                    qrCodeReader = new Html5Qrcode(scannerRootId);
                } else {
                    await qrCodeReader.stop().catch(() => {});
                    await qrCodeReader.clear().catch(() => {});
                }

                const config = {
                    fps: 10,
                    qrbox: 280,
                    aspectRatio: 1.0
                };

                qrCodeReader.start({ deviceId: { exact: cameraId } }, config, handleScan, () => {})
                    .then(() => {
                        currentCameraId = cameraId;
                        cameraSelect.value = cameraId;
                        setStatus('Camera ready — aim at a QR code.');
                    })
                    .catch((error) => {
                        setStatus('Unable to start camera: ' + error.message);
                    });
            };

            const hydrateCameraOptions = async () => {
                if (typeof Html5Qrcode === 'undefined') {
                    setStatus('QR library failed to load. Please refresh.');
                    return;
                }

                setStatus('Looking for available cameras…');

                let devices = [];
                try {
                    devices = await Html5Qrcode.getCameras();
                } catch (error) {
                    setStatus('Unable to access cameras: ' + error.message);
                    cameraSelect.innerHTML = '<option>Camera access blocked</option>';
                    return;
                }

                if (!devices.length) {
                    setStatus('No cameras detected. Try a different device.');
                    cameraSelect.innerHTML = '<option>No cameras found</option>';
                    return;
                }

                cameraSelect.innerHTML = devices.map((device, index) => {
                    const label = device.label && device.label.length ? device.label : `Camera ${index + 1}`;
                    return `<option value="${device.id}">${label}</option>`;
                }).join('');

                const backCamera = devices.find(device => (device.label || '').toLowerCase().includes('back'));
                const preferredId = backCamera ? backCamera.id : devices[0].id;
                await startScanner(preferredId);
            };

            cameraSelect.addEventListener('change', (event) => {
                const newCameraId = event.target.value;
                if (newCameraId && newCameraId !== currentCameraId) {
                    startScanner(newCameraId);
                }
            });

            refreshButton.addEventListener('click', () => hydrateCameraOptions());

            manualForm.addEventListener('submit', (event) => {
                event.preventDefault();
                const token = normalizeToken(manualToken.value);
                if (!token) {
                    setResult('error', 'Missing token', 'Paste a valid token or scan a QR code.');
                    return;
                }
                submitToken(token);
                manualToken.value = '';
            });

            hydrateCameraOptions();
        })();
    </script>
@endsection

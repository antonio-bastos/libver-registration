@extends('layouts.admin')

@section('title', 'Insights Dashboard')

@section('content')
    <style>
        .insights-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .insight-stat {
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            background: #fff;
        }

        .insight-stat .value {
            font-size: 26px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 4px;
        }

        .insight-stat .label {
            font-size: 13px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-weight: 600;
        }

        .insight-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .bar-list {
            display: grid;
            gap: 10px;
        }

        .bar-row {
            display: grid;
            grid-template-columns: 84px 1fr 48px;
            gap: 10px;
            align-items: center;
            font-size: 13px;
        }

        .bar-track {
            width: 100%;
            height: 12px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            background: #308bd6;
            border-radius: 999px;
        }

        @media (max-width: 900px) {
            .insight-sections {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="top-bar" style="margin-bottom: 16px;">
        <div>
            <h1 style="margin-bottom: 8px;">Analytics Dashboard (Insights)</h1>
            <p style="color: var(--text-muted);">Peak days/times, age-group demand, and cancellation rates.</p>
        </div>
        <form method="GET" action="{{ route('admin.analytics') }}" style="display: flex; gap: 10px; align-items: end; flex-wrap: wrap;">
            <div>
                <label for="from">From</label>
                <input id="from" type="date" name="from" value="{{ $from }}">
            </div>
            <div>
                <label for="to">To</label>
                <input id="to" type="date" name="to" value="{{ $to }}">
            </div>
            <button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i> Apply</button>
        </form>
    </div>

    <div class="insights-grid">
        <div class="insight-stat">
            <div class="value">{{ $totalRegistrations }}</div>
            <div class="label">Registrations</div>
        </div>
        <div class="insight-stat">
            <div class="value">{{ $totalCanceled }}</div>
            <div class="label">Canceled</div>
        </div>
        <div class="insight-stat">
            <div class="value">{{ number_format($overallCancellationRate, 1) }}%</div>
            <div class="label">Overall Cancellation Rate</div>
        </div>
    </div>

    <div class="insight-sections">
        <div class="card">
            <h3 style="margin-bottom: 14px;">Peak Days</h3>
            @php($maxPeakDay = max(1, (int) collect($peakDays)->max()))
            <div class="bar-list">
                @foreach($peakDays as $day => $count)
                    @php($width = $count > 0 ? round(($count / $maxPeakDay) * 100, 1) : 0)
                    <div class="bar-row">
                        <strong>{{ $day }}</strong>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: {{ $width }}%;"></div>
                        </div>
                        <span>{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 14px;">Peak Times (Top 8 Hours)</h3>
            @php($maxPeakHour = max(1, (int) collect($topHours)->max('count')))
            <div class="bar-list">
                @foreach($topHours as $row)
                    @php($width = $row['count'] > 0 ? round(($row['count'] / $maxPeakHour) * 100, 1) : 0)
                    <div class="bar-row">
                        <strong>{{ $row['label'] }}</strong>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: {{ $width }}%;"></div>
                        </div>
                        <span>{{ $row['count'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="insight-sections" style="margin-top: 18px;">
        <div class="card">
            <h3 style="margin-bottom: 14px;">Age Group Demand</h3>
            @php($maxAgeDemand = max(1, (int) collect($ageDemand)->max()))
            <div class="bar-list">
                @forelse($ageDemand as $age => $count)
                    @php($width = $count > 0 ? round(($count / $maxAgeDemand) * 100, 1) : 0)
                    <div class="bar-row" style="grid-template-columns: 180px 1fr 60px;">
                        <strong style="font-size: 12px;">{{ $age }}</strong>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: {{ $width }}%;"></div>
                        </div>
                        <span>{{ $count }}</span>
                    </div>
                @empty
                    <p style="color: var(--text-muted);">No demand data for this period.</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 14px;">Cancellation Rates (Monthly)</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Total</th>
                            <th>Canceled</th>
                            <th>Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthlyRates as $month => $row)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $month)->format('M Y') }}</td>
                                <td>{{ $row['total'] }}</td>
                                <td>{{ $row['canceled'] }}</td>
                                <td>
                                    <span class="badge {{ $row['rate'] >= 30 ? 'badge-danger' : ($row['rate'] >= 15 ? 'badge-warning' : 'badge-success') }}">
                                        {{ number_format($row['rate'], 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection


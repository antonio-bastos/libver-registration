<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $registration->id }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 14px; line-height: 1.4; color: #333; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; }
        .header { background: #f8fafc; padding: 20px; margin-bottom: 30px; border-radius: 8px; }
        .header h2 { margin: 0; color: #308bd6; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { text-align: left; padding: 10px; background: #f1f5f9; border-bottom: 2px solid #e2e8f0; }
        td { padding: 10px; border-bottom: 1px solid #e2e8f0; }
        .total-row { font-weight: bold; font-size: 16px; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; }
        .paid { background: #dcfce7; color: #166534; }
        .unpaid { background: #fee2e2; color: #991b1b; }
        .footer { text-align: center; margin-top: 40px; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <h2>Activity Receipt</h2>
            <p><strong>Registration ID:</strong> #{{ $registration->id }} | <strong>Date:</strong> {{ now()->format('d/m/Y') }}</p>
        </div>

        <table>
            <tr>
                <td style="width: 50%; border: none;">
                    <strong>Customer:</strong><br>
                    @if($registration->child)
                        {{ $registration->child->parent->name }} {{ $registration->child->parent->surname }}<br>
                        {{ $registration->child->parent->email }}
                    @else
                        {{ $registration->user->name }} {{ $registration->user->surname }}<br>
                        {{ $registration->user->email }}
                    @endif
                </td>
                <td style="width: 50%; border: none; text-align: right;">
                    <strong>Veria Central Public Library</strong><br>
                    Ellis 8, Veria 591 32<br>
                    Greece
                </td>
            </tr>
        </table>

        <h3>Registration Details</h3>
        <table>
            <thead>
                <tr>
                    <th>Item / Participant</th>
                    <th>Date</th>
                    <th style="text-align: right;">Price</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ $registration->activity->title }}</strong><br>
                        Participant: {{ $registration->child ? ($registration->child->first_name . ' ' . $registration->child->last_name) : ($registration->user->name . ' ' . $registration->user->surname) }}
                    </td>
                    <td>{{ $registration->activity->start_at->format('d/m/Y') }}</td>
                    <td style="text-align: right;">{{ number_format($registration->fee_amount, 2) }} €</td>
                </tr>
                <tr class="total-row">
                    <td colspan="2" style="text-align: right;">Total Paid</td>
                    <td style="text-align: right;">{{ number_format($registration->amount_paid, 2) }} €</td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 20px;">
            <strong>Payment Status:</strong> 
            <span class="status-badge {{ $registration->payment_status === 'paid' ? 'paid' : 'unpaid' }}">
                {{ strtoupper($registration->payment_status) }}
            </span>
        </div>

        <div class="footer">
            Thank you for participating in our library's programs!<br>
            2026 © Veria Central Public Library
        </div>
    </div>
</body>
</html>

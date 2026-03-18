<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $registration->id }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, .15); font-size: 16px; line-height: 24px; color: #555; }
        .invoice-box table { width: 100%; line-height: inherit; text-align: left; }
        .header { background: #f5f5f5; padding: 10px; margin-bottom: 20px; }
        .total { font-weight: bold; font-size: 1.2em; text-align: right; }
        .paid { color: green; }
        .unpaid { color: red; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <h2>Activity Registration Invoice</h2>
            <p><strong>Registration ID:</strong> #{{ $registration->id }}</p>
            <p><strong>Date:</strong> {{ $registration->created_at->format('d/m/Y') }}</p>
        </div>

        <table>
            <tr>
                <td>
                    <strong>Parent:</strong><br>
                    {{ $parent->name }}<br>
                    {{ $parent->email }}
                </td>
                <td>
                    <strong>Organization:</strong><br>
                    Bibliothèque Publique<br>
                    123 Library Lane
                </td>
            </tr>
        </table>

        <br>

        <h3>Activity Details</h3>
        <table>
            <tr class="heading">
                <td>Item</td>
                <td>Price</td>
            </tr>
            <tr class="item">
                <td>{{ $activity->title }} ({{ $activity->start_at ? $activity->start_at->format('d/m/Y') : 'TBA' }})</td>
                <td>{{ number_format($registration->fee_amount, 2) }} {{ $registration->currency }}</td>
            </tr>
        </table>

        <br>

        <div class="total">
            Total Due: {{ number_format($registration->fee_amount, 2) }} {{ $registration->currency }}
        </div>

        <br>

        <h3>Payment Status</h3>
        <p>
            Amount Paid: {{ number_format($registration->amount_paid, 2) }} {{ $registration->currency }}<br>
            Status: <span class="{{ $registration->payment_status === 'paid' ? 'paid' : 'unpaid' }}">{{ ucfirst($registration->payment_status) }}</span>
        </p>

        @if($registration->payment_status !== 'paid')
            <div style="margin-top: 20px; text-align: center;">
                <p>Please pay at the library desk or via bank transfer.</p>
            </div>
        @endif
    </div>
</body>
</html>

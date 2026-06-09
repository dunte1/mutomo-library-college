<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Receipt {{ $receipt->receipt_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #1E4FA3; padding-bottom: 15px; }
        .header h1 { color: #1E4FA3; margin: 0; font-size: 24px; }
        .header p { margin: 5px 0; color: #666; }
        .details { margin-bottom: 30px; }
        .details table { width: 100%; }
        .details td { padding: 3px 0; }
        .details .label { font-weight: bold; width: 150px; color: #555; }
        .receipt-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .receipt-table th { background-color: #1E4FA3; color: white; padding: 8px; text-align: left; }
        .receipt-table td { padding: 8px; border-bottom: 1px solid #ddd; }
        .total-row { font-weight: bold; font-size: 14px; }
        .total-row td { border-top: 2px solid #333; padding-top: 10px; }
        .paid-stamp { text-align: center; margin: 20px 0; }
        .paid-stamp span { display: inline-block; padding: 8px 30px; border: 3px solid #155724; color: #155724; font-weight: bold; font-size: 18px; border-radius: 5px; transform: rotate(-5deg); }
        .footer { text-align: center; color: #999; font-size: 10px; margin-top: 40px; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $display['site_name'] ?? config('app.name') }}</h1>
        <p>{{ $display['library_address'] ?? '' }}</p>
        <p>{{ $display['library_phone'] ?? '' }} | {{ $display['library_email'] ?? '' }}</p>
        <h2 style="margin-top: 15px;">OFFICIAL RECEIPT</h2>
    </div>

    <div class="paid-stamp">
        <span>PAID</span>
    </div>

    <div class="details">
        <table>
            <tr><td class="label">Receipt Number:</td><td>{{ $receipt->receipt_number }}</td></tr>
            <tr><td class="label">Date Issued:</td><td>{{ $receipt->issued_at?->format('d M Y H:i') ?? $receipt->created_at->format('d M Y H:i') }}</td></tr>
            <tr><td class="label">Payment Method:</td><td>{{ ucfirst($receipt->payment_method) }}</td></tr>
            <tr><td class="label">Received From:</td><td>{{ $receipt->user?->name ?? 'N/A' }}</td></tr>
            @if($transaction)
                <tr><td class="label">Transaction Ref:</td><td>{{ $transaction->reference ?? $transaction->transaction_number }}</td></tr>
            @endif
        </table>
    </div>

    <table class="receipt-table">
        <thead>
            <tr><th>Description</th><th style="text-align: right;">Amount ({{ $receipt->currency }})</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $transaction?->description ?? 'Payment receipt' }}</td>
                <td style="text-align: right;">{{ number_format($receipt->amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td style="text-align: right;">Total Paid:</td>
                <td style="text-align: right;">{{ number_format($receipt->amount, 2) }} {{ $receipt->currency }}</td>
            </tr>
        </tbody>
    </table>

    <p style="text-align: center; color: #666; font-style: italic;">Thank you for your payment.</p>

    <div class="footer">
        <p>{{ $branding['document_footer_text'] ?? 'Official Library Document' }}</p>
        <p>{{ $branding['document_footer_disclaimer'] ?? '' }}</p>
    </div>
</body>
</html>

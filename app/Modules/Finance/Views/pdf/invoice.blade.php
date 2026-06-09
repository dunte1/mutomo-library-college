<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #1E4FA3; padding-bottom: 15px; }
        .header h1 { color: #1E4FA3; margin: 0; font-size: 24px; }
        .header p { margin: 5px 0; color: #666; }
        .details { margin-bottom: 30px; }
        .details table { width: 100%; }
        .details td { padding: 3px 0; }
        .details .label { font-weight: bold; width: 150px; color: #555; }
        .invoice-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .invoice-table th { background-color: #1E4FA3; color: white; padding: 8px; text-align: left; }
        .invoice-table td { padding: 8px; border-bottom: 1px solid #ddd; }
        .total-row { font-weight: bold; font-size: 14px; }
        .total-row td { border-top: 2px solid #333; padding-top: 10px; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 11px; }
        .status-paid { background-color: #d4edda; color: #155724; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .footer { text-align: center; color: #999; font-size: 10px; margin-top: 40px; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $display['site_name'] ?? config('app.name') }}</h1>
        <p>{{ $display['library_address'] ?? '' }}</p>
        <p>{{ $display['library_phone'] ?? '' }} | {{ $display['library_email'] ?? '' }}</p>
        <h2 style="margin-top: 15px;">INVOICE</h2>
    </div>

    <div class="details">
        <table>
            <tr><td class="label">Invoice Number:</td><td>{{ $invoice->invoice_number }}</td></tr>
            <tr><td class="label">Date Issued:</td><td>{{ $invoice->issued_at?->format('d M Y H:i') ?? $invoice->created_at->format('d M Y H:i') }}</td></tr>
            <tr><td class="label">Due Date:</td><td>{{ $invoice->due_at?->format('d M Y') ?? 'N/A' }}</td></tr>
            <tr><td class="label">Status:</td><td><span class="status-badge status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span></td></tr>
            <tr><td class="label">Bill To:</td><td>{{ $invoice->user?->name ?? 'N/A' }}</td></tr>
            <tr><td class="label">Email:</td><td>{{ $invoice->user?->email ?? 'N/A' }}</td></tr>
        </table>
    </div>

    <table class="invoice-table">
        <thead>
            <tr><th>Description</th><th>Type</th><th style="text-align: right;">Amount ({{ $invoice->currency }})</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $invoice->description ?? 'Invoice for ' . str_replace('_', ' ', $invoice->type) }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $invoice->type)) }}</td>
                <td style="text-align: right;">{{ number_format($invoice->amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="2" style="text-align: right;">Total:</td>
                <td style="text-align: right;">{{ number_format($invoice->amount, 2) }} {{ $invoice->currency }}</td>
            </tr>
        </tbody>
    </table>

    @if($invoice->status === 'paid')
        <p style="text-align: center; color: #155724; font-weight: bold;">Paid on {{ $invoice->paid_at?->format('d M Y H:i') ?? 'N/A' }}</p>
    @endif

    <div class="footer">
        <p>{{ $branding['document_footer_text'] ?? 'Official Library Document' }}</p>
        <p>{{ $branding['document_footer_disclaimer'] ?? '' }}</p>
    </div>
</body>
</html>

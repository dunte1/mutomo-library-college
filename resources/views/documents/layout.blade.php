<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $documentMeta['title'] ?? $title ?? 'Document' }}</title>
    <style>
        @page {
            margin: 20mm 15mm 25mm 15mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .letterhead {
            width: 100%;
            border-bottom: 3px solid {{ $branding['primary_color'] }};
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .letterhead-table {
            width: 100%;
        }

        .letterhead-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }

        .letterhead-text {
            text-align: center;
            vertical-align: middle;
        }

        .letterhead-institution {
            font-size: 18px;
            font-weight: 800;
            color: {{ $branding['primary_color'] }};
            margin: 0;
            line-height: 1.2;
        }

        .letterhead-header {
            font-size: 13px;
            font-weight: 600;
            color: #555;
            margin: 2px 0;
        }

        .letterhead-contact {
            font-size: 9px;
            color: #777;
            margin: 1px 0;
        }

        .document-title {
            font-size: 16px;
            font-weight: 700;
            color: #111;
            text-align: center;
            margin: 16px 0 4px;
        }

        .document-meta {
            text-align: center;
            font-size: 10px;
            color: #888;
            margin-bottom: 18px;
        }

        .document-meta span {
            margin: 0 8px;
        }

        .content {
            margin-bottom: 30px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        table.data-table th {
            background: {{ $branding['primary_color'] }};
            color: #fff;
            text-align: left;
            padding: 7px 9px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid {{ $branding['primary_color'] }};
        }

        table.data-table td {
            padding: 6px 9px;
            border: 1px solid #e0e0e0;
        }

        table.data-table tr:nth-child(even) td {
            background: #f8f9fa;
        }

        .section-heading {
            font-size: 13px;
            font-weight: 700;
            color: {{ $branding['primary_color'] }};
            margin: 18px 0 8px;
            padding-bottom: 4px;
            border-bottom: 2px solid {{ $branding['primary_color'] }};
        }

        .verification-stamp {
            text-align: center;
            margin-top: 30px;
            padding-top: 16px;
            border-top: 2px dashed #ccc;
        }

        .verification-stamp img {
            width: 100px;
            height: 100px;
        }

        .verification-text {
            font-size: 9px;
            color: #999;
            margin-top: 6px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #aaa;
            padding: 8px 15mm;
            border-top: 1px solid #e5e7eb;
        }

        .footer-content {
            max-width: 100%;
        }

        .page-number:before {
            content: "Page " counter(page);
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 60px;
            color: rgba(200, 200, 200, 0.15);
            font-weight: 900;
            white-space: nowrap;
            pointer-events: none;
            z-index: -1;
        }
    </style>
</head>
<body>

    @if(!empty($branding['watermark_text']))
    <div class="watermark">{{ $branding['watermark_text'] }}</div>
    @endif

    <div class="letterhead">
        <table class="letterhead-table">
            <tr>
                @if(!empty($branding['logo_url']))
                <td width="90" style="vertical-align:middle;">
                    <img src="{{ $branding['logo_url'] }}" class="letterhead-logo" alt="Logo">
                </td>
                @endif
                <td class="letterhead-text">
                    <div class="letterhead-institution">{{ $branding['institution_name'] }}</div>
                    <div class="letterhead-header">{{ $branding['header_text'] }}</div>
                    @if($branding['institution_address'])
                    <div class="letterhead-contact">{{ $branding['institution_address'] }}</div>
                    @endif
                    @if($branding['institution_phone'] || $branding['institution_email'])
                    <div class="letterhead-contact">
                        @if($branding['institution_phone'])Tel: {{ $branding['institution_phone'] }}@endif
                        @if($branding['institution_phone'] && $branding['institution_email']) | @endif
                        @if($branding['institution_email'])Email: {{ $branding['institution_email'] }}@endif
                    </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="document-title">{{ $documentMeta['title'] }}</div>
    <div class="document-meta">
        <span>Document ID: {{ $documentMeta['document_id'] }}</span>
        <span>|</span>
        <span>Generated: {{ $documentMeta['generated_at'] }}</span>
        <span>|</span>
        <span>By: {{ $documentMeta['generated_by'] }}</span>
    </div>

    <div class="content">
        @yield('content')
    </div>

    @if($branding['show_verification_stamp'] || $branding['show_qr_code'])
    <div class="verification-stamp">
        @if($branding['show_qr_code'] && !empty($documentMeta['qr_code_svg']))
            <div style="margin-bottom:8px;">
                {!! $documentMeta['qr_code_svg'] !!}
            </div>
        @endif
        <div class="verification-text">
            @if($branding['show_verification_stamp'])
                This document is electronically generated.<br>
            @endif
            Verify authenticity at: {{ $documentMeta['verification_url'] }}<br>
            Document ID: {{ $documentMeta['document_id'] }}
        </div>
    </div>
    @endif

    <div class="footer">
        <div class="footer-content">
            {{ $branding['footer_text'] }} —
            <span class="page-number"></span> —
            {{ $branding['institution_name'] }}
            @if($branding['footer_disclaimer'])
            | {{ $branding['footer_disclaimer'] }}
            @endif
        </div>
    </div>

</body>
</html>

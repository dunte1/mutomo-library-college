<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Library Card - {{ $member->full_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', 'Helvetica Neue', Arial, sans-serif;
            width: 320px;
            height: 520px;
            background: transparent;
        }
        .card {
            width: 320px;
            height: 520px;
            background: linear-gradient(135deg, #1a365d 0%, #153168 50%, #0f2453 100%);
            border-radius: 16px;
            overflow: hidden;
            position: relative;
            color: white;
        }
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .card-header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-header-top .logo {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .card-header-top .logo svg {
            width: 20px;
            height: 20px;
        }
        .card-header-top .logo span {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .card-header-top .institution {
            font-size: 9px;
            opacity: 0.7;
            font-family: monospace;
        }
        .card-body {
            padding: 16px 20px;
        }
        .photo-section {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        .photo {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: rgba(255,255,255,0.1);
            border: 2px solid rgba(255,255,255,0.2);
            overflow: hidden;
            flex-shrink: 0;
        }
        .photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .photo-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
        }
        .name-section h4 {
            font-size: 16px;
            font-weight: 700;
            color: white;
            margin-bottom: 2px;
        }
        .name-section p {
            font-size: 10px;
            opacity: 0.7;
            text-transform: capitalize;
        }
        .card-number-section {
            margin-bottom: 10px;
        }
        .card-number-section .label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.6;
            margin-bottom: 2px;
        }
        .card-number-section .number {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 1.5px;
        }
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            margin-bottom: 10px;
            font-size: 9px;
        }
        .details-grid .item .label {
            opacity: 0.6;
            margin-bottom: 1px;
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .details-grid .item .value {
            font-weight: 500;
        }
        .details-grid .item .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: 600;
            background: rgba(16, 185, 129, 0.2);
            color: #6ee7b7;
        }
        .status-badge .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #34d399;
        }
        .barcode-section {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 8px;
            margin-bottom: 8px;
        }
        .barcode-section .barcode {
            background: white;
            border-radius: 4px;
            padding: 4px;
            text-align: center;
        }
        .barcode-section .barcode span {
            font-family: 'Courier New', monospace;
            font-size: 10px;
            color: #333;
            letter-spacing: 3px;
        }
        .qr-section {
            text-align: center;
            margin-bottom: 8px;
        }
        .qr-section .qr-box {
            display: inline-block;
            background: white;
            border-radius: 6px;
            padding: 4px;
        }
        .qr-section .qr-box svg, .qr-section .qr-box img {
            width: 60px;
            height: 60px;
        }
        .card-footer {
            padding: 10px 20px;
            background: rgba(0,0,0,0.2);
            border-top: 1px solid rgba(255,255,255,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 8px;
        }
        .card-footer .footer-text {
            opacity: 0.6;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <div class="card-header-top">
                <div class="logo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span>LIBRARY CARD</span>
                </div>
                <span class="institution">{{ config('app.name') }}</span>
            </div>
        </div>
        <div class="card-body">
            <div class="photo-section">
                <div class="photo">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="{{ $member->full_name }}">
                    @else
                        <div class="photo-placeholder">
                            {{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div class="name-section">
                    <h4>{{ $member->full_name }}</h4>
                    <p>{{ $member->membership_type }}</p>
                </div>
            </div>

            <div class="card-number-section">
                <div class="label">Card Number</div>
                <div class="number">{{ $card->card_number }}</div>
            </div>

            <div class="details-grid">
                <div class="item">
                    <div class="label">Member ID</div>
                    <div class="value">{{ $member->member_id }}</div>
                </div>
                <div class="item">
                    <div class="label">Department</div>
                    <div class="value">{{ $member->department?->name ?? 'N/A' }}</div>
                </div>
                <div class="item">
                    <div class="label">Issued</div>
                    <div class="value">{{ $card->issued_at->format('d M Y') }}</div>
                </div>
                <div class="item">
                    <div class="label">Expires</div>
                    <div class="value">{{ $card->expires_at?->format('d M Y') ?? 'N/A' }}</div>
                </div>
            </div>

            <div class="barcode-section">
                <div class="barcode">
                    <span>{{ $card->barcode }}</span>
                </div>
            </div>

            @if($qrCodeSvg)
                <div class="qr-section">
                    <div class="qr-box">
                        {!! $qrCodeSvg !!}
                    </div>
                </div>
            @endif
        </div>

        <div class="card-footer">
            <span>{{ config('app.name') }}</span>
            <span class="footer-text">Present this card for library services</span>
        </div>
    </div>
</body>
</html>

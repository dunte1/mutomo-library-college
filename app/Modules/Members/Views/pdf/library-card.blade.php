<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Library Card - {{ $member->full_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Helvetica, Arial, sans-serif; width: 1011px; background: transparent; }
        .card { width: 1011px; height: 638px; background: #fff; position: relative; overflow: hidden; }
        .header { width: 1011px; height: 100px; position: relative; }
        .header-bg { width: 1011px; height: 100px; }
        .wave { width: 1011px; height: 20px; margin-top: -1px; }
        .body-table { width: 1011px; }
        .body-table td { vertical-align: top; }
        .info-icon { display: inline-block; width: 26px; height: 26px; border-radius: 6px; text-align: center; line-height: 26px; font-size: 12px; color: #fff; vertical-align: middle; }
        .info-label { display: inline-block; width: 95px; font-size: 11px; color: #5a6a82; vertical-align: middle; font-weight: 500; }
        .info-value { font-size: 12px; font-weight: 600; color: #0a1e3a; vertical-align: middle; }
        .footer-bar { width: 1011px; height: 50px; }
    </style>
</head>
<body>
@php
    $primary = $cardBranding['card_primary_color'];
    $secondary = $cardBranding['card_secondary_color'];
    $tertiary = $cardBranding['card_tertiary_color'];
    $textColor = $cardBranding['card_text_color'];
    $accentColor = $cardBranding['card_accent_color'];
    $cardLogo = $cardBranding['card_logo'] ?? '';
    $hasLogo = $cardLogo && file_exists(storage_path('app/public/' . $cardLogo));
    $siteName = $displaySettings['site_name'] ?? config('app.name');
    $shortName = strtoupper(explode(' ', $siteName)[0] ?? 'OLLMCHS');
    $motto = $displaySettings['library_motto'] ?? 'Learn, Discover, Succeed';
    $phone = $displaySettings['library_phone'] ?? '';
    $email = $displaySettings['library_email'] ?? '';
    $website = $displaySettings['library_website'] ?? '';
    $address = $displaySettings['library_address'] ?? '';
@endphp

<div class="card">

    <!-- ===== HEADER ===== -->
    <div class="header">
        <table class="header-bg" cellpadding="0" cellspacing="0" border="0" width="1011" height="100">
            <tr>
                <td style="width:62%;background:{{ $primary }};padding:0 0 0 32px;vertical-align:middle;position:relative;">
                    <table cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td style="padding-right:16px;vertical-align:middle;">
                                @if($hasLogo)
                                    <div style="width:52px;height:52px;border-radius:50%;overflow:hidden;border:2px solid rgba(255,255,255,.25);">
                                        <img src="{{ storage_path('app/public/' . $cardLogo) }}" width="52" height="52" style="object-fit:cover;" alt="Logo">
                                    </div>
                                @else
                                    <div style="width:52px;height:52px;border-radius:50%;background:{{ $secondary }};border:2px solid rgba(255,255,255,.25);text-align:center;line-height:52px;">
                                        <svg viewBox="0 0 32 36" width="28" height="28" style="vertical-align:middle;">
                                            <path d="M16 2L28 8V18C28 26 22 32 16 34C10 32 4 26 4 18V8L16 2Z" fill="rgba(255,255,255,.12)" stroke="rgba(255,255,255,.5)" stroke-width="1.5"/>
                                            <rect x="13.5" y="10" width="5" height="16" rx="1" fill="rgba(255,255,255,.7)"/>
                                            <rect x="8" y="15.5" width="16" height="5" rx="1" fill="rgba(255,255,255,.7)"/>
                                        </svg>
                                    </div>
                                @endif
                            </td>
                            <td style="vertical-align:middle;">
                                <div style="font-size:15px;font-weight:800;color:#fff;letter-spacing:.8px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">{{ strtoupper(explode(' & ', $siteName)[0] ?? 'OUR LADY OF LOURDES') }}</div>
                                <div style="font-size:10.5px;font-weight:700;color:rgba(255,255,255,.8);letter-spacing:.5px;margin-top:1px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">{{ strtoupper($siteName) }}</div>
                                <div style="font-size:11px;font-weight:800;color:#FFC107;letter-spacing:2.5px;margin-top:4px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">LIBRARY MEMBERSHIP CARD</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width:38%;background:#fff;padding:20px 32px 20px 20px;vertical-align:middle;text-align:right;">
                    <!-- NFC Chip -->
                    <table cellpadding="0" cellspacing="0" border="0" style="margin-left:auto;">
                        <tr>
                            <td style="width:52px;height:40px;background:#c0c0c0;border:1px solid #a0a0a0;border-radius:4px;text-align:center;vertical-align:middle;">
                                <table cellpadding="0" cellspacing="2" border="0" align="center">
                                    <tr>
                                        <td style="width:10px;height:11px;background:#d4af37;border-radius:1px;"></td>
                                        <td style="width:10px;"></td>
                                        <td style="width:10px;height:11px;background:#d4af37;border-radius:1px;"></td>
                                    </tr>
                                    <tr>
                                        <td style="width:10px;height:11px;background:#d4af37;border-radius:1px;"></td>
                                        <td style="width:10px;"></td>
                                        <td style="width:10px;height:11px;background:#d4af37;border-radius:1px;"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- ===== WAVE ===== -->
    <svg class="wave" viewBox="0 0 1011 20" preserveAspectRatio="none" width="1011" height="20">
        <path d="M0,0 C200,20 400,20 505,10 C610,0 810,0 1011,15 L1011,20 L0,20 Z" fill="#ffffff"/>
    </svg>

    <!-- ===== BODY ===== -->
    <table class="body-table" cellpadding="0" cellspacing="0" border="0" width="1011" height="468">
        <tr>
            <!-- Photo Column -->
            <td style="width:155px;vertical-align:top;padding:18px 0 14px 28px;text-align:center;">
                @if($photoUrl)
                    <div style="width:148px;height:178px;border:3px solid #fff;overflow:hidden;background:#e9eef5;">
                        <img src="{{ $photoUrl }}" width="148" height="178" style="object-fit:cover;display:block;" alt="{{ $member->full_name }}">
                    </div>
                @else
                    <div style="width:148px;height:178px;border:3px solid #fff;background:#e9eef5;text-align:center;line-height:178px;font-size:42px;font-weight:700;color:{{ $primary }};font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                        {{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}
                    </div>
                @endif
                <!-- Status badge -->
                <div style="display:inline-block;margin-top:10px;padding:4px 12px;border-radius:10px;background:#e8f5e9;font-size:10px;font-weight:700;color:#2e7d32;letter-spacing:.5px;">
                    &#9679; ACTIVE
                </div>
            </td>

            <!-- Info Column -->
            <td style="vertical-align:top;padding:22px 20px 14px 22px;">
                <div style="font-size:19px;font-weight:700;color:#0a1e3a;letter-spacing:.3px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">{{ $member->full_name }}</div>
                <div style="display:inline-block;padding:3px 14px;background:{{ $teal ?? '#0097A7' }};color:#fff;font-size:10px;font-weight:700;letter-spacing:1.5px;margin-top:5px;">{{ strtoupper($member->membership_type) }}</div>

                <table cellpadding="0" cellspacing="0" border="0" style="margin-top:12px;">
                    @if($member->admission_number)
                    <tr>
                        <td style="padding-bottom:8px;">
                            <table cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-right:10px;vertical-align:middle;">
                                        <div style="width:26px;height:26px;border-radius:6px;background:{{ $primary }};text-align:center;line-height:26px;">
                                            <span style="color:#fff;font-size:12px;">&#128196;</span>
                                        </div>
                                    </td>
                                    <td style="padding-right:10px;vertical-align:middle;">
                                        <span class="info-label">Admission No.</span>
                                    </td>
                                    <td style="vertical-align:middle;">
                                        <span class="info-value">{{ $member->admission_number }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif
                    @if($member->program)
                    <tr>
                        <td style="padding-bottom:8px;">
                            <table cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-right:10px;vertical-align:middle;">
                                        <div style="width:26px;height:26px;border-radius:6px;background:{{ $primary }};text-align:center;line-height:26px;">
                                            <span style="color:#fff;font-size:12px;">&#128218;</span>
                                        </div>
                                    </td>
                                    <td style="padding-right:10px;vertical-align:middle;">
                                        <span class="info-label">Programme</span>
                                    </td>
                                    <td style="vertical-align:middle;">
                                        <span class="info-value">{{ $member->program->name }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif
                    @if($member->department)
                    <tr>
                        <td style="padding-bottom:8px;">
                            <table cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-right:10px;vertical-align:middle;">
                                        <div style="width:26px;height:26px;border-radius:6px;background:{{ $primary }};text-align:center;line-height:26px;">
                                            <span style="color:#fff;font-size:12px;">&#127963;</span>
                                        </div>
                                    </td>
                                    <td style="padding-right:10px;vertical-align:middle;">
                                        <span class="info-label">Department</span>
                                    </td>
                                    <td style="vertical-align:middle;">
                                        <span class="info-value">{{ $member->department->name }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td style="padding-bottom:8px;">
                            <table cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-right:10px;vertical-align:middle;">
                                        <div style="width:26px;height:26px;border-radius:6px;background:{{ $primary }};text-align:center;line-height:26px;">
                                            <span style="color:#fff;font-size:12px;">&#128197;</span>
                                        </div>
                                    </td>
                                    <td style="padding-right:10px;vertical-align:middle;">
                                        <span class="info-label">Issue Date</span>
                                    </td>
                                    <td style="vertical-align:middle;">
                                        <span class="info-value">{{ $card->issued_at->format('d M Y') }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-right:10px;vertical-align:middle;">
                                        <div style="width:26px;height:26px;border-radius:6px;background:{{ $primary }};text-align:center;line-height:26px;">
                                            <span style="color:#fff;font-size:12px;">&#9201;</span>
                                        </div>
                                    </td>
                                    <td style="padding-right:10px;vertical-align:middle;">
                                        <span class="info-label">Expiry Date</span>
                                    </td>
                                    <td style="vertical-align:middle;">
                                        <span class="info-value">{{ $card->expires_at ? $card->expires_at->format('d M Y') : 'N/A' }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>

            <!-- Codes Column -->
            <td style="width:195px;vertical-align:top;padding:18px 28px 14px 0;text-align:right;">
                @if($qrCodeSvg)
                    <div style="display:inline-block;background:#fff;border:1px solid rgba(11,60,109,.08);padding:6px;">
                        <div style="width:128px;height:128px;">{!! $qrCodeSvg !!}</div>
                    </div>
                @endif
                <div style="margin-top:12px;background:#fff;border:1px solid rgba(11,60,109,.08);padding:6px 8px 4px;text-align:center;width:195px;">
                    @if($card->barcode && str_contains($card->barcode, '<svg'))
                        <div style="width:100%;height:36px;">{!! $card->barcode !!}</div>
                    @else
                        <div style="height:36px;line-height:36px;font-size:10px;letter-spacing:1px;font-weight:700;color:#0a1e3a;">{{ $card->barcode }}</div>
                    @endif
                    <div style="font-size:10px;letter-spacing:1.2px;font-weight:700;color:#0a1e3a;margin-top:3px;">{{ $card->card_number }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- ===== FOOTER ===== -->
    <table class="footer-bar" cellpadding="0" cellspacing="0" border="0" width="1011" height="50">
        <tr>
            <td style="background:#E9EEF5;border-top:1px solid rgba(11,60,109,.06);text-align:center;vertical-align:middle;">
                @if($website)
                    <span style="font-size:9.5px;font-weight:500;color:#5a6a82;">&#127760; {{ $website }}</span>
                    <span style="color:rgba(11,60,109,.2);font-size:10px;margin:0 6px;">|</span>
                @endif
                @if($email)
                    <span style="font-size:9.5px;font-weight:500;color:#5a6a82;">&#9993; {{ $email }}</span>
                    <span style="color:rgba(11,60,109,.2);font-size:10px;margin:0 6px;">|</span>
                @endif
                @if($phone)
                    <span style="font-size:9.5px;font-weight:500;color:#5a6a82;">&#9742; {{ $phone }}</span>
                @endif
            </td>
        </tr>
    </table>

</div>
</body>
</html>

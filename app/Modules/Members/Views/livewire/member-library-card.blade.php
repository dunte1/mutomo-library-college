@section('title', 'My Library Card')
<div>
    <x-slot name="header">My Library Card</x-slot>
    <x-slot name="subtitle">View and manage your library card</x-slot>

    <style>
      @import url('https://fonts.bunny.net/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@700;800&family=Poppins:wght@600;700&display=swap');
    </style>

    <div class="max-w-4xl mx-auto">
        @if($member && $card)
            @php
                $primary = $cardBranding['card_primary_color'];
                $secondary = $cardBranding['card_secondary_color'];
                $tertiary = $cardBranding['card_tertiary_color'];
                $textColor = $cardBranding['card_text_color'];
                $accentColor = $cardBranding['card_accent_color'];
                $cardLogo = $cardBranding['card_logo'] ?: null;
                $siteName = $displaySettings['site_name'] ?? config('app.name');
                $shortName = strtoupper(explode(' ', $siteName)[0] ?? 'OLLMCHS');
                $motto = $displaySettings['library_motto'] ?? 'Learn • Discover • Succeed';
                $phone = $displaySettings['library_phone'] ?? '';
                $email = $displaySettings['library_email'] ?? '';
                $website = $displaySettings['library_website'] ?? '';
                $address = $displaySettings['library_address'] ?? '';
            @endphp
            @php
                $photoUrl = null;
                if ($card->passport_photo && file_exists(storage_path('app/public/' . $card->passport_photo))) {
                    $photoUrl = storage_path('app/public/' . $card->passport_photo);
                } elseif ($member->photo && file_exists(storage_path('app/public/' . $member->photo))) {
                    $photoUrl = storage_path('app/public/' . $member->photo);
                }
            @endphp

            <div class="space-y-6">
                {{-- Card Preview --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-semibold text-surface-900 dark:text-white">Library Card</h3>
                        <button wire:click="downloadCard" class="btn-outline btn-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download
                        </button>
                    </div>

                    <div class="card-body py-8 overflow-x-auto">
                        <div class="min-w-max flex flex-col items-center gap-6">

                        {{-- ===== CR80 SINGLE-SIDED CARD ===== --}}
                        <div style="width:1011px;height:638px;border-radius:14px;overflow:hidden;position:relative;background:#fff;box-shadow:0 2px 4px rgba(11,60,109,.06),0 8px 24px rgba(11,60,109,.10),0 24px 60px rgba(11,60,109,.14);font-family:'Inter',system-ui,sans-serif;">

                            {{-- Hexagon micro-pattern --}}
                            <div style="position:absolute;inset:0;z-index:1;pointer-events:none;opacity:.035;background-image:url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='28' height='49' viewBox='0 0 28 49'%3E%3Cg fill-rule='evenodd'%3E%3Cg fill='%230B3C6D'%3E%3Cpath d='M13.99 9.25l13 7.5v15l-13 7.5L1 31.75v-15l12.99-7.5zM3 17.9v12.7l10.99 6.34 11-6.35V17.9l-11-6.34L3 17.9zM0 15l12.98-7.5V0h-2v6.35L0 12.69v2.3zm0 18.5L12.98 41v8h-2v-6.85L0 35.81v-2.3zM15 0v7.5L27.99 15H28v-2.31h-.01L17 6.35V0h-2zm0 49v-8l12.99-7.5H28v2.31h-.01L17 42.15V49h-2z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E&quot;);"></div>

                            {{-- Security watermark --}}
                            <div style="position:absolute;top:50%;left:50%;width:320px;height:320px;transform:translate(-50%,-50%) rotate(22deg);z-index:2;pointer-events:none;opacity:.025;">
                                <svg viewBox="0 0 100 100" width="100%" height="100%">
                                    <path d="M50 5L90 22Q95 50 90 78L50 95L10 78Q5 50 10 22Z" fill="none" stroke="{{ $primary }}" stroke-width="2.5"/>
                                    <text x="50" y="56" text-anchor="middle" font-family="Montserrat,sans-serif" font-size="16" font-weight="800" fill="{{ $primary }}">OLLMCHS</text>
                                </svg>
                            </div>

                            {{-- Medical cross motif --}}
                            <div style="position:absolute;z-index:2;pointer-events:none;right:40px;top:120px;width:60px;height:60px;opacity:.03;">
                                <svg viewBox="0 0 40 40" width="100%" height="100%">
                                    <rect x="15" y="4" width="10" height="32" rx="1" fill="{{ $primary }}"/>
                                    <rect x="4" y="15" width="32" height="10" rx="1" fill="{{ $primary }}"/>
                                </svg>
                            </div>

                            {{-- Circuit lines --}}
                            <div style="position:absolute;z-index:2;pointer-events:none;left:20px;bottom:60px;width:100px;height:60px;opacity:.04;">
                                <svg viewBox="0 0 100 60" width="100%" height="100%">
                                    <line x1="0" y1="30" x2="40" y2="30" stroke="{{ $primary }}" stroke-width=".8"/>
                                    <line x1="40" y1="30" x2="55" y2="10" stroke="{{ $primary }}" stroke-width=".8"/>
                                    <line x1="55" y1="10" x2="80" y2="10" stroke="{{ $primary }}" stroke-width=".8"/>
                                    <circle cx="40" cy="30" r="2.5" fill="{{ $primary }}"/>
                                    <circle cx="55" cy="10" r="2.5" fill="{{ $primary }}"/>
                                    <circle cx="80" cy="10" r="2.5" fill="{{ $primary }}"/>
                                </svg>
                            </div>

                            {{-- ===== HEADER ===== --}}
                            <div style="height:100px;position:relative;z-index:10;background:linear-gradient(135deg,{{ $primary }} 0%,{{ $secondary }} 100%);display:flex;align-items:center;justify-content:space-between;padding:0 32px;overflow:hidden;">
                                <div style="position:absolute;inset:0;background:radial-gradient(circle at 20% 80%,rgba(0,151,167,.15) 0%,transparent 50%),radial-gradient(circle at 80% 20%,rgba(255,193,7,.08) 0%,transparent 40%);"></div>
                                <div style="display:flex;align-items:center;gap:16px;position:relative;z-index:2;">
                                    @if($cardLogo && file_exists(storage_path('app/public/' . $cardLogo)))
                                        <div style="width:52px;height:52px;border-radius:50%;overflow:hidden;flex-shrink:0;border:2px solid rgba(255,255,255,.25);box-shadow:0 0 20px rgba(0,151,167,.25);background:radial-gradient(circle at 35% 30%,{{ $secondary }},{{ $primary }});">
                                            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(storage_path('app/public/' . $cardLogo))) }}"
                                                 alt="Logo" style="width:100%;height:100%;object-fit:cover;">
                                        </div>
                                    @else
                                        <div style="width:52px;height:52px;border-radius:50%;flex-shrink:0;background:radial-gradient(circle at 35% 30%,{{ $secondary }},{{ $primary }});border:2px solid rgba(255,255,255,.25);display:flex;align-items:center;justify-content:center;box-shadow:0 0 20px rgba(0,151,167,.25);">
                                            <svg viewBox="0 0 32 36" width="28" height="28" fill="none">
                                                <path d="M16 2L28 8V18C28 26 22 32 16 34C10 32 4 26 4 18V8L16 2Z" fill="rgba(255,255,255,.12)" stroke="rgba(255,255,255,.5)" stroke-width="1.5"/>
                                                <rect x="13.5" y="10" width="5" height="16" rx="1" fill="rgba(255,255,255,.7)"/>
                                                <rect x="8" y="15.5" width="16" height="5" rx="1" fill="rgba(255,255,255,.7)"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div>
                                        <div style="font-family:'Montserrat',sans-serif;font-weight:800;font-size:15px;color:#fff;letter-spacing:.8px;line-height:1.2;">{{ strtoupper(explode(' & ', $siteName)[0] ?? 'OUR LADY OF LOURDES') }}</div>
                                        <div style="font-family:'Montserrat',sans-serif;font-weight:700;font-size:10.5px;color:rgba(255,255,255,.8);letter-spacing:.5px;line-height:1.3;margin-top:1px;">{{ strtoupper($siteName) }}</div>
                                        <div style="font-family:'Montserrat',sans-serif;font-weight:800;font-size:11px;color:#FFC107;letter-spacing:2.5px;line-height:1;margin-top:4px;">LIBRARY MEMBERSHIP CARD</div>
                                    </div>
                                </div>
                                <div style="position:relative;z-index:2;display:flex;align-items:center;gap:0;">
                                    <div style="width:52px;height:40px;border-radius:5px;background:linear-gradient(145deg,#d4d4d4,#b8b8b8);border:1px solid #a0a0a0;position:relative;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.2);">
                                        <div style="position:absolute;top:6px;left:6px;right:6px;bottom:6px;border:1.5px solid #999;border-radius:3px;"></div>
                                        <div style="position:absolute;left:10px;top:5px;width:10px;height:11px;border-radius:1px;background:linear-gradient(180deg,#e8c84a,#c9a82c);"></div>
                                        <div style="position:absolute;left:10px;top:18px;width:10px;height:11px;border-radius:1px;background:linear-gradient(180deg,#e8c84a,#c9a82c);"></div>
                                        <div style="position:absolute;right:10px;top:5px;width:10px;height:11px;border-radius:1px;background:linear-gradient(180deg,#e8c84a,#c9a82c);"></div>
                                        <div style="position:absolute;right:10px;top:18px;width:10px;height:11px;border-radius:1px;background:linear-gradient(180deg,#e8c84a,#c9a82c);"></div>
                                    </div>
                                    <svg width="22" height="30" viewBox="0 0 22 30" style="margin-left:-4px;">
                                        <path d="M2 25Q8 15 2 5" fill="none" stroke="rgba(255,255,255,.35)" stroke-width="1.5" stroke-linecap="round"/>
                                        <path d="M7 27Q15 15 7 3" fill="none" stroke="rgba(255,255,255,.35)" stroke-width="1.5" stroke-linecap="round"/>
                                        <path d="M12 28Q22 15 12 2" fill="none" stroke="rgba(255,255,255,.35)" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </div>
                            </div>

                            {{-- Wave divider --}}
                            <div style="height:20px;position:relative;z-index:10;margin-top:-1px;overflow:hidden;">
                                <svg viewBox="0 0 1011 20" preserveAspectRatio="none" style="display:block;width:100%;height:100%;">
                                    <path d="M0,0 C200,20 400,20 505,10 C610,0 810,0 1011,15 L1011,20 L0,20 Z" fill="#fff"/>
                                </svg>
                            </div>

                            {{-- ===== BODY ===== --}}
                            <div style="display:grid;grid-template-columns:155px 1fr 195px;gap:22px;padding:18px 28px 14px;height:468px;position:relative;z-index:10;">

                                {{-- Photo + Status --}}
                                <div style="display:flex;flex-direction:column;align-items:center;gap:10px;">
                                    <div style="width:148px;height:178px;border-radius:10px;border:3px solid #fff;box-shadow:0 4px 20px rgba(11,60,109,.10);overflow:hidden;background:#e9eef5;position:relative;">
                                        @if($photoUrl)
                                            <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents($photoUrl)) }}"
                                                 alt="{{ $member->full_name }}" style="width:100%;height:100%;object-fit:cover;display:block;">
                                        @else
                                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-family:'Poppins',sans-serif;font-weight:700;font-size:42px;color:{{ $primary }};background:linear-gradient(135deg,#e8edf5,#dde4ef);">
                                                {{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div style="display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:20px;background:linear-gradient(135deg,#e8f5e9,#c8e6c9);font-family:'Inter',sans-serif;font-weight:700;font-size:10px;color:#2e7d32;letter-spacing:.5px;">
                                        <span style="width:6px;height:6px;border-radius:50%;background:#4caf50;box-shadow:0 0 4px rgba(76,175,80,.5);"></span>
                                        ACTIVE
                                    </div>
                                </div>

                                {{-- Member Info --}}
                                <div style="display:flex;flex-direction:column;padding-top:4px;min-width:0;">
                                    <div style="font-family:'Poppins',sans-serif;font-weight:700;font-size:19px;color:#0a1e3a;letter-spacing:.3px;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $member->full_name }}</div>
                                    <div style="display:inline-flex;align-self:flex-start;padding:3px 14px;border-radius:4px;background:linear-gradient(135deg,#0097A7,#00acc1);color:#fff;font-family:'Inter',sans-serif;font-weight:700;font-size:10px;letter-spacing:1.5px;margin-top:5px;">{{ strtoupper($member->membership_type) }}</div>

                                    <div style="margin-top:12px;display:flex;flex-direction:column;gap:8px;">
                                        @if($member->admission_number)
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <div style="width:26px;height:26px;border-radius:7px;flex-shrink:0;background:linear-gradient(135deg,{{ $primary }},{{ $secondary }});display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(11,60,109,.12);">
                                                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="2" width="18" height="14" rx="2"/><line x1="8" y1="22" x2="16" y2="22"/><line x1="12" y1="16" x2="12" y2="22"/><circle cx="12" cy="9" r="2.5"/><path d="M7 13c0 0 2-1.5 5-1.5s5 1.5 5 1.5"/></svg>
                                            </div>
                                            <div style="font-family:'Inter',sans-serif;font-weight:500;font-size:11px;color:#5a6a82;width:95px;flex-shrink:0;">Admission No.</div>
                                            <div style="font-family:'Inter',sans-serif;font-weight:600;font-size:12px;color:#0a1e3a;">{{ $member->admission_number }}</div>
                                        </div>
                                        @endif
                                        @if($member->program)
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <div style="width:26px;height:26px;border-radius:7px;flex-shrink:0;background:linear-gradient(135deg,{{ $primary }},{{ $secondary }});display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(11,60,109,.12);">
                                                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/><line x1="9" y1="7" x2="16" y2="7"/><line x1="9" y1="11" x2="14" y2="11"/></svg>
                                            </div>
                                            <div style="font-family:'Inter',sans-serif;font-weight:500;font-size:11px;color:#5a6a82;width:95px;flex-shrink:0;">Programme</div>
                                            <div style="font-family:'Inter',sans-serif;font-weight:600;font-size:12px;color:#0a1e3a;">{{ $member->program->name }}</div>
                                        </div>
                                        @endif
                                        @if($member->department)
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <div style="width:26px;height:26px;border-radius:7px;flex-shrink:0;background:linear-gradient(135deg,{{ $primary }},{{ $secondary }});display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(11,60,109,.12);">
                                                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="1"/><rect x="8" y="6" width="2.5" height="2.5" rx=".5"/><rect x="13.5" y="6" width="2.5" height="2.5" rx=".5"/><rect x="8" y="11" width="2.5" height="2.5" rx=".5"/><rect x="13.5" y="11" width="2.5" height="2.5" rx=".5"/><rect x="10" y="17" width="4" height="5" rx=".5"/></svg>
                                            </div>
                                            <div style="font-family:'Inter',sans-serif;font-weight:500;font-size:11px;color:#5a6a82;width:95px;flex-shrink:0;">Department</div>
                                            <div style="font-family:'Inter',sans-serif;font-weight:600;font-size:12px;color:#0a1e3a;">{{ $member->department->name }}</div>
                                        </div>
                                        @endif
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <div style="width:26px;height:26px;border-radius:7px;flex-shrink:0;background:linear-gradient(135deg,{{ $primary }},{{ $secondary }});display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(11,60,109,.12);">
                                                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                            </div>
                                            <div style="font-family:'Inter',sans-serif;font-weight:500;font-size:11px;color:#5a6a82;width:95px;flex-shrink:0;">Issue Date</div>
                                            <div style="font-family:'Inter',sans-serif;font-weight:600;font-size:12px;color:#0a1e3a;">{{ $card->issued_at->format('d M Y') }}</div>
                                        </div>
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <div style="width:26px;height:26px;border-radius:7px;flex-shrink:0;background:linear-gradient(135deg,{{ $primary }},{{ $secondary }});display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(11,60,109,.12);">
                                                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                            </div>
                                            <div style="font-family:'Inter',sans-serif;font-weight:500;font-size:11px;color:#5a6a82;width:95px;flex-shrink:0;">Expiry Date</div>
                                            <div style="font-family:'Inter',sans-serif;font-weight:600;font-size:12px;color:#0a1e3a;">{{ $card->expires_at?->format('d M Y') ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- QR + Barcode --}}
                                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:12px;padding-top:2px;">
                                    @if($card->qr_code)
                                        <div style="background:#fff;border:1px solid rgba(11,60,109,.08);border-radius:10px;padding:6px;box-shadow:0 2px 12px rgba(11,60,109,.05);">
                                            <div style="width:128px;height:128px;">{!! $card->qr_code !!}</div>
                                        </div>
                                    @endif
                                    <div style="width:100%;background:#fff;border:1px solid rgba(11,60,109,.08);border-radius:8px;padding:6px 8px 4px;text-align:center;box-shadow:0 2px 8px rgba(11,60,109,.04);">
                                        @if($card->barcode && str_contains($card->barcode, '<svg'))
                                            <div style="width:100%;height:36px;">{!! $card->barcode !!}</div>
                                        @else
                                            <div style="height:36px;display:flex;align-items:center;justify-content:center;">
                                                <p style="font-size:10px;letter-spacing:1px;font-weight:700;color:#0a1e3a;">{{ $card->barcode }}</p>
                                            </div>
                                        @endif
                                        <div style="font-family:'Inter',sans-serif;font-weight:700;font-size:10px;color:#0a1e3a;letter-spacing:1.2px;margin-top:3px;">{{ $card->card_number }}</div>
                                    </div>
                                </div>

                            </div>

                            {{-- ===== FOOTER ===== --}}
                            <div style="height:50px;position:relative;z-index:10;background:linear-gradient(135deg,#E9EEF5,#dde3ed);display:flex;align-items:center;justify-content:center;gap:6px;padding:0 28px;border-top:1px solid rgba(11,60,109,.06);">
                                @if($website)
                                    <span style="font-family:'Inter',sans-serif;font-weight:500;font-size:9.5px;color:#5a6a82;letter-spacing:.3px;display:inline-flex;align-items:center;gap:5px;">
                                        <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="#0097A7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
                                        {{ $website }}
                                    </span>
                                    <span style="color:rgba(11,60,109,.2);font-size:10px;">|</span>
                                @endif
                                @if($email)
                                    <span style="font-family:'Inter',sans-serif;font-weight:500;font-size:9.5px;color:#5a6a82;letter-spacing:.3px;display:inline-flex;align-items:center;gap:5px;">
                                        <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="#0097A7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                        {{ $email }}
                                    </span>
                                    <span style="color:rgba(11,60,109,.2);font-size:10px;">|</span>
                                @endif
                                @if($phone)
                                    <span style="font-family:'Inter',sans-serif;font-weight:500;font-size:9.5px;color:#5a6a82;letter-spacing:.3px;display:inline-flex;align-items:center;gap:5px;">
                                        <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="#0097A7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                                        {{ $phone }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        </div>
                    </div>
                </div>

                {{-- Card Actions --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-semibold text-surface-900 dark:text-white">Card Actions</h3>
                    </div>
                    <div class="card-body space-y-3">
                        <button wire:click="downloadCard" class="btn-outline w-full justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download Card (PDF)
                        </button>
                        <button wire:click="reportLost" wire:confirm="Are you sure you want to report your card as lost? Please contact the library for a replacement."
                            class="btn-danger w-full justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            Report Card Lost
                        </button>
                    </div>
                </div>

                {{-- Expiry Warning --}}
                @if($card->expires_at && $card->expires_at->diffInDays(now()) <= 30)
                    <div class="card border-amber-300 dark:border-amber-600">
                        <div class="card-body">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-amber-800 dark:text-amber-200">Card Expiring Soon</p>
                                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                                        Your library card expires on {{ $card->expires_at->format('d M Y') }}.
                                        Please visit the library to renew your membership.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

        @else
            <div class="card">
                <div class="card-body text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-surface-300 dark:text-surface-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                    <h3 class="text-lg font-medium text-surface-900 dark:text-white mb-2">No Library Card</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400">
                        @if($member)
                            You don't have a library card yet. Please contact the library to get one issued.
                        @else
                            No member account found. Please register at the library to get started.
                        @endif
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>

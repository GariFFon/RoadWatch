<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RoadWatch') — RoadWatch</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    html{scroll-behavior:smooth}
    body{font-family:'Inter',sans-serif;background:#f2f4fb;color:#111827;-webkit-font-smoothing:antialiased;}

    /* ══ NAV ══════════════════════════════════════════ */
    .rw-nav{
        position:sticky;top:0;z-index:100;
        background:rgba(242,244,251,.88);
        backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
        border-bottom:1px solid rgba(255,255,255,.8);
        box-shadow:0 2px 16px rgba(15,14,26,.06);
    }
    .rw-nav-inner{
        max-width:1200px;margin:0 auto;padding:0 1.5rem;
        display:flex;align-items:center;justify-content:space-between;
        height:68px;gap:1rem;
    }
    .rw-logo{display:flex;align-items:center;gap:.75rem;text-decoration:none;}
    .rw-logo-icon{
        width:38px;height:38px;
        background:linear-gradient(135deg,#6366f1,#8b5cf6);
        border-radius:11px;
        display:flex;align-items:center;justify-content:center;
        color:#fff;font-weight:800;font-size:.9375rem;
        box-shadow:0 4px 14px rgba(99,102,241,.35);
        letter-spacing:-.03em;
    }
    .rw-logo-text{font-size:1.125rem;font-weight:800;color:#0f0e1a;letter-spacing:-.02em;}
    .rw-logo-tag{font-size:.625rem;font-weight:700;color:#8b5cf6;background:#f5f3ff;padding:.125rem .4rem;border-radius:5px;border:1px solid #ddd6fe;text-transform:uppercase;letter-spacing:.06em;margin-left:.125rem;}

    .rw-nav-links{display:flex;align-items:center;gap:.375rem;}
    .rw-nav-link{
        padding:.5rem .875rem;border-radius:.75rem;
        font-size:.875rem;font-weight:500;color:#6b7280;
        text-decoration:none;transition:all .18s;
    }
    .rw-nav-link:hover{background:rgba(99,102,241,.07);color:#4f46e5;}
    .rw-nav-link.active{background:#eef2ff;color:#4f46e5;font-weight:600;}

    .rw-report-btn{
        display:inline-flex;align-items:center;gap:.4375rem;
        background:linear-gradient(135deg,#6366f1,#8b5cf6);
        color:#fff;border:none;border-radius:.875rem;
        padding:.5625rem 1.125rem;
        font-size:.875rem;font-weight:700;
        text-decoration:none;cursor:pointer;
        transition:all .2s;
        box-shadow:0 4px 14px rgba(99,102,241,.3);
        letter-spacing:-.01em;
    }
    .rw-report-btn:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(99,102,241,.4);}
    .rw-report-btn svg{transition:transform .2s;}
    .rw-report-btn:hover svg{transform:rotate(90deg);}

    .rw-user-area{display:flex;align-items:center;gap:.75rem;}

    /* ══ USER DROPDOWN ════════════════════════════════ */
    @keyframes dropIn{from{opacity:0;transform:translateY(-8px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}
    #user-menu{animation:dropIn .18s cubic-bezier(.4,0,.2,1);}

    .rw-dd-item{
        display:flex;align-items:center;gap:.625rem;
        padding:.5625rem .875rem;border-radius:.75rem;
        font-size:.84375rem;font-weight:500;color:#374151;
        text-decoration:none;transition:all .15s;cursor:pointer;
        background:none;border:none;width:100%;font-family:inherit;text-align:left;
    }
    .rw-dd-item:hover{background:#f5f3ff;color:#4f46e5;}
    .rw-dd-item-icon{width:28px;height:28px;border-radius:.5rem;background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-size:.8125rem;flex-shrink:0;transition:background .15s;}
    .rw-dd-item:hover .rw-dd-item-icon{background:#ede9fe;}

    /* ══ MAIN ════════════════════════════════════════ */
    .rw-main{max-width:1200px;margin:0 auto;padding:2rem 1.5rem;}

    /* ══ FLASH MESSAGES ════════════════════════════════ */
    .rw-flash{
        display:flex;align-items:center;gap:.75rem;
        border-radius:1rem;padding:.875rem 1.125rem;
        margin-bottom:1.5rem;font-size:.875rem;font-weight:500;
        animation:fadeInDown .3s ease;
    }
    @keyframes fadeInDown{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}
    .rw-flash-success{background:#f0fdf4;border:1px solid #86efac;color:#166534;}
    .rw-flash-warning{background:#fffbeb;border:1px solid #fcd34d;color:#92400e;}
    .rw-flash-icon{width:32px;height:32px;border-radius:.625rem;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}
    .rw-flash-success .rw-flash-icon{background:#dcfce7;}
    .rw-flash-warning .rw-flash-icon{background:#fef9c3;}

    /* ══ RESPONSIVE ════════════════════════════════════ */
    @media(max-width:640px){
        .rw-nav-inner{padding:0 1rem;height:60px;}
        .rw-main{padding:1.25rem 1rem;}
        .rw-logo-tag{display:none;}
    }
    </style>
</head>
<body>

    <nav class="rw-nav">
        <div class="rw-nav-inner">

            {{-- Logo --}}
            <a href="{{ route('citizen.complaints.index') }}" class="rw-logo">
                <div class="rw-logo-icon">RW</div>
                <span class="rw-logo-text">RoadWatch</span>
                <span class="rw-logo-tag">Citizen</span>
            </a>

            {{-- Nav + report button --}}
            <div class="rw-nav-links">
                <a href="{{ route('citizen.complaints.index') }}"
                   class="rw-nav-link {{ request()->routeIs('citizen.complaints.index') ? 'active' : '' }}">
                    My Complaints
                </a>
                <a href="{{ route('citizen.complaints.create') }}" class="rw-report-btn" style="margin-left:.375rem;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Report Issue
                </a>
            </div>

            {{-- User avatar dropdown --}}
            <div class="rw-user-area" style="position:relative;">
                <button id="user-menu-btn" onclick="toggleUserMenu()"
                        style="display:flex;align-items:center;gap:.625rem;
                               background:rgba(255,255,255,.7);border:1.5px solid rgba(255,255,255,.9);
                               border-radius:9999px;padding:.3125rem .875rem .3125rem .3125rem;
                               cursor:pointer;font-family:inherit;transition:all .2s;
                               backdrop-filter:blur(10px);"
                        onmouseover="this.style.background='rgba(255,255,255,.95)';this.style.borderColor='#c4b5fd'"
                        onmouseout="this.style.background='rgba(255,255,255,.7)';this.style.borderColor='rgba(255,255,255,.9)'">
                    @if(auth()->user()->profile_photo_url)
                        <img src="{{ auth()->user()->profile_photo_url }}" alt="avatar"
                             style="width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                    @else
                        <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);
                                    display:flex;align-items:center;justify-content:center;
                                    color:#fff;font-size:.78rem;font-weight:700;flex-shrink:0;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif
                    <span style="font-size:.84375rem;font-weight:600;color:#0f0e1a;
                                 max-width:110px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ explode(' ', auth()->user()->name)[0] }}
                    </span>
                    <svg id="chevron" style="width:12px;height:12px;color:#9ca3af;flex-shrink:0;transition:transform .2s;"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Dropdown --}}
                <div id="user-menu"
                     style="display:none;position:absolute;top:calc(100% + 12px);right:0;width:240px;
                            background:#fff;border:1px solid #ebe9f6;border-radius:1.25rem;
                            box-shadow:0 20px 60px rgba(15,14,26,.14);z-index:500;overflow:hidden;">

                    {{-- Header --}}
                    <div style="padding:1rem;border-bottom:1px solid #f3f4f6;display:flex;gap:.75rem;align-items:center;background:linear-gradient(135deg,#fafbff,#f5f3ff);">
                        @if(auth()->user()->profile_photo_url)
                            <img src="{{ auth()->user()->profile_photo_url }}" alt="avatar"
                                 style="width:42px;height:42px;border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid #e0e7ff;">
                        @else
                            <div style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);
                                        display:flex;align-items:center;justify-content:center;
                                        color:#fff;font-size:1rem;font-weight:700;flex-shrink:0;
                                        box-shadow:0 3px 10px rgba(99,102,241,.3);">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        @endif
                        <div style="min-width:0;">
                            <p style="font-size:.875rem;font-weight:700;color:#0f0e1a;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->name }}</p>
                            <p style="font-size:.72rem;color:#9ca3af;margin:.1rem 0 .375rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->email }}</p>
                            <span style="font-size:.625rem;font-weight:700;background:#eef2ff;color:#4f46e5;padding:.125rem .4rem;border-radius:5px;text-transform:uppercase;letter-spacing:.05em;">
                                {{ ucfirst(auth()->user()->getRoleNames()->first() ?? 'citizen') }}
                            </span>
                        </div>
                    </div>

                    {{-- Links --}}
                    <div style="padding:.5rem;">
                        <a href="{{ route('citizen.profile') }}" class="rw-dd-item">
                            <span class="rw-dd-item-icon">👤</span> My Profile
                        </a>
                        <a href="{{ route('citizen.complaints.index') }}" class="rw-dd-item">
                            <span class="rw-dd-item-icon">📋</span> My Complaints
                        </a>
                        <a href="{{ route('citizen.complaints.create') }}" class="rw-dd-item">
                            <span class="rw-dd-item-icon">🚨</span> Report Issue
                        </a>
                    </div>

                    {{-- Sign out --}}
                    <div style="padding:.5rem;border-top:1px solid #f3f4f6;">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rw-dd-item" style="color:#dc2626;">
                                <span class="rw-dd-item-icon" style="background:#fef2f2;">🚪</span> Sign Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </nav>

    <script>
    function toggleUserMenu() {
        const menu    = document.getElementById('user-menu');
        const chevron = document.getElementById('chevron');
        const open    = menu.style.display === 'block';
        menu.style.display = open ? 'none' : 'block';
        chevron.style.transform = open ? '' : 'rotate(180deg)';
    }
    document.addEventListener('click', e => {
        const btn = document.getElementById('user-menu-btn');
        if (btn && !btn.contains(e.target) && !document.getElementById('user-menu').contains(e.target)) {
            document.getElementById('user-menu').style.display = 'none';
            document.getElementById('chevron').style.transform = '';
        }
    });
    </script>

    {{-- Flash messages --}}
    <div class="rw-main" style="padding-bottom:0;padding-top:1.25rem;">
        @if(session('success'))
            <div class="rw-flash rw-flash-success">
                <span class="rw-flash-icon">✅</span>
                {{ session('success') }}
            </div>
        @endif
        @if(session('warning'))
            <div class="rw-flash rw-flash-warning">
                <span class="rw-flash-icon">⚠️</span>
                {{ session('warning') }}
            </div>
        @endif
    </div>

    <main class="rw-main">
        @yield('content')
    </main>

    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>

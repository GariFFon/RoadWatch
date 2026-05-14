<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RoadWatch')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; margin: 0; }
        .rw-nav {
            position: sticky; top: 0; z-index: 100;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .rw-nav-inner {
            max-width: 1200px; margin: 0 auto; padding: 0 1.5rem;
            display: flex; align-items: center; justify-content: space-between;
            height: 64px;
        }
        .rw-logo {
            display: flex; align-items: center; gap: 0.625rem;
            text-decoration: none;
        }
        .rw-logo-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 800; font-size: 0.875rem;
            box-shadow: 0 2px 8px rgba(79,70,229,0.3);
        }
        .rw-logo-text { font-size: 1.125rem; font-weight: 700; color: #111827; }
        .rw-nav-links { display: flex; align-items: center; gap: 0.25rem; }
        .rw-nav-link {
            padding: 0.5rem 0.875rem;
            border-radius: 0.5rem;
            font-size: 0.875rem; font-weight: 500;
            color: #6b7280; text-decoration: none;
            transition: background 0.15s, color 0.15s;
        }
        .rw-nav-link:hover { background: #f3f4f6; color: #111827; }
        .rw-nav-link.active { background: #eef2ff; color: #4f46e5; font-weight: 600; }
        .rw-report-btn {
            display: inline-flex; align-items: center; gap: 0.375rem;
            background: #4f46e5; color: #fff;
            border: none; border-radius: 0.625rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem; font-weight: 600;
            text-decoration: none; cursor: pointer;
            transition: background 0.15s, transform 0.1s;
            box-shadow: 0 2px 8px rgba(79,70,229,0.25);
        }
        .rw-report-btn:hover { background: #4338ca; transform: translateY(-1px); }
        .rw-user-area { display: flex; align-items: center; gap: 0.75rem; }
        .rw-main { max-width: 1200px; margin: 0 auto; padding: 2rem 1.5rem; }
        .rw-flash-success {
            background: #f0fdf4; border: 1px solid #86efac;
            border-radius: 0.75rem; padding: 0.875rem 1rem;
            color: #166534; font-size: 0.875rem; margin-bottom: 1.5rem;
        }
        .rw-flash-warning {
            background: #fffbeb; border: 1px solid #fcd34d;
            border-radius: 0.75rem; padding: 0.875rem 1rem;
            color: #92400e; font-size: 0.875rem; margin-bottom: 1.5rem;
        }
        .rw-dd-item:hover { background: #f9fafb !important; }
    </style>
</head>
<body>

    <nav class="rw-nav">
        <div class="rw-nav-inner">

            {{-- Logo --}}
            <a href="{{ route('citizen.complaints.index') }}" class="rw-logo">
                <div class="rw-logo-icon">RW</div>
                <span class="rw-logo-text">RoadWatch</span>
            </a>

            {{-- Nav links + Report button --}}
            <div class="rw-nav-links">
                <a href="{{ route('citizen.complaints.index') }}"
                   class="rw-nav-link {{ request()->routeIs('citizen.complaints.index') ? 'active' : '' }}">
                    My Complaints
                </a>
                <a href="{{ route('citizen.complaints.create') }}" class="rw-report-btn" style="margin-left: 0.5rem;">
                    + Report Issue
                </a>
            </div>

            {{-- User avatar dropdown --}}
            <div class="rw-user-area" style="position:relative;">
                <button id="user-menu-btn" onclick="toggleUserMenu()"
                        style="display:flex;align-items:center;gap:0.625rem;background:#fff;
                               border:1.5px solid #e5e7eb;border-radius:2rem;
                               padding:0.3rem 0.875rem 0.3rem 0.3rem;cursor:pointer;
                               font-family:inherit;transition:border-color .2s,box-shadow .2s;"
                        onmouseover="this.style.borderColor='#a5b4fc'"
                        onmouseout="this.style.borderColor='#e5e7eb'">
                    @if(auth()->user()->profile_photo_url)
                        <img src="{{ auth()->user()->profile_photo_url }}" alt="avatar"
                             style="width:32px;height:32px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                    @else
                        <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#7c3aed);
                                    display:flex;align-items:center;justify-content:center;
                                    color:#fff;font-size:.875rem;font-weight:700;flex-shrink:0;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif
                    <span style="font-size:.875rem;font-weight:600;color:#111827;
                                 max-width:110px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ explode(' ', auth()->user()->name)[0] }}
                    </span>
                    <svg id="chevron" style="width:14px;height:14px;color:#9ca3af;flex-shrink:0;transition:transform .2s;"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Dropdown menu --}}
                <style>
                    @keyframes dropIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}
                </style>
                <div id="user-menu"
                     style="display:none;position:absolute;top:calc(100% + 12px);right:0;width:230px;
                            background:#fff;border:1.5px solid #f1f1f1;border-radius:1rem;
                            box-shadow:0 16px 48px rgba(0,0,0,.12);z-index:500;overflow:hidden;
                            animation:dropIn .15s ease;">

                    {{-- Header --}}
                    <div style="padding:1rem 1rem .875rem;border-bottom:1px solid #f3f4f6;display:flex;gap:.75rem;align-items:center;">
                        @if(auth()->user()->profile_photo_url)
                            <img src="{{ auth()->user()->profile_photo_url }}" alt="avatar"
                                 style="width:40px;height:40px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                        @else
                            <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#7c3aed);
                                        display:flex;align-items:center;justify-content:center;
                                        color:#fff;font-size:1rem;font-weight:700;flex-shrink:0;">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        @endif
                        <div style="min-width:0;">
                            <p style="font-size:.875rem;font-weight:700;color:#111827;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->name }}</p>
                            <p style="font-size:.75rem;color:#9ca3af;margin:.1rem 0 .375rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->email }}</p>
                            <span style="font-size:.7rem;font-weight:700;background:#eef2ff;color:#4f46e5;padding:.15rem .5rem;border-radius:9999px;">
                                {{ ucfirst(auth()->user()->getRoleNames()->first() ?? 'citizen') }}
                            </span>
                        </div>
                    </div>

                    {{-- Links --}}
                    <div style="padding:.5rem;">
                        <a href="{{ route('citizen.profile') }}" class="rw-dd-item"
                           style="display:flex;align-items:center;gap:.625rem;padding:.625rem .75rem;border-radius:.625rem;
                                  text-decoration:none;font-size:.875rem;color:#374151;font-weight:500;transition:background .15s;">
                            👤 My Profile
                        </a>
                        <a href="{{ route('citizen.complaints.index') }}" class="rw-dd-item"
                           style="display:flex;align-items:center;gap:.625rem;padding:.625rem .75rem;border-radius:.625rem;
                                  text-decoration:none;font-size:.875rem;color:#374151;font-weight:500;transition:background .15s;">
                            📋 My Complaints
                        </a>
                        <a href="{{ route('citizen.complaints.create') }}" class="rw-dd-item"
                           style="display:flex;align-items:center;gap:.625rem;padding:.625rem .75rem;border-radius:.625rem;
                                  text-decoration:none;font-size:.875rem;color:#374151;font-weight:500;transition:background .15s;">
                            🚨 Report Issue
                        </a>
                    </div>

                    {{-- Sign out --}}
                    <div style="padding:.5rem;border-top:1px solid #f3f4f6;">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rw-dd-item"
                                    style="display:flex;align-items:center;gap:.625rem;width:100%;padding:.625rem .75rem;border-radius:.625rem;
                                           font-size:.875rem;font-weight:500;color:#ef4444;background:none;border:none;
                                           cursor:pointer;font-family:inherit;transition:background .15s;">
                                🚪 Sign Out
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
    // Close on outside click
    document.addEventListener('click', e => {
        const btn = document.getElementById('user-menu-btn');
        if (btn && !btn.contains(e.target)) {
            document.getElementById('user-menu').style.display = 'none';
            document.getElementById('chevron').style.transform = '';
        }
    });
    </script>

    {{-- Flash messages --}}
    <div class="rw-main" style="padding-bottom: 0; padding-top: 1.25rem;">
        @if(session('success'))
            <div class="rw-flash-success">✅ {{ session('success') }}</div>
        @endif
        @if(session('warning'))
            <div class="rw-flash-warning">⚠️ {{ session('warning') }}</div>
        @endif
    </div>

    <main class="rw-main">
        @yield('content')
    </main>

</body>
</html>

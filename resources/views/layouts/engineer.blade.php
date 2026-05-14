<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Engineer') — RoadWatch</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#f5f6fa;color:#111827;-webkit-font-smoothing:antialiased;display:flex;min-height:100vh;}

/* ── Sidebar ── */
.eng-sidebar{width:230px;flex-shrink:0;background:#0f172a;min-height:100vh;display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:50;}
.eng-logo{display:flex;align-items:center;gap:.625rem;padding:1.375rem 1.25rem;border-bottom:1px solid rgba(255,255,255,.07);}
.eng-logo-icon{width:34px;height:34px;background:linear-gradient(135deg,#0ea5e9,#6366f1);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}
.eng-logo-text{font-size:1rem;font-weight:800;color:#fff;}
.eng-logo-badge{font-size:.6rem;font-weight:700;background:#0ea5e9;color:#fff;padding:.15rem .4rem;border-radius:4px;margin-left:.25rem;}
.eng-nav{flex:1;padding:1rem .75rem;display:flex;flex-direction:column;gap:.25rem;}
.eng-nav-label{font-size:.65rem;font-weight:700;color:rgba(255,255,255,.3);text-transform:uppercase;letter-spacing:.08em;padding:.75rem .5rem .25rem;}
.eng-nav-link{display:flex;align-items:center;gap:.625rem;padding:.625rem .875rem;border-radius:.625rem;font-size:.875rem;font-weight:500;color:rgba(255,255,255,.55);text-decoration:none;transition:background .15s,color .15s;}
.eng-nav-link:hover{background:rgba(255,255,255,.07);color:#fff;}
.eng-nav-link.active{background:rgba(14,165,233,.25);color:#fff;font-weight:600;}
.eng-nav-link .icon{font-size:1rem;width:1.25rem;text-align:center;}
.eng-footer{padding:1rem .75rem;border-top:1px solid rgba(255,255,255,.07);}
.eng-user-row{display:flex;align-items:center;gap:.625rem;}
.eng-user-avatar{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#0ea5e9,#6366f1);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.8rem;font-weight:700;flex-shrink:0;}
.eng-signout{margin-left:auto;background:none;border:none;color:rgba(255,255,255,.3);cursor:pointer;font-size:.75rem;padding:.25rem;transition:color .15s;}
.eng-signout:hover{color:#ef4444;}

/* ── Main ── */
.eng-main{margin-left:230px;flex:1;display:flex;flex-direction:column;min-height:100vh;}
.eng-topbar{background:#fff;border-bottom:1px solid #e5e7eb;padding:0 2rem;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:40;}
.eng-topbar-title{font-size:1rem;font-weight:700;color:#111827;}
.eng-content{padding:2rem;flex:1;}

/* ── Utilities ── */
.eng-card{background:#fff;border:1px solid #e5e7eb;border-radius:1rem;box-shadow:0 1px 4px rgba(0,0,0,.05);}
.eng-badge{display:inline-flex;align-items:center;font-size:.7rem;font-weight:700;padding:.2rem .625rem;border-radius:9999px;}
.eng-btn{display:inline-flex;align-items:center;gap:.375rem;border-radius:.625rem;padding:.5rem 1rem;font-size:.8125rem;font-weight:600;cursor:pointer;border:none;font-family:inherit;transition:all .15s;}
.eng-btn-primary{background:linear-gradient(135deg,#0ea5e9,#6366f1);color:#fff;box-shadow:0 2px 8px rgba(14,165,233,.3);}
.eng-btn-primary:hover{opacity:.9;transform:translateY(-1px);}
.eng-btn-outline{background:#fff;color:#374151;border:1.5px solid #e5e7eb;}
.eng-btn-outline:hover{border-color:#93c5fd;color:#0ea5e9;}
.eng-btn-sm{padding:.3rem .7rem;font-size:.78rem;}
.eng-btn:disabled{opacity:.5;cursor:not-allowed;transform:none;}
.eng-input{border:1.5px solid #e5e7eb;border-radius:.5rem;padding:.45rem .75rem;font-size:.875rem;outline:none;font-family:inherit;transition:border-color .15s;background:#fff;color:#111827;}
.eng-input:focus{border-color:#0ea5e9;}
.eng-select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='%236b7280' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right .5rem center;background-size:1rem;padding-right:2rem;}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
@keyframes spin{to{transform:rotate(360deg)}}

/* ── Modal ── */
.eng-modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:200;align-items:center;justify-content:center;}
.eng-modal-bg.open{display:flex;}
.eng-modal{background:#fff;border-radius:1.25rem;width:480px;max-width:calc(100vw - 2rem);padding:1.75rem;box-shadow:0 24px 64px rgba(0,0,0,.2);}
</style>
</head>
<body>

{{-- ── Sidebar ── --}}
<aside class="eng-sidebar">
    <div class="eng-logo">
        <div class="eng-logo-icon">🔧</div>
        <span class="eng-logo-text">RoadWatch</span>
        <span class="eng-logo-badge">Engineer</span>
    </div>

    <nav class="eng-nav">
        <div class="eng-nav-label">My Work</div>
        <a href="{{ route('engineer.complaints.index') }}"
           class="eng-nav-link {{ request()->routeIs('engineer.complaints.index') ? 'active' : '' }}">
            <span class="icon">📋</span> My Assignments
        </a>
        <a href="{{ route('engineer.complaints.completed') }}"
           class="eng-nav-link {{ request()->routeIs('engineer.complaints.completed') ? 'active' : '' }}"
           style="{{ request()->routeIs('engineer.complaints.completed') ? '' : '' }}">
            <span class="icon">🏆</span> Completed Tasks
            {{-- Live badge showing verified count --}}
            <span id="completed-badge" style="margin-left:auto;min-width:20px;height:20px;background:rgba(16,185,129,.2);
                  color:#059669;font-size:.6rem;font-weight:700;border-radius:9999px;
                  display:inline-flex;align-items:center;justify-content:center;padding:0 .35rem;"></span>
        </a>

        <div class="eng-nav-label">System</div>
        <a href="{{ url('/') }}" class="eng-nav-link">
            <span class="icon">🌐</span> Public Site
        </a>
    </nav>

    <div class="eng-footer">
        <div class="eng-user-row">
            @if(auth()->user()->profile_photo_url)
                <img src="{{ auth()->user()->profile_photo_url }}" alt="avatar"
                     style="width:32px;height:32px;border-radius:50%;object-fit:cover;flex-shrink:0;">
            @else
                <div class="eng-user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            @endif
            <div style="flex:1;min-width:0;">
                <div style="font-size:.8125rem;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->name }}</div>
                <div style="font-size:.7rem;color:rgba(255,255,255,.4);">Engineer</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="eng-signout" title="Sign out">
                    <svg style="width:1.125rem;height:1.125rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- ── Main ── --}}
<div class="eng-main">
    <header class="eng-topbar">
        <span class="eng-topbar-title">@yield('page-title', 'Engineer Panel')</span>

        {{-- Topbar user dropdown --}}
        <style>
            @keyframes engDropIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}
            #eng-user-menu{animation:engDropIn .15s ease;}
        </style>
        <div style="position:relative;">
            <button onclick="document.getElementById('eng-user-menu').style.display=document.getElementById('eng-user-menu').style.display==='block'?'none':'block'"
                    style="display:flex;align-items:center;gap:.5rem;background:#f9fafb;border:1.5px solid #e5e7eb;
                           border-radius:9999px;padding:.3rem .75rem .3rem .3rem;cursor:pointer;
                           transition:border-color .15s;font-family:inherit;"
                    onmouseover="this.style.borderColor='#7dd3fc'" onmouseout="this.style.borderColor='#e5e7eb'">
                @if(auth()->user()->profile_photo_url)
                    <img src="{{ auth()->user()->profile_photo_url }}" alt="avatar"
                         style="width:28px;height:28px;border-radius:50%;object-fit:cover;">
                @else
                    <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#0ea5e9,#6366f1);
                                display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:700;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
                <span style="font-size:.8125rem;font-weight:600;color:#374151;">{{ auth()->user()->name }}</span>
                <svg style="width:12px;height:12px;color:#9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div id="eng-user-menu" style="display:none;position:absolute;top:calc(100% + 10px);right:0;width:220px;
                 background:#fff;border:1.5px solid #f1f1f1;border-radius:1rem;
                 box-shadow:0 16px 48px rgba(0,0,0,.12);z-index:500;overflow:hidden;">

                {{-- Profile header --}}
                <div style="padding:.875rem 1rem;border-bottom:1px solid #f3f4f6;display:flex;gap:.625rem;align-items:center;">
                    @if(auth()->user()->profile_photo_url)
                        <img src="{{ auth()->user()->profile_photo_url }}" alt="avatar"
                             style="width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                    @else
                        <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#0ea5e9,#6366f1);
                                    display:flex;align-items:center;justify-content:center;
                                    color:#fff;font-size:.875rem;font-weight:700;flex-shrink:0;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif
                    <div style="min-width:0;">
                        <div style="font-size:.8125rem;font-weight:700;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->name }}</div>
                        <div style="font-size:.72rem;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->email }}</div>
                        <span style="font-size:.65rem;font-weight:700;background:#e0f2fe;color:#0284c7;padding:.1rem .4rem;border-radius:4px;">Engineer</span>
                    </div>
                </div>

                {{-- Sign Out --}}
                <div style="padding:.5rem;">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                style="width:100%;display:flex;align-items:center;gap:.5rem;padding:.5rem .75rem;
                                       background:none;border:none;border-radius:.5rem;font-size:.8125rem;
                                       font-weight:600;color:#dc2626;cursor:pointer;font-family:inherit;
                                       transition:background .15s;text-align:left;"
                                onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='none'">
                            🚪 Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <script>
        document.addEventListener('click', function(e) {
            const menu = document.getElementById('eng-user-menu');
            if (menu && !e.target.closest('[onclick*="eng-user-menu"]') && !menu.contains(e.target)) {
                menu.style.display = 'none';
            }
        });
    </script>


    <main class="eng-content">
        @yield('content')
    </main>
</div>

<script>
// Load verified count badge for sidebar
(async () => {
    try {
        const r = await fetch('/api/v1/engineer/complaints?completed=1&per_page=1');
        const d = await r.json();
        const count = d.meta?.total ?? 0;
        const el = document.getElementById('completed-badge');
        if (el && count > 0) el.textContent = count;
    } catch(_) {}
})();
</script>
</body>
</html>

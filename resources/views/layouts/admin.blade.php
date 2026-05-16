<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Admin') — RoadWatch</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@vite(['resources/css/app.css'])
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#f5f6fa;color:#111827;-webkit-font-smoothing:antialiased;display:flex;min-height:100vh;}

/* ── Sidebar ── */
.adm-sidebar{width:240px;flex-shrink:0;background:#111827;min-height:100vh;display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:200;transition:transform .25s ease;}
.adm-logo{display:flex;align-items:center;gap:.625rem;padding:1.375rem 1.25rem;border-bottom:1px solid rgba(255,255,255,.08);}
.adm-logo-icon{width:34px;height:34px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}
.adm-logo-text{font-size:1rem;font-weight:800;color:#fff;}
.adm-logo-badge{font-size:.6rem;font-weight:700;background:#4f46e5;color:#fff;padding:.15rem .4rem;border-radius:4px;margin-left:.25rem;}
.adm-nav{flex:1;padding:1rem .75rem;display:flex;flex-direction:column;gap:.25rem;overflow-y:auto;}
.adm-nav-label{font-size:.65rem;font-weight:700;color:rgba(255,255,255,.3);text-transform:uppercase;letter-spacing:.08em;padding:.75rem .5rem .25rem;}
.adm-nav-link{display:flex;align-items:center;gap:.625rem;padding:.625rem .875rem;border-radius:.625rem;font-size:.875rem;font-weight:500;color:rgba(255,255,255,.6);text-decoration:none;transition:background .15s,color .15s;}
.adm-nav-link:hover{background:rgba(255,255,255,.07);color:#fff;}
.adm-nav-link.active{background:rgba(79,70,229,.35);color:#fff;font-weight:600;}
.adm-nav-link .icon{font-size:1rem;width:1.25rem;text-align:center;}
.adm-footer{padding:1rem .75rem;border-top:1px solid rgba(255,255,255,.08);flex-shrink:0;}
.adm-user-row{display:flex;align-items:center;gap:.625rem;}
.adm-user-avatar{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#7c3aed);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.8rem;font-weight:700;flex-shrink:0;}
.adm-user-name{font-size:.8125rem;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.adm-user-role{font-size:.7rem;color:rgba(255,255,255,.4);}
.adm-signout{margin-left:auto;background:none;border:none;color:rgba(255,255,255,.35);cursor:pointer;font-size:.75rem;padding:.25rem;border-radius:.375rem;transition:color .15s;}
.adm-signout:hover{color:#ef4444;}

/* ── Sidebar overlay (mobile) ── */
.adm-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:190;backdrop-filter:blur(2px);}
.adm-overlay.open{display:block;}

/* ── Main ── */
.adm-main{margin-left:240px;flex:1;display:flex;flex-direction:column;min-height:100vh;}
.adm-topbar{background:#fff;border-bottom:1px solid #e5e7eb;padding:0 1.5rem;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:40;gap:.75rem;}
.adm-topbar-left{display:flex;align-items:center;gap:.75rem;}
.adm-topbar-title{font-size:1rem;font-weight:700;color:#111827;}
.adm-content{padding:1.5rem;flex:1;}

/* ── Hamburger ── */
.adm-hamburger{display:none;flex-direction:column;justify-content:center;align-items:center;width:36px;height:36px;gap:5px;background:none;border:1.5px solid #e5e7eb;border-radius:.5rem;cursor:pointer;flex-shrink:0;padding:6px;transition:border-color .2s;}
.adm-hamburger:hover{border-color:#4f46e5;}
.adm-hamburger span{display:block;width:16px;height:2px;background:#374151;border-radius:2px;transition:all .25s;}
.adm-hamburger.open span:nth-child(1){transform:translateY(7px) rotate(45deg);}
.adm-hamburger.open span:nth-child(2){opacity:0;transform:scaleX(0);}
.adm-hamburger.open span:nth-child(3){transform:translateY(-7px) rotate(-45deg);}

/* ── Utilities ── */
.adm-card{background:#fff;border:1px solid #e5e7eb;border-radius:1rem;box-shadow:0 1px 4px rgba(0,0,0,.05);}
.adm-badge{display:inline-flex;align-items:center;font-size:.7rem;font-weight:700;padding:.2rem .625rem;border-radius:9999px;}
.adm-btn{display:inline-flex;align-items:center;gap:.375rem;border-radius:.625rem;padding:.475rem .9rem;font-size:.8125rem;font-weight:600;cursor:pointer;border:none;font-family:inherit;transition:all .15s;}
.adm-btn-primary{background:#4f46e5;color:#fff;box-shadow:0 1px 6px rgba(79,70,229,.3);}
.adm-btn-primary:hover{background:#4338ca;}
.adm-btn-outline{background:#fff;color:#374151;border:1.5px solid #e5e7eb;}
.adm-btn-outline:hover{border-color:#a5b4fc;color:#4f46e5;}
.adm-btn-sm{padding:.3rem .7rem;font-size:.78rem;}
.adm-input{border:1.5px solid #e5e7eb;border-radius:.5rem;padding:.45rem .75rem;font-size:.875rem;outline:none;font-family:inherit;transition:border-color .15s;background:#fff;color:#111827;width:100%;}
.adm-input:focus{border-color:#4f46e5;}
.adm-select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='%236b7280' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right .5rem center;background-size:1rem;padding-right:2rem;}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}

/* ── Modal ── */
.adm-modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:300;align-items:center;justify-content:center;padding:1rem;}
.adm-modal-bg.open{display:flex;}
.adm-modal{background:#fff;border-radius:1.25rem;width:440px;max-width:100%;padding:1.75rem;box-shadow:0 20px 60px rgba(0,0,0,.2);}
.adm-modal-title{font-size:1rem;font-weight:700;color:#111827;margin-bottom:1.25rem;}

/* ── Responsive ── */
@media(max-width:768px){
  .adm-sidebar{transform:translateX(-100%);}
  .adm-sidebar.open{transform:translateX(0);}
  .adm-main{margin-left:0;}
  .adm-hamburger{display:flex;}
  .adm-content{padding:1rem;}
  .adm-topbar{padding:0 1rem;}
}
</style>
</head>
<body>

{{-- ── Sidebar ── --}}
<aside class="adm-sidebar">
    <div class="adm-logo">
        <div class="adm-logo-icon">🛣️</div>
        <span class="adm-logo-text">RoadWatch</span>
        <span class="adm-logo-badge">Admin</span>
    </div>

    <nav class="adm-nav">
        <div class="adm-nav-label">Overview</div>
        <a href="{{ route('admin.dashboard') }}" class="adm-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <span class="icon">📊</span> Dashboard
        </a>

        <div class="adm-nav-label">Manage</div>
        <a href="{{ route('admin.complaints.index') }}" class="adm-nav-link {{ request()->routeIs('admin.complaints.*') ? 'active' : '' }}">
            <span class="icon">📋</span> Complaints
        </a>
        <a href="{{ route('admin.users.index') }}" class="adm-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <span class="icon">👥</span> Users
        </a>
        <a href="{{ route('admin.categories.index') }}" class="adm-nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
            <span class="icon">🏷️</span> Categories
        </a>

        <div class="adm-nav-label">System</div>
        <a href="{{ url('/') }}" class="adm-nav-link">
            <span class="icon">🌐</span> Public Site
        </a>
    </nav>

    <div class="adm-footer">
        <div class="adm-user-row">
            <div class="adm-user-avatar" id="adm-sidebar-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div style="flex:1;min-width:0;">
                <div class="adm-user-name">{{ auth()->user()->name }}</div>
                <div class="adm-user-role">Administrator</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="adm-signout" title="Sign out">
                    <svg style="width:1.125rem;height:1.125rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- ── Sidebar overlay (mobile) ── --}}
<div class="adm-overlay" id="adm-overlay" onclick="admCloseSidebar()"></div>

{{-- ── Main content area ── --}}
<div class="adm-main">
    <header class="adm-topbar">
        <div class="adm-topbar-left">
            {{-- Hamburger (mobile only) --}}
            <button class="adm-hamburger" id="adm-hamburger" aria-label="Toggle sidebar" onclick="admToggleSidebar()">
                <span></span><span></span><span></span>
            </button>
            <span class="adm-topbar-title">@yield('page-title', 'Admin Panel')</span>
        </div>

        {{-- Topbar user dropdown --}}
        <style>
            @keyframes admDropIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}
            #adm-user-menu{animation:admDropIn .15s ease;}
        </style>
        <div style="position:relative;">
            <button onclick="document.getElementById('adm-user-menu').style.display=document.getElementById('adm-user-menu').style.display==='block'?'none':'block'"
                    style="display:flex;align-items:center;gap:.5rem;background:#f9fafb;border:1.5px solid #e5e7eb;
                           border-radius:9999px;padding:.3rem .75rem .3rem .3rem;cursor:pointer;
                           transition:border-color .15s;font-family:inherit;"
                    onmouseover="this.style.borderColor='#a5b4fc'" onmouseout="this.style.borderColor='#e5e7eb'">
                @if(auth()->user()->profile_photo_url)
                    <img src="{{ auth()->user()->profile_photo_url }}" alt="avatar"
                         style="width:28px;height:28px;border-radius:50%;object-fit:cover;">
                @else
                    <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#7c3aed);
                                display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:700;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
                <span style="font-size:.8125rem;font-weight:600;color:#374151;">{{ auth()->user()->name }}</span>
                <svg style="width:12px;height:12px;color:#9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div id="adm-user-menu" style="display:none;position:absolute;top:calc(100% + 10px);right:0;width:220px;
                 background:#fff;border:1.5px solid #f1f1f1;border-radius:1rem;
                 box-shadow:0 16px 48px rgba(0,0,0,.12);z-index:500;overflow:hidden;">

                {{-- Profile header --}}
                <div style="padding:.875rem 1rem;border-bottom:1px solid #f3f4f6;display:flex;gap:.625rem;align-items:center;">
                    @if(auth()->user()->profile_photo_url)
                        <img src="{{ auth()->user()->profile_photo_url }}" alt="avatar"
                             style="width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                    @else
                        <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#7c3aed);
                                    display:flex;align-items:center;justify-content:center;
                                    color:#fff;font-size:.875rem;font-weight:700;flex-shrink:0;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif
                    <div style="min-width:0;">
                        <div style="font-size:.8125rem;font-weight:700;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->name }}</div>
                        <div style="font-size:.72rem;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->email }}</div>
                        <span style="font-size:.65rem;font-weight:700;background:#eef2ff;color:#4338ca;padding:.1rem .4rem;border-radius:4px;">Admin</span>
                    </div>
                </div>

                {{-- My Profile + Sign Out --}}
                <div style="padding:.5rem;">
                    <a href="{{ route('admin.profile') }}"
                       style="width:100%;display:flex;align-items:center;gap:.5rem;padding:.5rem .75rem;
                              background:none;border-radius:.5rem;font-size:.8125rem;
                              font-weight:600;color:#374151;cursor:pointer;text-decoration:none;
                              transition:background .15s;"
                       onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='none'">
                        👤 My Profile
                    </a>
                    <div style="height:1px;background:#f3f4f6;margin:.25rem 0;"></div>
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
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const menu = document.getElementById('adm-user-menu');
            if (menu && !e.target.closest('[onclick*="adm-user-menu"]') && !menu.contains(e.target)) {
                menu.style.display = 'none';
            }
        });

        // ── Mobile sidebar toggle ──────────────────────────────────────────
        function admToggleSidebar() {
            const sidebar  = document.querySelector('.adm-sidebar');
            const overlay  = document.getElementById('adm-overlay');
            const hamburger = document.getElementById('adm-hamburger');
            const isOpen   = sidebar.classList.contains('open');
            if (isOpen) {
                sidebar.classList.remove('open');
                overlay.classList.remove('open');
                hamburger.classList.remove('open');
            } else {
                sidebar.classList.add('open');
                overlay.classList.add('open');
                hamburger.classList.add('open');
            }
        }
        function admCloseSidebar() {
            document.querySelector('.adm-sidebar').classList.remove('open');
            document.getElementById('adm-overlay').classList.remove('open');
            document.getElementById('adm-hamburger').classList.remove('open');
        }
        // Close sidebar when a nav link is clicked (mobile UX)
        document.querySelectorAll('.adm-nav-link').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) admCloseSidebar();
            });
        });
    </script>


    <main class="adm-content">
        @yield('content')
    </main>
</div>

    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>

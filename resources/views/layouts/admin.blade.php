<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Admin') — RoadWatch</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
@vite(['resources/css/app.css'])
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{overflow-x:hidden;scroll-behavior:smooth}
body{font-family:'Inter',sans-serif;background:#f0f2f8;color:#111827;-webkit-font-smoothing:antialiased;display:flex;min-height:100vh;overflow-x:hidden}

/* ══════════════════════════════════════════
   SIDEBAR
══════════════════════════════════════════ */
.adm-sidebar{
  width:240px;flex-shrink:0;
  background:linear-gradient(180deg,#13111c 0%,#1a1730 50%,#13111c 100%);
  min-height:100vh;display:flex;flex-direction:column;
  position:fixed;top:0;left:0;bottom:0;z-index:200;
  transition:transform .3s cubic-bezier(.4,0,.2,1);
  border-right:1px solid rgba(255,255,255,.05);
}

/* Subtle top accent line */
.adm-sidebar::before{
  content:'';position:absolute;top:0;left:0;right:0;height:3px;
  background:linear-gradient(90deg,#6366f1,#8b5cf6,#a78bfa);
  border-radius:0 0 4px 4px;
}

.adm-logo{
  display:flex;align-items:center;gap:.75rem;
  padding:1.5rem 1.25rem 1.25rem;
  border-bottom:1px solid rgba(255,255,255,.06);
}
.adm-logo-icon{
  width:36px;height:36px;
  background:linear-gradient(135deg,#6366f1,#8b5cf6);
  border-radius:10px;display:flex;align-items:center;justify-content:center;
  font-size:1.1rem;flex-shrink:0;
  box-shadow:0 4px 15px rgba(99,102,241,.4);
}
.adm-logo-text{font-size:1.0625rem;font-weight:800;color:#fff;letter-spacing:-.01em;}
.adm-logo-badge{
  font-size:.58rem;font-weight:700;
  background:rgba(99,102,241,.25);color:#a5b4fc;
  padding:.15rem .45rem;border-radius:5px;margin-left:.25rem;
  border:1px solid rgba(99,102,241,.3);letter-spacing:.05em;text-transform:uppercase;
}

.adm-nav{flex:1;padding:1rem .875rem;display:flex;flex-direction:column;gap:.125rem;overflow-y:auto;}
.adm-nav::-webkit-scrollbar{width:3px}
.adm-nav::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:3px}

.adm-nav-label{
  font-size:.625rem;font-weight:700;color:rgba(255,255,255,.25);
  text-transform:uppercase;letter-spacing:.1em;
  padding:.875rem .625rem .375rem;
}

.adm-nav-link{
  display:flex;align-items:center;gap:.75rem;
  padding:.625rem .875rem;border-radius:.75rem;
  font-size:.84375rem;font-weight:500;color:rgba(255,255,255,.5);
  text-decoration:none;transition:all .2s ease;position:relative;
  overflow:hidden;
}
.adm-nav-link::before{
  content:'';position:absolute;inset:0;
  background:rgba(255,255,255,.05);opacity:0;
  transition:opacity .2s;border-radius:.75rem;
}
.adm-nav-link:hover{color:rgba(255,255,255,.9);}
.adm-nav-link:hover::before{opacity:1;}

.adm-nav-link.active{
  background:rgba(99,102,241,.2);color:#fff;font-weight:600;
  box-shadow:inset 0 0 0 1px rgba(99,102,241,.3);
}
.adm-nav-link.active .nav-icon-wrap{
  background:rgba(99,102,241,.3);color:#a5b4fc;
}

.nav-icon-wrap{
  width:28px;height:28px;border-radius:.5rem;
  display:flex;align-items:center;justify-content:center;
  background:rgba(255,255,255,.06);
  transition:background .2s,color .2s;font-size:.875rem;flex-shrink:0;
}

.adm-nav-link:hover .nav-icon-wrap{background:rgba(255,255,255,.1);}

.adm-footer{
  padding:1rem .875rem;border-top:1px solid rgba(255,255,255,.06);flex-shrink:0;
}
.adm-user-row{display:flex;align-items:center;gap:.625rem;}
.adm-user-avatar{
  width:34px;height:34px;border-radius:50%;
  background:linear-gradient(135deg,#6366f1,#8b5cf6);
  display:flex;align-items:center;justify-content:center;
  color:#fff;font-size:.8125rem;font-weight:700;flex-shrink:0;
  box-shadow:0 2px 8px rgba(99,102,241,.4);
}
.adm-user-name{font-size:.8125rem;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.adm-user-role{font-size:.6875rem;color:rgba(255,255,255,.35);margin-top:.0625rem;}
.adm-signout{
  margin-left:auto;background:none;border:none;
  color:rgba(255,255,255,.25);cursor:pointer;
  padding:.375rem;border-radius:.5rem;
  transition:all .2s;display:flex;align-items:center;
}
.adm-signout:hover{color:#f87171;background:rgba(248,113,113,.1);}

/* ══════════════════════════════════════════
   OVERLAY (mobile)
══════════════════════════════════════════ */
.adm-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:190;backdrop-filter:blur(4px);}
.adm-overlay.open{display:block;}

/* ══════════════════════════════════════════
   MAIN
══════════════════════════════════════════ */
.adm-main{margin-left:240px;flex:1;display:flex;flex-direction:column;min-height:100vh;}

/* TOPBAR */
.adm-topbar{
  background:rgba(240,242,248,.85);
  backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
  border-bottom:1px solid rgba(255,255,255,.7);
  padding:0 1.75rem;height:64px;
  display:flex;align-items:center;justify-content:space-between;
  position:sticky;top:0;z-index:40;gap:.75rem;
}
.adm-topbar-left{display:flex;align-items:center;gap:.875rem;}
.adm-topbar-title{font-size:1.125rem;font-weight:700;color:#0f0e1a;letter-spacing:-.02em;}

.adm-content{padding:1.75rem;flex:1;}

/* HAMBURGER */
.adm-hamburger{
  display:none;flex-direction:column;justify-content:center;align-items:center;
  width:38px;height:38px;gap:5px;background:rgba(255,255,255,.6);
  border:1.5px solid rgba(255,255,255,.8);border-radius:.625rem;
  cursor:pointer;flex-shrink:0;padding:6px;
  transition:all .2s;backdrop-filter:blur(10px);
}
.adm-hamburger:hover{background:rgba(99,102,241,.1);border-color:#a5b4fc;}
.adm-hamburger span{display:block;width:16px;height:2px;background:#374151;border-radius:2px;transition:all .25s;}
.adm-hamburger.open span:nth-child(1){transform:translateY(7px) rotate(45deg);}
.adm-hamburger.open span:nth-child(2){opacity:0;transform:scaleX(0);}
.adm-hamburger.open span:nth-child(3){transform:translateY(-7px) rotate(-45deg);}

/* ══════════════════════════════════════════
   CARD SYSTEM
══════════════════════════════════════════ */
.adm-card{
  background:#fff;
  border:1px solid rgba(231,234,243,.8);
  border-radius:1.25rem;
  box-shadow:0 2px 12px rgba(15,14,26,.05),0 1px 3px rgba(15,14,26,.04);
  transition:box-shadow .25s;
}
.adm-card:hover{box-shadow:0 6px 24px rgba(15,14,26,.08),0 2px 6px rgba(15,14,26,.05);}

/* ══════════════════════════════════════════
   UTILITIES
══════════════════════════════════════════ */
.adm-badge{display:inline-flex;align-items:center;font-size:.7rem;font-weight:700;padding:.2rem .625rem;border-radius:9999px;}
.adm-btn{display:inline-flex;align-items:center;gap:.375rem;border-radius:.75rem;padding:.5rem .9375rem;font-size:.8125rem;font-weight:600;cursor:pointer;border:none;font-family:inherit;transition:all .2s;}
.adm-btn-primary{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;box-shadow:0 4px 15px rgba(99,102,241,.3);}
.adm-btn-primary:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(99,102,241,.4);}
.adm-btn-outline{background:#fff;color:#374151;border:1.5px solid #e5e7eb;}
.adm-btn-outline:hover{border-color:#a5b4fc;color:#6366f1;background:#fafbff;}
.adm-btn-sm{padding:.3125rem .75rem;font-size:.78rem;}
.adm-input{
  border:1.5px solid #e5e7eb;border-radius:.75rem;
  padding:.5rem .875rem;font-size:.875rem;outline:none;
  font-family:inherit;transition:all .2s;
  background:#fafbff;color:#111827;width:100%;
}
.adm-input:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1);}
.adm-select{
  appearance:none;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='%236b7280' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
  background-repeat:no-repeat;background-position:right .625rem center;background-size:1rem;padding-right:2.25rem;
}

@keyframes pulse{0%,100%{opacity:1}50%{opacity:.45}}
@keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}
@keyframes fadeInUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}

.skeleton{
  background:linear-gradient(90deg,#f0f2f8 25%,#e4e8f3 50%,#f0f2f8 75%);
  background-size:200% 100%;
  animation:shimmer 1.5s infinite;
  border-radius:.75rem;
}

/* ══════════════════════════════════════════
   MODAL
══════════════════════════════════════════ */
.adm-modal-bg{display:none;position:fixed;inset:0;background:rgba(15,14,26,.5);z-index:300;align-items:center;justify-content:center;padding:1rem;backdrop-filter:blur(6px);}
.adm-modal-bg.open{display:flex;}
.adm-modal{background:#fff;border-radius:1.5rem;width:460px;max-width:100%;padding:2rem;box-shadow:0 24px 80px rgba(15,14,26,.25);}
.adm-modal-title{font-size:1.0625rem;font-weight:700;color:#111827;margin-bottom:1.375rem;}

/* ══════════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════════ */
@media(max-width:768px){
  .adm-sidebar{transform:translateX(-100%);}
  .adm-sidebar.open{transform:translateX(0);}
  .adm-main{margin-left:0;width:100vw;max-width:100vw;overflow-x:hidden;}
  .adm-hamburger{display:flex;}
  .adm-content{padding:1rem;max-width:100%;}
  .adm-topbar{padding:0 1rem;max-width:100vw;}
  .adm-card{max-width:100%;}
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
            <span class="nav-icon-wrap">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            </span>
            Dashboard
        </a>

        <div class="adm-nav-label">Manage</div>
        <a href="{{ route('admin.complaints.index') }}" class="adm-nav-link {{ request()->routeIs('admin.complaints.*') ? 'active' : '' }}">
            <span class="nav-icon-wrap">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            </span>
            Complaints
        </a>
        <a href="{{ route('admin.users.index') }}" class="adm-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <span class="nav-icon-wrap">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </span>
            Users
        </a>
        <a href="{{ route('admin.categories.index') }}" class="adm-nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
            <span class="nav-icon-wrap">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </span>
            Categories
        </a>

        <div class="adm-nav-label">System</div>
        <a href="{{ url('/') }}" class="adm-nav-link" target="_blank">
            <span class="nav-icon-wrap">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
            </span>
            Public Site
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
                    <svg style="width:1rem;height:1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

        {{-- Topbar right: notifications + user dropdown --}}
        <div style="display:flex;align-items:center;gap:.875rem;">

            {{-- Notification Bell --}}
            <button style="position:relative;width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,.7);border:1.5px solid rgba(255,255,255,.9);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .2s;backdrop-filter:blur(10px);"
                    onmouseover="this.style.background='rgba(99,102,241,.1)';this.style.borderColor='#a5b4fc'"
                    onmouseout="this.style.background='rgba(255,255,255,.7)';this.style.borderColor='rgba(255,255,255,.9)'">
                <svg width="16" height="16" fill="none" stroke="#374151" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </button>

            {{-- User Dropdown --}}
            <style>
                @keyframes admDropIn{from{opacity:0;transform:translateY(-8px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}
                #adm-user-menu{animation:admDropIn .18s cubic-bezier(.4,0,.2,1);}
            </style>
            <div style="position:relative;">
                <button onclick="document.getElementById('adm-user-menu').style.display=document.getElementById('adm-user-menu').style.display==='block'?'none':'block'"
                        style="display:flex;align-items:center;gap:.625rem;background:rgba(255,255,255,.7);border:1.5px solid rgba(255,255,255,.9);
                               border-radius:9999px;padding:.3125rem .875rem .3125rem .3125rem;cursor:pointer;
                               transition:all .2s;font-family:inherit;backdrop-filter:blur(10px);"
                        onmouseover="this.style.background='rgba(255,255,255,.9)';this.style.borderColor='#a5b4fc'"
                        onmouseout="this.style.background='rgba(255,255,255,.7)';this.style.borderColor='rgba(255,255,255,.9)'">
                    @if(auth()->user()->profile_photo_url)
                        <img src="{{ auth()->user()->profile_photo_url }}" alt="avatar"
                             style="width:30px;height:30px;border-radius:50%;object-fit:cover;">
                    @else
                        <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);
                                    display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:700;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif
                    <span style="font-size:.8125rem;font-weight:600;color:#1f1d2e;">{{ auth()->user()->name }}</span>
                    <svg style="width:12px;height:12px;color:#9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div id="adm-user-menu" style="display:none;position:absolute;top:calc(100% + 12px);right:0;width:230px;
                     background:#fff;border:1px solid #ebe9f6;border-radius:1.25rem;
                     box-shadow:0 20px 60px rgba(15,14,26,.15);z-index:500;overflow:hidden;">

                    {{-- Profile header --}}
                    <div style="padding:1rem;border-bottom:1px solid #f3f4f6;display:flex;gap:.75rem;align-items:center;background:linear-gradient(135deg,#fafbff,#f5f3ff);">
                        @if(auth()->user()->profile_photo_url)
                            <img src="{{ auth()->user()->profile_photo_url }}" alt="avatar"
                                 style="width:40px;height:40px;border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid #e0e7ff;">
                        @else
                            <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);
                                        display:flex;align-items:center;justify-content:center;
                                        color:#fff;font-size:.9375rem;font-weight:700;flex-shrink:0;
                                        box-shadow:0 3px 10px rgba(99,102,241,.35);">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        @endif
                        <div style="min-width:0;">
                            <div style="font-size:.8125rem;font-weight:700;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->name }}</div>
                            <div style="font-size:.7rem;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:.0625rem;">{{ auth()->user()->email }}</div>
                            <span style="font-size:.625rem;font-weight:700;background:#eef2ff;color:#4f46e5;padding:.125rem .4rem;border-radius:5px;margin-top:.25rem;display:inline-block;">ADMIN</span>
                        </div>
                    </div>

                    {{-- Menu items --}}
                    <div style="padding:.5rem;">
                        <a href="{{ route('admin.profile') }}"
                           style="width:100%;display:flex;align-items:center;gap:.625rem;padding:.5625rem .875rem;
                                  background:none;border-radius:.75rem;font-size:.8125rem;
                                  font-weight:500;color:#374151;cursor:pointer;text-decoration:none;
                                  transition:all .15s;"
                           onmouseover="this.style.background='#f5f3ff';this.style.color='#6366f1'" onmouseout="this.style.background='none';this.style.color='#374151'">
                            <span style="width:28px;height:28px;border-radius:.5rem;background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-size:.8125rem;">👤</span>
                            My Profile
                        </a>
                        <div style="height:1px;background:#f3f4f6;margin:.375rem .25rem;"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    style="width:100%;display:flex;align-items:center;gap:.625rem;padding:.5625rem .875rem;
                                           background:none;border:none;border-radius:.75rem;font-size:.8125rem;
                                           font-weight:500;color:#dc2626;cursor:pointer;font-family:inherit;
                                           transition:all .15s;text-align:left;"
                                    onmouseover="this.style.background='#fff5f5'" onmouseout="this.style.background='none'">
                                <span style="width:28px;height:28px;border-radius:.5rem;background:#fef2f2;display:flex;align-items:center;justify-content:center;font-size:.8125rem;">🚪</span>
                                Sign Out
                            </button>
                        </form>
                    </div>
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

        // ── Mobile sidebar toggle ──
        function admToggleSidebar() {
            const sidebar   = document.querySelector('.adm-sidebar');
            const overlay   = document.getElementById('adm-overlay');
            const hamburger = document.getElementById('adm-hamburger');
            const isOpen    = sidebar.classList.contains('open');
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

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' — RoadWatch' : 'RoadWatch' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .rw-auth-card { background: #fff; border-radius: 1.5rem; box-shadow: 0 20px 60px rgba(0,0,0,.12); width: 100%; max-width: 460px; overflow: hidden; }
        .rw-auth-header { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #9333ea 100%); padding: 2rem 2rem 1.75rem; text-align: center; position: relative; overflow: hidden; }
        .rw-auth-header::before { content: ''; position: absolute; inset: 0; background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E"); }
        .rw-auth-logo { display: inline-flex; align-items: center; gap: .625rem; margin-bottom: 1rem; text-decoration: none; position: relative; }
        .rw-auth-logo-icon { width: 42px; height: 42px; background: rgba(255,255,255,.2); border-radius: .75rem; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,.3); }
        .rw-auth-logo-text { font-size: 1.375rem; font-weight: 800; color: #fff; letter-spacing: -.03em; }
        .rw-auth-tagline { font-size: .8125rem; color: rgba(255,255,255,.75); font-weight: 500; position: relative; }
        .rw-auth-body { padding: 2rem; }

        /* Form elements reset */
        label { display: block; font-size: .8125rem; font-weight: 600; color: #374151; margin-bottom: .375rem; }
        input[type="text"], input[type="email"], input[type="password"], input[type="tel"], select, textarea {
            display: block; width: 100%; padding: .625rem .875rem; font-size: .9375rem; font-family: inherit;
            border: 1.5px solid #e5e7eb; border-radius: .625rem; background: #f9fafb; color: #111827;
            outline: none; transition: border-color .15s, background .15s, box-shadow .15s;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #6366f1; background: #fff;
            box-shadow: 0 0 0 3px rgba(99,102,241,.12);
        }
        input[type="checkbox"] { width: 1rem; height: 1rem; accent-color: #4f46e5; cursor: pointer; }
        .rw-field { margin-bottom: 1rem; }
        .rw-btn-primary {
            display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
            background: linear-gradient(135deg, #4f46e5, #6366f1); color: #fff; font-family: inherit;
            font-size: .9375rem; font-weight: 700; border: none; border-radius: .625rem;
            padding: .6875rem 1.5rem; cursor: pointer; transition: opacity .15s, transform .1s;
            box-shadow: 0 4px 14px rgba(79,70,229,.35); text-decoration: none;
        }
        .rw-btn-primary:hover { opacity: .9; transform: translateY(-1px); }
        .rw-btn-primary:active { transform: translateY(0); }
        .rw-error { font-size: .78rem; color: #dc2626; margin-top: .25rem; }
        .rw-hint { font-size: .75rem; color: #9ca3af; margin-top: .25rem; }
        .rw-link { color: #4f46e5; font-weight: 600; text-decoration: none; }
        .rw-link:hover { text-decoration: underline; }
        .rw-divider { display: flex; align-items: center; gap: .75rem; margin: 1.25rem 0; }
        .rw-divider::before, .rw-divider::after { content: ''; flex: 1; height: 1px; background: #e5e7eb; }
        .rw-divider span { font-size: .78rem; color: #9ca3af; white-space: nowrap; }
        .rw-google-btn {
            display: flex; align-items: center; justify-content: center; gap: .625rem;
            width: 100%; padding: .6875rem 1rem; border: 1.5px solid #e5e7eb; border-radius: .625rem;
            background: #fff; color: #374151; font-size: .9375rem; font-weight: 600;
            text-decoration: none; cursor: pointer; transition: all .15s;
            box-shadow: 0 1px 3px rgba(0,0,0,.06); font-family: inherit;
        }
        .rw-google-btn:hover { background: #f9fafb; border-color: #d1d5db; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .rw-form-footer { text-align: center; font-size: .8125rem; color: #6b7280; margin-top: 1.25rem; }
        .rw-alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: .75rem 1rem; border-radius: .625rem; font-size: .875rem; margin-bottom: 1rem; }
        .rw-alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; padding: .75rem 1rem; border-radius: .625rem; font-size: .875rem; margin-bottom: 1rem; }
        .rw-alert-info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; padding: .75rem 1rem; border-radius: .625rem; font-size: .875rem; margin-bottom: 1rem; }
        .rw-flex-between { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
        .rw-remember { display: flex; align-items: center; gap: .5rem; font-size: .875rem; color: #4b5563; cursor: pointer; }
    </style>
</head>
<body>
    <div class="rw-auth-card">
        {{-- Branded header --}}
        <div class="rw-auth-header">
            <a href="/" class="rw-auth-logo">
                <div class="rw-auth-logo-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="rgba(255,255,255,0.9)" stroke="rgba(255,255,255,0.5)" stroke-width="1"/>
                        <path d="M2 17L12 22L22 17" stroke="rgba(255,255,255,0.9)" stroke-width="2" stroke-linecap="round"/>
                        <path d="M2 12L12 17L22 12" stroke="rgba(255,255,255,0.7)" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <span class="rw-auth-logo-text">RoadWatch</span>
            </a>
            <p class="rw-auth-tagline">Report · Track · Resolve Road Issues</p>
        </div>

        {{-- Form body --}}
        <div class="rw-auth-body">
            {{ $slot }}
        </div>
    </div>
</body>
</html>

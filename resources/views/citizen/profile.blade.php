@extends('layouts.citizen')

@section('title', 'My Profile — RoadWatch')

@section('content')

{{-- Loading state --}}
<div id="profile-loading" style="display:flex;flex-direction:column;gap:1.5rem;max-width:800px;margin:0 auto;">
    <div style="background:#f3f4f6;border-radius:1.25rem;height:200px;animation:ppulse 1.4s ease-in-out infinite;"></div>
    <div style="background:#f3f4f6;border-radius:1.25rem;height:140px;animation:ppulse 1.4s ease-in-out infinite 0.1s;"></div>
</div>

{{-- Main profile content (filled by JS) --}}
<div id="profile-content" style="display:none;max-width:800px;margin:0 auto;">

    {{-- ── Hero card ── --}}
    <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:1.5rem;
                overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.06);margin-bottom:1.5rem;">
        {{-- Cover strip (clickable to change banner) --}}
        <div id="profile-banner-area"
             style="height:130px;background:linear-gradient(135deg,#4f46e5,#7c3aed,#a855f7);
                    position:relative;cursor:pointer;"
             onclick="puTriggerBanner()">
            <div id="pu-banner-spinner" class="pu-spinner"
                 style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);"></div>
            <button class="pu-btn pu-overlay-banner" onclick="event.stopPropagation();puTriggerBanner()"
                    style="position:absolute;bottom:.5rem;right:.75rem;border-radius:.5rem;
                           padding:.35rem .75rem;font-size:.72rem;gap:.35rem;">
                📷 Edit Cover
            </button>
        </div>

        {{-- Avatar + info --}}
        <div style="padding:0 1.75rem 1.75rem;position:relative;">
            {{-- Avatar (with edit overlay) --}}
            <div id="avatar-wrap" style="margin-top:-50px;margin-bottom:1rem;display:inline-block;position:relative;">
                {{-- JS fills this in --}}
            </div>

            <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
                <div>
                    <h1 id="p-name" style="font-size:1.5rem;font-weight:800;color:#111827;margin:0 0 .25rem;letter-spacing:-.02em;"></h1>
                    <p id="p-email" style="font-size:.9rem;color:#6b7280;margin:0 0 .625rem;"></p>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                        <span id="p-role-badge"></span>
                        <span id="p-provider-badge"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Stats row ── --}}
    <div id="stats-row" style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;"></div>

    {{-- ── Details grid ── --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

        {{-- Personal info --}}
        <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:1.25rem;padding:1.5rem;
                    box-shadow:0 2px 12px rgba(0,0,0,.04);">
            <h2 style="font-size:1rem;font-weight:700;color:#111827;margin:0 0 1.25rem;">👤 Personal Information</h2>
            <div id="p-info-rows" style="display:flex;flex-direction:column;gap:.75rem;"></div>
        </div>

        {{-- Account info --}}
        <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:1.25rem;padding:1.5rem;
                    box-shadow:0 2px 12px rgba(0,0,0,.04);">
            <h2 style="font-size:1rem;font-weight:700;color:#111827;margin:0 0 1.25rem;">🔐 Account Details</h2>
            <div id="p-account-rows" style="display:flex;flex-direction:column;gap:.75rem;"></div>
        </div>
    </div>

    {{-- ── Recent complaints ── --}}
    <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:1.25rem;padding:1.5rem;
                box-shadow:0 2px 12px rgba(0,0,0,.04);margin-top:1.5rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
            <h2 style="font-size:1rem;font-weight:700;color:#111827;margin:0;">📋 Recent Complaints</h2>
            <a href="{{ route('citizen.complaints.index') }}"
               style="font-size:.8125rem;font-weight:600;color:#4f46e5;text-decoration:none;">View all →</a>
        </div>
        <div id="recent-complaints"></div>
    </div>

</div>

<style>
@keyframes ppulse{0%,100%{opacity:1}50%{opacity:.4}}
</style>

<script>
const STATUS_CFG = {
    pending:      {label:'⏳ Pending',      color:'#92400e', bg:'#fffbeb'},
    under_review: {label:'🔍 Under Review', color:'#1e40af', bg:'#eff6ff'},
    in_progress:  {label:'🔧 In Progress',  color:'#1d4ed8', bg:'#dbeafe'},
    resolved:     {label:'✅ Resolved',     color:'#14532d', bg:'#f0fdf4'},
    rejected:     {label:'❌ Rejected',     color:'#7f1d1d', bg:'#fef2f2'},
};

function row(label, value, icon = '') {
    return `<div style="display:flex;justify-content:space-between;align-items:center;
                padding:.5rem 0;border-bottom:1px solid #f9fafb;font-size:.8125rem;">
        <span style="color:#6b7280;font-weight:500;display:flex;align-items:center;gap:.375rem;">${icon} ${label}</span>
        <span style="color:#111827;font-weight:600;text-align:right;max-width:55%;">${esc(value)}</span>
    </div>`;
}

function esc(s){return String(s??'—').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
function fmt(iso){return iso ? new Date(iso).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'}) : '—';}

async function loadProfile() {
    try {
        const [meRes, cRes] = await Promise.all([
            axios.get('/api/v1/me'),
            axios.get('/api/v1/citizen/complaints', {params:{page:1,per_page:5}})
        ]);
        const u = meRes.data.data;
        const complaints = cRes.data.data ?? [];
        const meta       = cRes.data.meta ?? {};

        // ── Banner --
        if (u.profile_banner_url) {
            const ba = document.getElementById('profile-banner-area');
            if (ba) {
                ba.style.backgroundImage    = `url('${esc(u.profile_banner_url)}')`;
                ba.style.backgroundSize     = 'cover';
                ba.style.backgroundPosition = 'center';
            }
        }

        // ── Avatar --
        const avatarWrap = document.getElementById('avatar-wrap');
        const avatarInner = u.profile_photo_url
            ? `<img src="${esc(u.profile_photo_url)}" alt="avatar"
                    style="width:98px;height:98px;border-radius:50%;object-fit:cover;
                           border:4px solid #fff;box-sizing:border-box;box-shadow:0 4px 16px rgba(0,0,0,.12);display:block;">`
            : `<div style="width:98px;height:98px;border-radius:50%;box-sizing:border-box;
                   background:linear-gradient(135deg,#4f46e5,#7c3aed);
                   border:4px solid #fff;box-shadow:0 4px 16px rgba(79,70,229,.25);
                   display:flex;align-items:center;justify-content:center;
                   font-size:2.25rem;font-weight:800;color:#fff;">${(u.name??'U')[0].toUpperCase()}</div>`;
        avatarWrap.innerHTML = `
            <div style="position:relative;display:inline-block;width:98px;height:98px;border-radius:50%;overflow:hidden;">
                ${avatarInner}
                <button class="pu-btn pu-overlay-avatar" onclick="puTriggerPhoto()"
                        style="width:98px;height:98px;top:0;left:0;border-radius:50%;">
                    <div id="pu-avatar-spinner" class="pu-spinner"></div>
                    <span style="font-size:1.25rem;">📷</span>
                </button>
            </div>`;

        // ── Name / email / badges --
        document.getElementById('p-name').textContent  = u.name ?? '—';
        document.getElementById('p-email').textContent = u.email ?? '—';

        document.getElementById('p-role-badge').innerHTML =
            `<span style="font-size:.73rem;font-weight:700;background:#eef2ff;color:#4f46e5;
                          padding:.2rem .75rem;border-radius:9999px;">${(u.role??'citizen').toUpperCase()}</span>`;

        if (u.auth_provider && u.auth_provider !== 'local') {
            document.getElementById('p-provider-badge').innerHTML =
                `<span style="font-size:.73rem;font-weight:700;background:#fef3c7;color:#92400e;
                              padding:.2rem .75rem;border-radius:9999px;">via ${u.auth_provider}</span>`;
        }

        // ── Stats row --
        const resolved  = complaints.filter(c => c.status === 'resolved').length;
        const pending   = complaints.filter(c => c.status === 'pending').length;
        const inprog    = complaints.filter(c => c.status === 'in_progress').length;

        const stats = [
            {label:'Total Reports', value: meta.total ?? complaints.length, icon:'📋', color:'#4f46e5', bg:'#eef2ff'},
            {label:'Pending',       value: pending,   icon:'⏳', color:'#92400e', bg:'#fffbeb'},
            {label:'In Progress',   value: inprog,    icon:'🔧', color:'#1d4ed8', bg:'#dbeafe'},
            {label:'Resolved',      value: resolved,  icon:'✅', color:'#14532d', bg:'#f0fdf4'},
        ];
        document.getElementById('stats-row').innerHTML = stats.map(s => `
            <div style="background:${s.bg};border-radius:1rem;padding:1.25rem;text-align:center;">
                <div style="font-size:1.75rem;margin-bottom:.375rem;">${s.icon}</div>
                <div style="font-size:1.625rem;font-weight:900;color:${s.color};line-height:1;">${s.value}</div>
                <div style="font-size:.75rem;font-weight:600;color:${s.color};opacity:.7;margin-top:.25rem;">${s.label}</div>
            </div>`).join('');

        // ── Personal info rows --
        document.getElementById('p-info-rows').innerHTML =
            row('Full Name',   u.name)   +
            row('Phone',       u.phone   ?? 'Not set') +
            row('Gender',      u.gender  ? u.gender.charAt(0).toUpperCase() + u.gender.slice(1) : 'Not set');

        // ── Account rows --
        document.getElementById('p-account-rows').innerHTML =
            row('Member Since', fmt(u.created_at)) +
            row('Auth Method',  u.auth_provider ?? 'Email/Password') +
            row('Account Status', u.is_active ? '🟢 Active' : '🔴 Inactive');

        // ── Recent complaints --
        const rc = document.getElementById('recent-complaints');
        if (!complaints.length) {
            rc.innerHTML = `<div style="text-align:center;padding:2rem;color:#9ca3af;font-size:.875rem;">
                No complaints yet. <a href="{{ route('citizen.complaints.create') }}"
                style="color:#4f46e5;font-weight:600;text-decoration:none;">Report your first issue →</a></div>`;
        } else {
            rc.innerHTML = complaints.slice(0,5).map(c => {
                const s = STATUS_CFG[c.status] ?? {label:c.status,color:'#374151',bg:'#f3f4f6'};
                return `<a href="/citizen/complaints/${c.id}"
                     style="display:flex;align-items:center;justify-content:space-between;
                            padding:.875rem .75rem;border-radius:.75rem;text-decoration:none;
                            color:inherit;transition:background .15s;gap:1rem;"
                     onmouseover="this.style.background='#f9fafb'"
                     onmouseout="this.style.background='transparent'">
                    <div style="display:flex;align-items:center;gap:.75rem;min-width:0;">
                        <span style="font-size:1.5rem;flex-shrink:0;">${c.category?.icon ?? '📌'}</span>
                        <div style="min-width:0;">
                            <p style="font-size:.875rem;font-weight:600;color:#111827;margin:0;
                                      white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(c.title)}</p>
                            <p style="font-size:.75rem;color:#9ca3af;margin:.125rem 0 0;">${esc(c.complaint_number)} · ${esc(c.location)}</p>
                        </div>
                    </div>
                    <span style="font-size:.72rem;font-weight:700;padding:.25rem .625rem;border-radius:9999px;
                                 white-space:nowrap;background:${s.bg};color:${s.color};flex-shrink:0;">${s.label}</span>
                </a>`;
            }).join('');
        }

        // Show content
        document.getElementById('profile-loading').style.display = 'none';
        document.getElementById('profile-content').style.display = 'block';

    } catch(e) {
        document.getElementById('profile-loading').innerHTML =
            `<div style="text-align:center;padding:4rem;color:#9ca3af;">
                <div style="font-size:2.5rem;margin-bottom:1rem;">⚠️</div>
                <p>Could not load profile. <a href="" style="color:#4f46e5;">Retry</a></p>
            </div>`;
    }
}

document.addEventListener('DOMContentLoaded', loadProfile);
</script>

@include('partials.profile-upload-ui')

@endsection

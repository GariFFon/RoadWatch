@extends('layouts.admin')

@section('title', 'My Profile — Admin')
@section('page-title', 'My Profile')

@section('content')

{{-- Loading skeleton --}}
<div id="profile-loading" style="display:flex;flex-direction:column;gap:1.5rem;max-width:860px;margin:0 auto;">
    <div style="background:#f3f4f6;border-radius:1.25rem;height:200px;animation:apulse 1.4s ease-in-out infinite;"></div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;">
        <div style="background:#f3f4f6;border-radius:1rem;height:100px;animation:apulse 1.4s ease-in-out infinite 0.1s;"></div>
        <div style="background:#f3f4f6;border-radius:1rem;height:100px;animation:apulse 1.4s ease-in-out infinite 0.15s;"></div>
        <div style="background:#f3f4f6;border-radius:1rem;height:100px;animation:apulse 1.4s ease-in-out infinite 0.2s;"></div>
        <div style="background:#f3f4f6;border-radius:1rem;height:100px;animation:apulse 1.4s ease-in-out infinite 0.25s;"></div>
    </div>
</div>

{{-- Profile content --}}
<div id="profile-content" style="display:none;max-width:860px;margin:0 auto;">

    {{-- Hero card --}}
    <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:1.5rem;
                overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.06);margin-bottom:1.5rem;">
        {{-- Cover strip (click = lightbox, pencil = upload) --}}
        <div id="profile-banner-area" data-src=""
             style="height:130px;background:linear-gradient(135deg,#4f46e5,#7c3aed,#9333ea);
                    position:relative;cursor:zoom-in;"
             onclick="puOpenLightbox(this.dataset.src)">
            <div id="pu-banner-spinner" class="pu-spinner"
                 style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);"></div>
            <button class="pu-btn pu-pencil-banner"
                    onclick="event.stopPropagation();puTriggerBanner()"
                    title="Change cover photo">
                ✏️ Edit Cover
            </button>
        </div>

        {{-- Avatar + info --}}
        <div style="padding:0 1.75rem 1.75rem;position:relative;">
            <div id="ap-avatar-wrap" style="margin-top:-50px;margin-bottom:1rem;display:inline-block;position:relative;"></div>

            <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
                <div>
                    <h1 id="ap-name" style="font-size:1.5rem;font-weight:800;color:#111827;margin:0 0 .25rem;letter-spacing:-.02em;"></h1>
                    <p id="ap-email" style="font-size:.9rem;color:#6b7280;margin:0 0 .625rem;"></p>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                        <span id="ap-role-badge"></span>
                        <span id="ap-provider-badge"></span>
                    </div>
                </div>
                <a href="{{ route('admin.complaints.index') }}"
                   style="display:inline-flex;align-items:center;gap:.5rem;background:#4f46e5;color:#fff;
                          text-decoration:none;border-radius:.875rem;padding:.625rem 1.25rem;
                          font-size:.875rem;font-weight:700;box-shadow:0 2px 10px rgba(79,70,229,.35);
                          transition:background .15s;" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
                    📋 Manage Complaints
                </a>
            </div>
        </div>
    </div>

    {{-- Stats row --}}
    <div id="ap-stats-row" style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;"></div>

    {{-- Details grid --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">

        {{-- Personal info --}}
        <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:1.25rem;padding:1.5rem;
                    box-shadow:0 2px 12px rgba(0,0,0,.04);">
            <h2 style="font-size:1rem;font-weight:700;color:#111827;margin:0 0 1.25rem;">👤 Personal Information</h2>
            <div id="ap-info-rows" style="display:flex;flex-direction:column;gap:.75rem;"></div>
        </div>

        {{-- Account info --}}
        <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:1.25rem;padding:1.5rem;
                    box-shadow:0 2px 12px rgba(0,0,0,.04);">
            <h2 style="font-size:1rem;font-weight:700;color:#111827;margin:0 0 1.25rem;">🔐 Account Details</h2>
            <div id="ap-account-rows" style="display:flex;flex-direction:column;gap:.75rem;"></div>
        </div>
    </div>

    {{-- System overview --}}
    <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:1.25rem;padding:1.5rem;
                box-shadow:0 2px 12px rgba(0,0,0,.04);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
            <h2 style="font-size:1rem;font-weight:700;color:#111827;margin:0;">📊 Complaint Overview (All)</h2>
            <a href="{{ route('admin.complaints.index') }}"
               style="font-size:.8125rem;font-weight:600;color:#4f46e5;text-decoration:none;">View all →</a>
        </div>
        <div id="ap-recent"></div>
    </div>

</div>

<style>
@keyframes apulse{0%,100%{opacity:1}50%{opacity:.4}}
</style>

<script>
const AP_STATUS = {
    pending:               {label:'⏳ Pending',              color:'#92400e', bg:'#fffbeb'},
    under_review:          {label:'🔍 Under Review',         color:'#1e40af', bg:'#eff6ff'},
    in_progress:           {label:'🔧 In Progress',          color:'#0c4a6e', bg:'#e0f2fe'},
    awaiting_verification: {label:'🕐 Awaiting Verification',color:'#c2410c', bg:'#fff7ed'},
    verified:              {label:'✅ Verified',             color:'#14532d', bg:'#f0fdf4'},
    rejected:              {label:'❌ Rejected',             color:'#7f1d1d', bg:'#fef2f2'},
};

function esc(s){return String(s??'—').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
function fmt(iso){return iso ? new Date(iso).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'}) : '—';}

function row(label, value, icon='') {
    return `<div style="display:flex;justify-content:space-between;align-items:center;
                padding:.5rem 0;border-bottom:1px solid #f9fafb;font-size:.8125rem;">
        <span style="color:#6b7280;font-weight:500;display:flex;align-items:center;gap:.375rem;">${icon} ${label}</span>
        <span style="color:#111827;font-weight:600;text-align:right;max-width:55%;">${esc(value)}</span>
    </div>`;
}

async function loadProfile() {
    try {
        const [meRes, cmpRes] = await Promise.all([
            axios.get('/api/v1/me'),
            axios.get('/api/v1/admin/complaints', {params:{page:1, per_page:100}}),
        ]);

        const u   = meRes.data.data;
        const all = cmpRes.data.data ?? [];
        const meta = cmpRes.data.meta ?? {};

        // ── Banner
        const ba = document.getElementById('profile-banner-area');
        if (ba && u.profile_banner_url) {
            ba.style.backgroundImage    = `url('${esc(u.profile_banner_url)}')`;
            ba.style.backgroundSize     = 'cover';
            ba.style.backgroundPosition = 'center';
            ba.style.cursor             = 'zoom-in';
            ba.dataset.src              = u.profile_banner_url;
        }

        // ── Avatar
        const wrap     = document.getElementById('ap-avatar-wrap');
        const photoUrl = u.profile_photo_url || '';
        const initials = (u.name ?? 'A')[0].toUpperCase();

        const avatarContent = photoUrl
            ? `<img src="${esc(photoUrl)}" data-src="${esc(photoUrl)}" alt="avatar"
                    onclick="puOpenLightbox(this.dataset.src)"
                    style="width:98px;height:98px;border-radius:50%;object-fit:cover;
                           border:4px solid #fff;box-sizing:border-box;
                           box-shadow:0 4px 16px rgba(0,0,0,.12);display:block;cursor:zoom-in;">`
            : `<div style="width:98px;height:98px;border-radius:50%;box-sizing:border-box;
                   background:linear-gradient(135deg,#4f46e5,#7c3aed);
                   border:4px solid #fff;box-shadow:0 4px 16px rgba(79,70,229,.25);
                   display:flex;align-items:center;justify-content:center;
                   font-size:2.25rem;font-weight:800;color:#fff;">${initials}</div>`;

        wrap.innerHTML = `
            <div style="position:relative;display:inline-block;width:98px;height:98px;">
                ${avatarContent}
                <button class="pu-btn pu-pencil-avatar" onclick="puTriggerPhoto()" title="Change profile photo">
                    <div id="pu-avatar-spinner" class="pu-spinner"></div>
                    ✏️
                </button>
            </div>`;

        document.getElementById('ap-name').textContent  = u.name ?? '—';
        document.getElementById('ap-email').textContent = u.email ?? '—';
        document.getElementById('ap-role-badge').innerHTML =
            `<span style="font-size:.73rem;font-weight:700;background:#eef2ff;color:#4338ca;
                          padding:.2rem .75rem;border-radius:9999px;">ADMIN</span>`;
        if (u.auth_provider && u.auth_provider !== 'local') {
            document.getElementById('ap-provider-badge').innerHTML =
                `<span style="font-size:.73rem;font-weight:700;background:#fef3c7;color:#92400e;
                              padding:.2rem .75rem;border-radius:9999px;">via ${u.auth_provider}</span>`;
        }

        // ── Stats
        const pending  = all.filter(c => c.status === 'pending').length;
        const active   = all.filter(c => ['under_review','in_progress','awaiting_verification'].includes(c.status)).length;
        const verified = all.filter(c => c.status === 'verified').length;
        const total    = meta.total ?? all.length;

        const stats = [
            {label:'Total',      value: total,    icon:'📋', color:'#4f46e5', bg:'#eef2ff'},
            {label:'Pending',    value: pending,  icon:'⏳', color:'#92400e', bg:'#fffbeb'},
            {label:'Active',     value: active,   icon:'🔧', color:'#0c4a6e', bg:'#e0f2fe'},
            {label:'Verified',   value: verified, icon:'✅', color:'#14532d', bg:'#f0fdf4'},
        ];
        document.getElementById('ap-stats-row').innerHTML = stats.map(s => `
            <div style="background:${s.bg};border-radius:1rem;padding:1.25rem;text-align:center;">
                <div style="font-size:1.75rem;margin-bottom:.375rem;">${s.icon}</div>
                <div style="font-size:1.625rem;font-weight:900;color:${s.color};line-height:1;">${s.value}</div>
                <div style="font-size:.75rem;font-weight:600;color:${s.color};opacity:.75;margin-top:.25rem;">${s.label}</div>
            </div>`).join('');

        // ── Personal info
        document.getElementById('ap-info-rows').innerHTML =
            row('Full Name', u.name) +
            row('Phone',     u.phone  ?? 'Not set') +
            row('Gender',    u.gender ? u.gender.charAt(0).toUpperCase() + u.gender.slice(1) : 'Not set');

        // ── Account rows
        document.getElementById('ap-account-rows').innerHTML =
            row('Member Since',   fmt(u.created_at)) +
            row('Auth Method',    u.auth_provider ?? 'Email/Password') +
            row('Account Status', u.is_active ? '🟢 Active' : '🔴 Inactive');

        // ── Recent complaints
        const rc = document.getElementById('ap-recent');
        if (!all.length) {
            rc.innerHTML = `<div style="text-align:center;padding:2rem;color:#9ca3af;font-size:.875rem;">No complaints in the system yet.</div>`;
        } else {
            rc.innerHTML = all.slice(0,6).map(c => {
                const s = AP_STATUS[c.status] ?? {label:c.status, color:'#374151', bg:'#f3f4f6'};
                return `<div style="display:flex;align-items:center;justify-content:space-between;
                            padding:.875rem .75rem;border-radius:.75rem;gap:1rem;">
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
                </div>`;
            }).join('');
        }

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

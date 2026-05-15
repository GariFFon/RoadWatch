@extends('layouts.engineer')

@section('title', 'My Profile — Engineer')
@section('page-title', 'My Profile')

@section('content')

{{-- Loading skeleton --}}
<div id="profile-loading" style="display:flex;flex-direction:column;gap:1.5rem;max-width:860px;margin:0 auto;">
    <div style="background:#f3f4f6;border-radius:1.25rem;height:200px;animation:epulse 1.4s ease-in-out infinite;"></div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;">
        <div style="background:#f3f4f6;border-radius:1rem;height:100px;animation:epulse 1.4s ease-in-out infinite 0.1s;"></div>
        <div style="background:#f3f4f6;border-radius:1rem;height:100px;animation:epulse 1.4s ease-in-out infinite 0.15s;"></div>
        <div style="background:#f3f4f6;border-radius:1rem;height:100px;animation:epulse 1.4s ease-in-out infinite 0.2s;"></div>
    </div>
</div>

{{-- Profile content --}}
<div id="profile-content" style="display:none;max-width:860px;margin:0 auto;">

    {{-- Hero card --}}
    <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:1.5rem;
                overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.06);margin-bottom:1.5rem;">
        {{-- Cover strip (click = lightbox, pencil = upload) --}}
        <div id="profile-banner-area" data-src=""
             style="height:130px;background:linear-gradient(135deg,#0ea5e9,#6366f1,#8b5cf6);
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
            <div id="ep-avatar-wrap" style="margin-top:-50px;margin-bottom:1rem;display:inline-block;position:relative;"></div>

            <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
                <div>
                    <h1 id="ep-name" style="font-size:1.5rem;font-weight:800;color:#111827;margin:0 0 .25rem;letter-spacing:-.02em;"></h1>
                    <p id="ep-email" style="font-size:.9rem;color:#6b7280;margin:0 0 .625rem;"></p>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                        <span id="ep-role-badge"></span>
                        <span id="ep-provider-badge"></span>
                    </div>
                </div>
                <a href="{{ route('engineer.complaints.index') }}"
                   style="display:inline-flex;align-items:center;gap:.5rem;
                          background:linear-gradient(135deg,#0ea5e9,#6366f1);color:#fff;
                          text-decoration:none;border-radius:.875rem;padding:.625rem 1.25rem;
                          font-size:.875rem;font-weight:700;box-shadow:0 2px 10px rgba(14,165,233,.35);
                          transition:opacity .15s;" onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                    📋 My Assignments
                </a>
            </div>
        </div>
    </div>

    {{-- Stats row --}}
    <div id="ep-stats-row" style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem;"></div>

    {{-- Details grid --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">

        {{-- Personal info --}}
        <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:1.25rem;padding:1.5rem;
                    box-shadow:0 2px 12px rgba(0,0,0,.04);">
            <h2 style="font-size:1rem;font-weight:700;color:#111827;margin:0 0 1.25rem;">👤 Personal Information</h2>
            <div id="ep-info-rows" style="display:flex;flex-direction:column;gap:.75rem;"></div>
        </div>

        {{-- Account info --}}
        <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:1.25rem;padding:1.5rem;
                    box-shadow:0 2px 12px rgba(0,0,0,.04);">
            <h2 style="font-size:1rem;font-weight:700;color:#111827;margin:0 0 1.25rem;">🔐 Account Details</h2>
            <div id="ep-account-rows" style="display:flex;flex-direction:column;gap:.75rem;"></div>
        </div>
    </div>

    {{-- Recent assignments --}}
    <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:1.25rem;padding:1.5rem;
                box-shadow:0 2px 12px rgba(0,0,0,.04);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
            <h2 style="font-size:1rem;font-weight:700;color:#111827;margin:0;">🔧 Recent Assignments</h2>
            <a href="{{ route('engineer.complaints.index') }}"
               style="font-size:.8125rem;font-weight:600;color:#0ea5e9;text-decoration:none;">View all →</a>
        </div>
        <div id="ep-recent"></div>
    </div>

</div>

<style>
@keyframes epulse{0%,100%{opacity:1}50%{opacity:.4}}
</style>

<script>
const EP_STATUS = {
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
        const [meRes, assignRes, compRes] = await Promise.all([
            axios.get('/api/v1/me'),
            axios.get('/api/v1/engineer/complaints', {params:{page:1, per_page:100}}),
            axios.get('/api/v1/engineer/complaints', {params:{page:1, per_page:100, completed:1}}),
        ]);

        const u = meRes.data.data;
        const assignments = assignRes.data.data ?? [];
        const completed   = compRes.data.data ?? [];
        const totalMeta   = assignRes.data.meta ?? {};

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
        const wrap     = document.getElementById('ep-avatar-wrap');
        const photoUrl = u.profile_photo_url || '';
        const initials = (u.name ?? 'E')[0].toUpperCase();

        const avatarContent = photoUrl
            ? `<img src="${esc(photoUrl)}" data-src="${esc(photoUrl)}" alt="avatar"
                    onclick="puOpenLightbox(this.dataset.src)"
                    style="width:98px;height:98px;border-radius:50%;object-fit:cover;
                           border:4px solid #fff;box-sizing:border-box;
                           box-shadow:0 4px 16px rgba(0,0,0,.12);display:block;cursor:zoom-in;">`
            : `<div style="width:98px;height:98px;border-radius:50%;box-sizing:border-box;
                   background:linear-gradient(135deg,#0ea5e9,#6366f1);
                   border:4px solid #fff;box-shadow:0 4px 16px rgba(14,165,233,.25);
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

        document.getElementById('ep-name').textContent  = u.name ?? '—';
        document.getElementById('ep-email').textContent = u.email ?? '—';
        document.getElementById('ep-role-badge').innerHTML =
            `<span style="font-size:.73rem;font-weight:700;background:#e0f2fe;color:#0284c7;
                          padding:.2rem .75rem;border-radius:9999px;">ENGINEER</span>`;
        if (u.auth_provider && u.auth_provider !== 'local') {
            document.getElementById('ep-provider-badge').innerHTML =
                `<span style="font-size:.73rem;font-weight:700;background:#fef3c7;color:#92400e;
                              padding:.2rem .75rem;border-radius:9999px;">via ${u.auth_provider}</span>`;
        }

        // ── Stats
        const inProgress = assignments.filter(c => c.status === 'in_progress').length;
        const awaiting   = assignments.filter(c => c.status === 'awaiting_verification').length;
        const stats = [
            {label:'Active Assignments', value: assignments.length, icon:'📋', color:'#0284c7', bg:'#e0f2fe'},
            {label:'In Progress',        value: inProgress,         icon:'🔧', color:'#0c4a6e', bg:'#bae6fd'},
            {label:'Completed',          value: completed.length,   icon:'✅', color:'#14532d', bg:'#f0fdf4'},
        ];
        document.getElementById('ep-stats-row').innerHTML = stats.map(s => `
            <div style="background:${s.bg};border-radius:1rem;padding:1.25rem;text-align:center;">
                <div style="font-size:1.75rem;margin-bottom:.375rem;">${s.icon}</div>
                <div style="font-size:1.625rem;font-weight:900;color:${s.color};line-height:1;">${s.value}</div>
                <div style="font-size:.75rem;font-weight:600;color:${s.color};opacity:.75;margin-top:.25rem;">${s.label}</div>
            </div>`).join('');

        // ── Personal info
        document.getElementById('ep-info-rows').innerHTML =
            row('Full Name', u.name) +
            row('Phone',     u.phone  ?? 'Not set') +
            row('Gender',    u.gender ? u.gender.charAt(0).toUpperCase() + u.gender.slice(1) : 'Not set');

        // ── Account rows
        document.getElementById('ep-account-rows').innerHTML =
            row('Member Since',    fmt(u.created_at)) +
            row('Auth Method',     u.auth_provider ?? 'Email/Password') +
            row('Account Status',  u.is_active ? '🟢 Active' : '🔴 Inactive');

        // ── Recent assignments
        const rc = document.getElementById('ep-recent');
        const all = [...assignments, ...completed].slice(0, 6);
        if (!all.length) {
            rc.innerHTML = `<div style="text-align:center;padding:2rem;color:#9ca3af;font-size:.875rem;">
                No assignments yet.</div>`;
        } else {
            rc.innerHTML = all.map(c => {
                const s = EP_STATUS[c.status] ?? {label:c.status, color:'#374151', bg:'#f3f4f6'};
                return `<a href="/engineer/complaints/${c.id}"
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

        document.getElementById('profile-loading').style.display = 'none';
        document.getElementById('profile-content').style.display = 'block';

    } catch(e) {
        document.getElementById('profile-loading').innerHTML =
            `<div style="text-align:center;padding:4rem;color:#9ca3af;">
                <div style="font-size:2.5rem;margin-bottom:1rem;">⚠️</div>
                <p>Could not load profile. <a href="" style="color:#0ea5e9;">Retry</a></p>
            </div>`;
    }
}

document.addEventListener('DOMContentLoaded', loadProfile);
</script>

@include('partials.profile-upload-ui')

@endsection

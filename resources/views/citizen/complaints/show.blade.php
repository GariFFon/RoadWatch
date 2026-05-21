@extends('layouts.citizen')

@section('title', 'Complaint Detail')

@section('content')

{{-- ══════════════════════════════════════════════════════
     STYLES
══════════════════════════════════════════════════════ --}}
<style>
@keyframes fadeInUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.45}}
@keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}
@keyframes spin{to{transform:rotate(360deg)}}
@keyframes starPop{0%{transform:scale(0)}60%{transform:scale(1.25)}100%{transform:scale(1)}}

/* ── Base card ── */
.sd-card{
  background:#fff;
  border:1px solid rgba(228,232,246,.9);
  border-radius:1.375rem;
  padding:1.5rem;
  box-shadow:0 2px 12px rgba(15,14,26,.05),0 1px 3px rgba(15,14,26,.04);
  transition:box-shadow .25s;
}
.sd-card:hover{box-shadow:0 6px 24px rgba(15,14,26,.08);}

/* ── Section title ── */
.sd-sec-title{font-size:.9375rem;font-weight:700;color:#0f0e1a;margin:0;display:flex;align-items:center;gap:.5rem;}

/* ── Skeleton shimmer ── */
.sd-skeleton{
  background:linear-gradient(90deg,#f0f3fb 25%,#e4eaf6 50%,#f0f3fb 75%);
  background-size:200% 100%;animation:shimmer 1.5s infinite;border-radius:1rem;
}

/* ── Status badge ── */
.sd-status{display:inline-flex;align-items:center;gap:.35rem;padding:.275rem .75rem;border-radius:9999px;font-size:.75rem;font-weight:700;}
.sd-status-dot{width:6px;height:6px;border-radius:50%;flex-shrink:0;}

/* ── Meta pill ── */
.sd-pill{display:inline-flex;align-items:center;gap:.25rem;padding:.25rem .625rem;border-radius:9999px;font-size:.7rem;font-weight:700;}

/* ── Progress pipeline ── */
.sd-pipe{display:flex;align-items:center;margin-top:1.25rem;position:relative;}
.sd-pipe-item{display:flex;flex-direction:column;align-items:center;flex:1;position:relative;}
.sd-pipe-circle{
  width:48px;height:48px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;font-size:1.125rem;
  position:relative;z-index:1;transition:all .3s;
}
.sd-pipe-circle.done{
  background:linear-gradient(135deg,#6366f1,#8b5cf6);
  box-shadow:0 4px 14px rgba(99,102,241,.35);
}
.sd-pipe-circle.current{
  background:linear-gradient(135deg,#6366f1,#8b5cf6);
  box-shadow:0 0 0 5px rgba(99,102,241,.18),0 4px 14px rgba(99,102,241,.3);
  animation:currentPulse 2s infinite;
}
@keyframes currentPulse{0%,100%{box-shadow:0 0 0 5px rgba(99,102,241,.18),0 4px 14px rgba(99,102,241,.3)}50%{box-shadow:0 0 0 9px rgba(99,102,241,.1),0 4px 14px rgba(99,102,241,.2)}}
.sd-pipe-circle.pending{
  background:#f3f4f6;border:2px solid #e5e7eb;
}
.sd-pipe-label{font-size:.7rem;font-weight:600;margin-top:.5rem;text-align:center;line-height:1.3;}
.sd-pipe-label.done,.sd-pipe-label.current{color:#4f46e5;}
.sd-pipe-label.pending{color:#9ca3af;}
.sd-pipe-connector{flex:1;height:3px;border-radius:9999px;margin-bottom:1.5rem;margin-top:1.5px;position:absolute;top:23px;left:calc(50% + 28px);right:calc(-50% + 28px);}
.sd-pipe-connector.done{background:linear-gradient(90deg,#6366f1,#8b5cf6);}
.sd-pipe-connector.pending{background:#e5e7eb;}

/* ── Media gallery ── */
.sd-media-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-top:1rem;}
.sd-media-thumb{aspect-ratio:4/3;border-radius:.875rem;overflow:hidden;border:1.5px solid #e5e7eb;position:relative;transition:all .2s;}
.sd-media-thumb:hover{border-color:#c4b5fd;box-shadow:0 4px 16px rgba(99,102,241,.15);}
.sd-media-thumb img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .25s;}
.sd-media-thumb:hover img{transform:scale(1.04);}

/* ── Details table ── */
.sd-detail-row{
  display:flex;justify-content:space-between;align-items:center;
  padding:.5625rem 0;font-size:.8125rem;
}
.sd-detail-row:not(:last-child){border-bottom:1px solid #f8faff;}
.sd-detail-key{color:#6b7280;font-weight:500;}
.sd-detail-val{color:#0f0e1a;font-weight:600;text-align:right;}

/* ── Timeline ── */
.sd-tl-item{display:flex;gap:.875rem;align-items:flex-start;position:relative;}
.sd-tl-item:not(:last-child)::before{
  content:'';position:absolute;left:15px;top:33px;bottom:-12px;
  width:2px;background:linear-gradient(180deg,#e5e7eb 0%,transparent 100%);
}
.sd-tl-dot{
  width:32px;height:32px;border-radius:50%;border:3px solid #e5e7eb;
  background:#fff;flex-shrink:0;z-index:1;
  display:flex;align-items:center;justify-content:center;font-size:.8125rem;
}
.sd-tl-content{flex:1;min-width:0;padding-top:.125rem;}
.sd-tl-title{font-size:.8125rem;font-weight:700;color:#0f0e1a;}
.sd-tl-time{font-size:.72rem;color:#9ca3af;margin-top:.125rem;}
.sd-tl-note{font-size:.78rem;color:#6b7280;margin-top:.25rem;font-style:italic;}

/* ── Star rating ── */
.sd-star-row{display:flex;gap:.25rem;justify-content:center;}
.sd-star{
  font-size:2.125rem;cursor:pointer;
  transition:transform .15s,color .15s;
  color:#d1d5db;user-select:none;
}
.sd-star.filled,.sd-star:hover,.sd-star.hover{color:#f59e0b;}
.sd-star:hover{transform:scale(1.2);}
.sd-star.pop{animation:starPop .3s ease;}
.sd-star-label{text-align:center;font-size:.8rem;font-weight:700;color:#6b7280;margin:.375rem 0 .875rem;min-height:1.2em;}

/* ── Feedback card ── */
.sd-feedback-card{
  background:#f0fdf4;border:1.5px solid #86efac;border-radius:1.25rem;padding:1.375rem;
  box-shadow:0 2px 8px rgba(16,185,129,.08);
}
.sd-feedback-card.no-feedback{
  background:linear-gradient(135deg,#f0fdf4,#dcfce7);
}

/* ── Sealed ratings ── */
.sd-sealed{
  background:linear-gradient(135deg,#fffbeb,#fef9c3);
  border:1.5px solid #fcd34d;border-radius:1.25rem;padding:1.375rem;
}

/* ── Back link ── */
.sd-back{
  display:inline-flex;align-items:center;gap:.375rem;
  font-size:.875rem;color:#6b7280;text-decoration:none;
  margin-bottom:1.5rem;padding:.4rem .75rem;border-radius:.75rem;
  transition:all .18s;background:rgba(255,255,255,.7);
  border:1px solid rgba(255,255,255,.9);backdrop-filter:blur(8px);
}
.sd-back:hover{color:#4f46e5;background:#eef2ff;border-color:#c4b5fd;}

/* ── Map ── */
.sd-map{
  height:240px;border-radius:1rem;border:1.5px solid #e5e7eb;
  position:relative;z-index:1;overflow:hidden;
  box-shadow:inset 0 0 0 1px rgba(255,255,255,.5);
  transition:border-color .2s;
}
.sd-map:hover{border-color:#c4b5fd;}

/* ── Submit button ── */
.sd-btn{
  display:inline-flex;align-items:center;justify-content:center;gap:.5rem;
  border:none;border-radius:.875rem;
  padding:.625rem 1.25rem;font-size:.875rem;font-weight:700;
  cursor:pointer;transition:all .2s;font-family:inherit;
}
.sd-btn-green{
  background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;
  box-shadow:0 4px 14px rgba(22,163,74,.25);
}
.sd-btn-green:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(22,163,74,.35);}
.sd-btn-green:disabled{background:#86efac;cursor:not-allowed;transform:none;box-shadow:none;}
.sd-btn-indigo{
  background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;
  box-shadow:0 4px 14px rgba(99,102,241,.25);
}
.sd-btn-indigo:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(99,102,241,.35);}

/* ── Grid ── */
.sd-grid{display:grid;grid-template-columns:1fr 330px;gap:1.375rem;align-items:start;}
.sd-left{display:flex;flex-direction:column;gap:1.25rem;}
.sd-right{display:flex;flex-direction:column;gap:1.25rem;}

@@media(max-width:768px){.sd-grid{grid-template-columns:1fr !important;}}
@media(max-width:640px){.sd-media-grid{grid-template-columns:repeat(2,1fr);}}
</style>

{{-- Back link --}}
<a href="{{ route('citizen.complaints.index') }}" class="sd-back">
    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
    Back to My Complaints
</a>

{{-- Loading skeleton --}}
<div id="loading-state" style="display:flex;flex-direction:column;gap:1.25rem;">
    <div class="sd-skeleton" style="height:160px;"></div>
    <div class="sd-skeleton" style="height:100px;"></div>
    <div class="sd-skeleton" style="height:80px;"></div>
</div>

{{-- Error state --}}
<div id="error-state" style="display:none;text-align:center;padding:4rem 2rem;">
    <div style="width:70px;height:70px;border-radius:1.375rem;background:linear-gradient(135deg,#fef2f2,#fee2e2);display:flex;align-items:center;justify-content:center;font-size:1.875rem;margin:0 auto 1.25rem;border:1px solid #fca5a5;">⚠️</div>
    <h2 style="color:#0f0e1a;font-size:1.25rem;font-weight:700;margin:0 0 .5rem;">Could not load complaint</h2>
    <p style="color:#6b7280;margin:0 0 1.5rem;" id="error-msg">An error occurred.</p>
    <button onclick="loadComplaint()" class="sd-btn sd-btn-indigo">Retry</button>
</div>

{{-- Main content --}}
<div id="complaint-content" style="display:none;animation:fadeInUp .4s ease;">
    <div class="sd-grid" id="detail-grid">

        {{-- LEFT COLUMN --}}
        <div class="sd-left">
            <div class="sd-card" id="card-header"></div>
            <div class="sd-card" id="card-pipeline"></div>
            <div class="sd-card" id="card-media" style="display:none;"></div>
            <div class="sd-card">
                <h2 class="sd-sec-title">
                    <svg width="16" height="16" fill="none" stroke="#6366f1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Location on Map
                </h2>
                <div id="detail-map" class="sd-map" style="margin-top:1rem;"></div>
            </div>
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="sd-right">
            <div class="sd-card" id="card-details"></div>
            <div class="sd-card" id="card-timeline"></div>
            <div id="card-feedback"></div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════════════════ --}}
<script>
window.COMPLAINT_ID = {{ $complaintId }};

window.STATUS_CONFIG = {
    pending:               { label:'Pending',               color:'#92400e', bg:'#fffbeb', border:'#fcd34d',  dot:'#f59e0b', step:'⏳' },
    under_review:          { label:'Under Review',          color:'#1e40af', bg:'#eff6ff', border:'#93c5fd',  dot:'#3b82f6', step:'🔍' },
    in_progress:           { label:'In Progress',           color:'#1d4ed8', bg:'#dbeafe', border:'#60a5fa',  dot:'#2563eb', step:'🔧' },
    awaiting_verification: { label:'Awaiting Verification', color:'#9a3412', bg:'#fff7ed', border:'#fdba74',  dot:'#f97316', step:'🕐' },
    resolved:              { label:'Resolved',              color:'#14532d', bg:'#f0fdf4', border:'#86efac',  dot:'#22c55e', step:'✅' },
    verified:              { label:'Verified',              color:'#14532d', bg:'#f0fdf4', border:'#86efac',  dot:'#16a34a', step:'🏆' },
    rejected:              { label:'Rejected',              color:'#7f1d1d', bg:'#fef2f2', border:'#fca5a5',  dot:'#ef4444', step:'❌' },
};
window.SEV_CONFIG = {
    low:       { label:'Low',       color:'#059669', bg:'#ecfdf5', dot:'#10b981' },
    medium:    { label:'Medium',    color:'#d97706', bg:'#fffbeb', dot:'#f59e0b' },
    high:      { label:'High',      color:'#c2410c', bg:'#fff7ed', dot:'#f97316' },
    emergency: { label:'Emergency', color:'#dc2626', bg:'#fef2f2', dot:'#ef4444' },
};
window.PIPELINE = [
    { key:'pending',               label:'Submitted',   icon:'📋' },
    { key:'under_review',          label:'Reviewing',   icon:'🔍' },
    { key:'in_progress',           label:'In Progress', icon:'🔧' },
    { key:'awaiting_verification', label:'Verifying',   icon:'🕐' },
    { key:'resolved',              label:'Resolved',    icon:'✅' },
];
window.leafletMap = null;

function esc(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function timeAgo(iso) {
    const diff=Date.now()-new Date(iso);
    const m=Math.floor(diff/60000),h=Math.floor(m/60),d=Math.floor(h/24);
    return d>0?`${d}d ago`:h>0?`${h}h ago`:m>0?`${m}m ago`:'just now';
}
function fmtDate(iso) {
    return new Date(iso).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
}
function capWords(str) {
    return str.replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase());
}

window.loadComplaint = async function() {
    document.getElementById('loading-state').style.display    = 'flex';
    document.getElementById('error-state').style.display      = 'none';
    document.getElementById('complaint-content').style.display= 'none';
    try {
        const res = await axios.get(`/api/v1/citizen/complaints/${window.COMPLAINT_ID}`);
        const c   = res.data.data;
        renderAll(c);
        document.getElementById('loading-state').style.display     = 'none';
        document.getElementById('complaint-content').style.display = 'block';
    } catch (err) {
        document.getElementById('loading-state').style.display = 'none';
        document.getElementById('error-state').style.display   = 'block';
        document.getElementById('error-msg').textContent =
            err.response?.status === 403 ? 'You do not have access to this complaint.' :
            err.response?.status === 404 ? 'Complaint not found.' :
            'Failed to load. Please try again.';
    }
};

function renderAll(c) {
    document.title = c.title + ' — RoadWatch';
    const s   = window.STATUS_CONFIG[c.status] ?? { label:c.status, color:'#374151', bg:'#f3f4f6', border:'#d1d5db', dot:'#9ca3af' };
    const sev = window.SEV_CONFIG[c.severity]  ?? { label:c.severity, color:'#374151', bg:'#f3f4f6', dot:'#9ca3af' };

    /* ── Header card ──────────────────────────────────────────────────────── */
    document.getElementById('card-header').innerHTML = `
        <div style="display:flex;align-items:flex-start;gap:1rem;">
            <div style="width:56px;height:56px;border-radius:1rem;background:linear-gradient(135deg,#f5f3ff,#eef2ff);
                        display:flex;align-items:center;justify-content:center;font-size:2rem;
                        flex-shrink:0;border:1.5px solid #ddd6fe;box-shadow:0 3px 10px rgba(99,102,241,.12);">
                ${c.category?.icon ?? '📌'}
            </div>
            <div style="flex:1;min-width:0;">
                <div style="display:flex;flex-wrap:wrap;gap:.4375rem;margin-bottom:.625rem;align-items:center;">
                    <span style="font-size:.7rem;font-family:monospace;background:#f3f4f6;color:#6b7280;padding:.2rem .625rem;border-radius:9999px;letter-spacing:.03em;">${esc(c.complaint_number)}</span>
                    <span class="sd-status" style="background:${s.bg};color:${s.color};border:1px solid ${s.border};">
                        <span class="sd-status-dot" style="background:${s.dot};"></span>
                        ${esc(s.label)}
                    </span>
                    <span class="sd-pill" style="background:${sev.bg};color:${sev.color};">
                        <span style="width:5px;height:5px;border-radius:50%;background:${sev.dot};"></span>
                        ${esc(sev.label)}
                    </span>
                    ${c.is_anonymous ? `<span class="sd-pill" style="background:#f3f4f6;color:#6b7280;">👤 Anonymous</span>` : ''}
                </div>
                <h1 style="font-size:1.25rem;font-weight:800;color:#0f0e1a;margin:0 0 .375rem;letter-spacing:-.02em;line-height:1.3;">${esc(c.title)}</h1>
                <p style="font-size:.8125rem;color:#6b7280;margin:0;display:flex;flex-wrap:wrap;gap:.5rem;align-items:center;">
                    <span style="display:flex;align-items:center;gap:.25rem;">
                        <svg width="12" height="12" fill="none" stroke="#9ca3af" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        ${esc(c.location)}
                    </span>
                    <span style="color:#e5e7eb;">·</span>
                    <span>${esc(c.category?.name ?? '')}</span>
                    <span style="color:#e5e7eb;">·</span>
                    <span>${timeAgo(c.created_at)}</span>
                </p>
            </div>
        </div>
        <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid #f3f4f6;font-size:.875rem;color:#374151;line-height:1.75;">
            ${esc(c.description)}
        </div>`;

    /* ── Progress pipeline ───────────────────────────────────────────────── */
    const isRejected  = c.status === 'rejected';
    const currentStep = window.PIPELINE.findIndex(p => p.key === c.status);

    if (isRejected) {
        document.getElementById('card-pipeline').innerHTML = `
            <h2 class="sd-sec-title">
                <svg width="16" height="16" fill="none" stroke="#dc2626" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Complaint Status
            </h2>
            <div style="background:linear-gradient(135deg,#fef2f2,#fee2e2);border:1.5px solid #fca5a5;border-radius:1rem;padding:1.25rem;margin-top:1rem;display:flex;align-items:center;gap:.875rem;">
                <div style="width:44px;height:44px;border-radius:.875rem;background:#fee2e2;display:flex;align-items:center;justify-content:center;font-size:1.375rem;flex-shrink:0;">❌</div>
                <div>
                    <div style="font-size:.9375rem;font-weight:700;color:#7f1d1d;">Complaint Rejected</div>
                    <p style="font-size:.8125rem;color:#991b1b;margin:.25rem 0 0;">Please contact support if you have questions about this decision.</p>
                </div>
            </div>`;
    } else {
        const stepsHtml = window.PIPELINE.map((step, idx) => {
            const done    = currentStep >= idx && currentStep >= 0;
            const current = currentStep === idx;
            const cls     = current ? 'current' : done ? 'done' : 'pending';
            const connector = idx < window.PIPELINE.length - 1
                ? `<div class="sd-pipe-connector ${done ? 'done' : 'pending'}"></div>` : '';
            return `<div class="sd-pipe-item">
                <div class="sd-pipe-circle ${cls}">${done ? step.icon : '<svg width="14" height="14" fill="none" stroke="#d1d5db" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5" stroke-width="2.5"/></svg>'}</div>
                <p class="sd-pipe-label ${cls}">${step.label}</p>
                ${connector}
            </div>`;
        }).join('');
        document.getElementById('card-pipeline').innerHTML =
            `<h2 class="sd-sec-title">
                <svg width="16" height="16" fill="none" stroke="#6366f1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Progress
            </h2>
            <div class="sd-pipe" style="margin-top:1.375rem;">${stepsHtml}</div>`;
    }

    /* ── Media gallery ───────────────────────────────────────────────────── */
    if (c.media && c.media.length > 0) {
        const mediaHtml = c.media.map(m => m.file_type === 'image'
            ? `<a href="${m.cloud_url}" target="_blank" class="sd-media-thumb">
                   <img src="${m.cloud_url}" alt="${esc(m.original_name)}">
               </a>`
            : `<div class="sd-media-thumb" style="background:#1f2937;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.5rem;">
                   <div style="font-size:2rem;">🎥</div>
                   <a href="${m.cloud_url}" target="_blank" style="font-size:.75rem;color:#60a5fa;">Watch video</a>
               </div>`
        ).join('');
        const card = document.getElementById('card-media');
        card.innerHTML = `
            <h2 class="sd-sec-title">
                <svg width="16" height="16" fill="none" stroke="#6366f1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Attached Media
                <span style="font-size:.72rem;font-weight:600;color:#9ca3af;margin-left:.25rem;">(${c.media.length} file${c.media.length!==1?'s':''})</span>
            </h2>
            <div class="sd-media-grid">${mediaHtml}</div>`;
        card.style.display = 'block';
    }

    /* ── Leaflet map ─────────────────────────────────────────────────────── */
    if (!window.leafletMap) {
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl:'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
            iconUrl:      'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
            shadowUrl:    'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
        });
        window.leafletMap = L.map('detail-map', { zoomControl:true, dragging:false, scrollWheelZoom:false })
                    .setView([c.latitude, c.longitude], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution:'© OpenStreetMap' }).addTo(window.leafletMap);
        L.marker([c.latitude, c.longitude]).addTo(window.leafletMap)
         .bindPopup(`<strong>${esc(c.title)}</strong><br>${esc(c.location)}`).openPopup();
    }

    /* ── Details sidebar ─────────────────────────────────────────────────── */
    const rows = [
        { key:'Category',  val:c.category?.name ?? '—' },
        { key:'Severity',  val:`<span class="sd-pill" style="background:${sev.bg};color:${sev.color};">${esc(sev.label)}</span>` },
        { key:'Status',    val:`<span class="sd-pill" style="background:${s.bg};color:${s.color};">${esc(s.label)}</span>` },
        { key:'Submitted', val:fmtDate(c.created_at) },
        { key:'Views',     val:`${c.views_count ?? 0}` },
        { key:'Upvotes',   val:`${c.votes_count ?? 0}` },
        ...(c.resolved_at ? [{ key:'Resolved', val:fmtDate(c.resolved_at) }] : []),
    ];
    document.getElementById('card-details').innerHTML = `
        <h2 class="sd-sec-title">
            <svg width="16" height="16" fill="none" stroke="#6366f1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Details
        </h2>
        <div style="margin-top:.875rem;">
            ${rows.map(r => `
            <div class="sd-detail-row">
                <span class="sd-detail-key">${r.key}</span>
                <span class="sd-detail-val">${r.val}</span>
            </div>`).join('')}
        </div>`;

    /* ── Status timeline ─────────────────────────────────────────────────── */
    const histories = c.status_histories ?? [];
    const tlItems = histories.length === 0
        ? `<p style="font-size:.8125rem;color:#9ca3af;margin-top:.875rem;">No status changes yet.</p>`
        : `<div style="display:flex;flex-direction:column;gap:1rem;margin-top:1rem;position:relative;">
            ${histories.map((h, i) => {
                const hs = window.STATUS_CONFIG[h.new_status] ?? { border:'#e5e7eb', step:'•' };
                return `<div class="sd-tl-item" style="margin-bottom:${i<histories.length-1?'.75rem':'0'};">
                    <div class="sd-tl-dot" style="border-color:${hs.border};">${hs.step}</div>
                    <div class="sd-tl-content">
                        <div class="sd-tl-title">${h.old_status ? capWords(h.old_status) + ' → ' + capWords(h.new_status) : '📋 Complaint Filed'}</div>
                        <div class="sd-tl-time">${fmtDate(h.created_at)}</div>
                        ${h.notes ? `<div class="sd-tl-note">"${esc(h.notes)}"</div>` : ''}
                    </div>
                </div>`;
            }).join('')}
           </div>`;
    document.getElementById('card-timeline').innerHTML = `
        <h2 class="sd-sec-title">
            <svg width="16" height="16" fill="none" stroke="#6366f1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Status History
        </h2>
        ${tlItems}`;

    /* ── Ratings & Feedback ──────────────────────────────────────────────── */
    if (c.status === 'verified') {
        if (c.ratings_locked) {
            const er = c.engineer_rating, fb = c.feedback;
            const erStars = er ? '★'.repeat(er.score)+'☆'.repeat(5-er.score) : '—';
            const fbStars = fb ? '★'.repeat(fb.rating)+'☆'.repeat(5-fb.rating) : '—';
            document.getElementById('card-feedback').innerHTML = `
                <div class="sd-sealed">
                    <div style="text-align:center;margin-bottom:1rem;">
                        <div style="font-size:1.5rem;margin-bottom:.25rem;">🔒</div>
                        <div style="font-size:.9375rem;font-weight:700;color:#92400e;">All Ratings Sealed &amp; Final</div>
                        <p style="font-size:.78rem;color:#92400e;margin:.25rem 0 0;">Both admin and citizen have submitted their ratings.</p>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                        ${er ? `<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:.875rem;padding:.875rem;">
                            <div style="font-size:.72rem;font-weight:700;color:#14532d;margin-bottom:.25rem;">⭐ Engineer Work</div>
                            <div style="font-size:1.25rem;color:#f59e0b;">${erStars}</div>
                            <div style="font-size:.8rem;font-weight:700;color:#374151;margin-top:.2rem;">${esc(er.emoji)} ${esc(er.label)} (${er.score}/5)</div>
                            ${er.comment ? `<div style="font-size:.75rem;color:#6b7280;font-style:italic;margin-top:.25rem;">"${esc(er.comment)}"</div>` : ''}
                        </div>` : ''}
                        ${fb ? `<div style="background:#eef2ff;border:1px solid #a5b4fc;border-radius:.875rem;padding:.875rem;">
                            <div style="font-size:.72rem;font-weight:700;color:#3730a3;margin-bottom:.25rem;">💬 Your Feedback</div>
                            <div style="font-size:1.25rem;color:#f59e0b;">${fbStars}</div>
                            <div style="font-size:.8rem;font-weight:700;color:#374151;margin-top:.2rem;">${esc(fb.emoji)} ${esc(fb.label)} (${fb.rating}/5)</div>
                            ${fb.comment ? `<div style="font-size:.75rem;color:#6b7280;font-style:italic;margin-top:.25rem;">"${esc(fb.comment)}"</div>` : ''}
                        </div>` : ''}
                    </div>
                </div>`;
            return;
        }

        // Engineer rating (admin) shown but not locked
        if (c.engineer_rating) {
            const r = c.engineer_rating;
            const adminStars = '★'.repeat(r.score) + '☆'.repeat(5 - r.score);
            document.getElementById('card-feedback').insertAdjacentHTML('beforebegin', `
                <div class="sd-card" style="border-color:#fde68a;background:linear-gradient(135deg,#fffbeb,#fef9c3);">
                    <h2 class="sd-sec-title" style="color:#92400e;">⭐ Engineer Work Quality</h2>
                    <div style="display:flex;align-items:center;gap:1rem;margin-top:.875rem;flex-wrap:wrap;">
                        <div style="font-size:1.875rem;color:#f59e0b;letter-spacing:.05em;">${adminStars}</div>
                        <div>
                            <div style="font-size:.9rem;font-weight:700;color:#374151;">${esc(r.emoji)} ${esc(r.label)} (${r.score}/5)</div>
                            ${r.comment ? `<div style="font-size:.8rem;color:#6b7280;font-style:italic;margin-top:.2rem;">"${esc(r.comment)}"</div>` : ''}
                            <div style="font-size:.72rem;color:#9ca3af;margin-top:.2rem;">Rated by ${esc(r.rated_by?.name ?? 'Admin')}</div>
                        </div>
                    </div>
                </div>`);
        }
    }

    /* ── Citizen feedback ────────────────────────────────────────────────── */
    if (c.status === 'verified' || c.status === 'resolved') {
        const LABELS = [null,'Very Poor','Poor','Average','Good','Excellent'];
        const EMOJIS = [null,'😡','😞','😐','😊','😍'];
        let html = '';
        if (c.feedback) {
            const fb    = c.feedback;
            const stars = '★'.repeat(fb.rating) + '☆'.repeat(5 - fb.rating);
            html = `<div class="sd-feedback-card">
                <h2 class="sd-sec-title" style="color:#15803d;margin-bottom:.875rem;">
                    <svg width="16" height="16" fill="none" stroke="#15803d" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    Your Feedback
                </h2>
                <div style="font-size:1.5rem;color:#f59e0b;">${stars}</div>
                <p style="font-size:.875rem;color:#166534;font-weight:700;margin:.25rem 0 0;">${EMOJIS[fb.rating]} ${LABELS[fb.rating]} — ${fb.rating}/5</p>
                ${fb.comment ? `<p style="font-size:.8125rem;color:#15803d;font-style:italic;margin:.5rem 0 0;">"${esc(fb.comment)}"</p>` : ''}
                <p style="font-size:.72rem;color:#9ca3af;margin:.5rem 0 .875rem;">You can update your rating below.</p>
                <div style="display:flex;gap:.25rem;margin-bottom:.625rem;" id="stars-row">
                    ${[1,2,3,4,5].map(i=>`<span class="sd-star ${i<=fb.rating?'filled':''}" data-val="${i}" onclick="handleStarClick(${i})">
                        ${i<=fb.rating?'★':'☆'}
                    </span>`).join('')}
                </div>
                <textarea id="feedback-comment" rows="2" placeholder="Update comment (optional)..."
                    style="width:100%;border:1.5px solid #86efac;border-radius:.875rem;padding:.625rem;
                           font-size:.8125rem;resize:none;outline:none;background:#fff;
                           font-family:inherit;box-sizing:border-box;transition:border-color .2s;"
                    onfocus="this.style.borderColor='#4ade80'" onblur="this.style.borderColor='#86efac'">${esc(fb.comment??'')}</textarea>
                <button onclick="submitFeedback()" id="feedback-btn" class="sd-btn sd-btn-green" style="margin-top:.625rem;width:100%;">
                    ⭐ Update Feedback
                </button>
                <span id="feedback-msg" style="font-size:.78rem;font-weight:600;display:none;margin-top:.5rem;text-align:center;display:none;"></span>
            </div>`;
            window.selectedRating = fb.rating;
        } else {
            html = `<div class="sd-feedback-card no-feedback">
                <h2 class="sd-sec-title" style="color:#15803d;margin-bottom:.875rem;justify-content:center;">
                    💬 Rate the Resolution
                </h2>
                <p style="font-size:.875rem;color:#166534;margin:0 0 1rem;text-align:center;">How satisfied are you with how this was handled?</p>
                <div class="sd-star-row" id="stars-row">
                    ${[1,2,3,4,5].map(i=>`<span class="sd-star" data-val="${i}" onclick="handleStarClick(${i})">☆</span>`).join('')}
                </div>
                <p id="star-label" class="sd-star-label"></p>
                <textarea id="feedback-comment" rows="3" placeholder="Optional comment (what went well, what could improve?)..."
                    style="width:100%;border:1.5px solid #86efac;border-radius:.875rem;padding:.75rem;
                           font-size:.8125rem;resize:none;outline:none;background:#fff;
                           font-family:inherit;box-sizing:border-box;transition:border-color .2s;"
                    onfocus="this.style.borderColor='#4ade80'" onblur="this.style.borderColor='#86efac'"></textarea>
                <p id="feedback-error" style="color:#dc2626;font-size:.8125rem;display:none;margin:.375rem 0 0;font-weight:500;">Please select a star rating first.</p>
                <button onclick="submitFeedback()" id="feedback-btn" class="sd-btn sd-btn-green" style="margin-top:.875rem;width:100%;">
                    ⭐ Submit Feedback
                </button>
            </div>`;
        }
        document.getElementById('card-feedback').innerHTML = html;
    }
}

/* ── Star rating interactions ──────────────────────────────────────────────── */
window.selectedRating = window.selectedRating || 0;
const STAR_LABELS_SHOW = [null,'Very Poor 😡','Poor 😞','Average 😐','Good 😊','Excellent 😍'];

window.handleStarClick = function(n) {
    window.selectedRating = n;
    document.querySelectorAll('.sd-star').forEach((s, i) => {
        s.textContent     = i < n ? '★' : '☆';
        s.classList.toggle('filled', i < n);
        s.style.transform = i < n ? 'scale(1.05)' : 'scale(1)';
        if (i === n - 1) { s.classList.add('pop'); setTimeout(() => s.classList.remove('pop'), 300); }
    });
    const lbl = document.getElementById('star-label');
    if (lbl) { lbl.textContent = STAR_LABELS_SHOW[n] || ''; lbl.style.color = '#0f0e1a'; }
    const err = document.getElementById('feedback-error');
    if (err) err.style.display = 'none';
};

/* ── Submit feedback ───────────────────────────────────────────────────────── */
window.submitFeedback = async function() {
    if (!window.selectedRating) {
        const err = document.getElementById('feedback-error');
        if (err) { err.style.display = 'block'; } return;
    }
    const btn = document.getElementById('feedback-btn');
    btn.disabled = true; btn.innerHTML = `<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="animation:spin .7s linear infinite"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Submitting…`;
    try {
        await axios.post(`/api/v1/citizen/complaints/${window.COMPLAINT_ID}/feedback`, {
            rating:  window.selectedRating,
            comment: document.getElementById('feedback-comment')?.value || null,
        });
        btn.innerHTML = '✅ Feedback saved!';
        setTimeout(() => window.loadComplaint(), 1000);
    } catch (err) {
        btn.disabled = false; btn.innerHTML = '⭐ Submit Feedback';
        alert(err.response?.data?.message ?? 'Failed to submit feedback.');
    }
};

document.addEventListener('DOMContentLoaded', () => window.loadComplaint());
</script>

@endsection

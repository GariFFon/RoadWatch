@extends('layouts.citizen')

@section('title', 'Complaint Detail')

@section('content')

{{-- Back link --}}
<a href="{{ route('citizen.complaints.index') }}"
   style="display:inline-flex; align-items:center; gap:0.375rem; font-size:0.875rem;
          color:#6b7280; text-decoration:none; margin-bottom:1.5rem;"
   onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#6b7280'">
    ← Back to My Complaints
</a>

{{-- Loading skeleton --}}
<div id="loading-state" style="display:flex; flex-direction:column; gap:1.5rem;">
    <div style="background:#f3f4f6; border-radius:1rem; height:160px; animation:pulse 1.5s infinite;"></div>
    <div style="background:#f3f4f6; border-radius:1rem; height:100px; animation:pulse 1.5s infinite;"></div>
</div>

{{-- Error state --}}
<div id="error-state" style="display:none; text-align:center; padding:4rem 2rem;">
    <div style="font-size:3rem; margin-bottom:1rem;">⚠️</div>
    <h2 style="color:#111827; font-size:1.25rem; margin:0 0 0.5rem;">Could not load complaint</h2>
    <p style="color:#6b7280; margin:0 0 1.5rem;" id="error-msg">An error occurred.</p>
    <button onclick="loadComplaint()" style="background:#4f46e5; color:#fff; border:none; border-radius:0.75rem;
            padding:0.625rem 1.25rem; font-size:0.875rem; font-weight:600; cursor:pointer;">Retry</button>
</div>

{{-- Main content (populated by JS) --}}
<div id="complaint-content" style="display:none;">
    <div style="display:grid; grid-template-columns:1fr 340px; gap:1.5rem; align-items:start;" id="detail-grid">

        {{-- LEFT COLUMN --}}
        <div style="display:flex; flex-direction:column; gap:1.5rem;">

            {{-- Header card --}}
            <div class="rw-card" id="card-header"></div>

            {{-- Progress pipeline --}}
            <div class="rw-card" id="card-pipeline"></div>

            {{-- Media gallery --}}
            <div class="rw-card" id="card-media" style="display:none;"></div>

            {{-- Map --}}
            <div class="rw-card">
                <h2 class="rw-section-title">📍 Location on Map</h2>
                <div id="detail-map" style="height:220px; border-radius:0.75rem; border:1px solid #e5e7eb; margin-top:1rem; z-index:1;"></div>
            </div>
        </div>

        {{-- RIGHT COLUMN --}}
        <div style="display:flex; flex-direction:column; gap:1.5rem;">
            <div class="rw-card" id="card-details"></div>
            <div class="rw-card" id="card-timeline"></div>
            <div id="card-feedback"></div>
        </div>
    </div>
</div>

{{-- Styles --}}
<style>
.rw-card { background:#fff; border:1px solid #e5e7eb; border-radius:1rem; padding:1.375rem; box-shadow:0 1px 3px rgba(0,0,0,0.06); }
.rw-section-title { font-size:0.9375rem; font-weight:700; color:#111827; margin:0; }
@@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.5} }
@@media(max-width:768px) { #detail-grid { grid-template-columns:1fr !important; } }
.star-btn { font-size:1.75rem; cursor:pointer; transition:transform 0.1s; display:inline-block; }
.star-btn:hover { transform:scale(1.2); }
</style>

{{-- JS --}}
<script>
window.COMPLAINT_ID = {{ $complaintId }};

window.STATUS_CONFIG = {
    pending:      { label:'⏳ Pending',       color:'#92400e', bg:'#fffbeb', border:'#fcd34d' },
    under_review: { label:'🔍 Under Review',  color:'#1e40af', bg:'#eff6ff', border:'#93c5fd' },
    in_progress:  { label:'🔧 In Progress',   color:'#1d4ed8', bg:'#dbeafe', border:'#60a5fa' },
    resolved:     { label:'✅ Resolved',      color:'#14532d', bg:'#f0fdf4', border:'#86efac' },
    rejected:     { label:'❌ Rejected',      color:'#7f1d1d', bg:'#fef2f2', border:'#fca5a5' },
};
window.SEV_CONFIG = {
    low:       { label:'🟢 Low',       color:'#065f46', bg:'#d1fae5' },
    medium:    { label:'🟡 Medium',    color:'#713f12', bg:'#fef9c3' },
    high:      { label:'🟠 High',      color:'#7c2d12', bg:'#ffedd5' },
    emergency: { label:'🔴 Emergency', color:'#7f1d1d', bg:'#fee2e2' },
};
window.PIPELINE = [
    { key:'pending',      label:'Submitted',    icon:'📋' },
    { key:'under_review', label:'Under Review', icon:'🔍' },
    { key:'in_progress',  label:'In Progress',  icon:'🔧' },
    { key:'resolved',     label:'Resolved',     icon:'✅' },
];

window.leafletMap = null;

window.loadComplaint = async function() {
    document.getElementById('loading-state').style.display = 'flex';
    document.getElementById('error-state').style.display   = 'none';
    document.getElementById('complaint-content').style.display = 'none';

    try {
        const res = await axios.get(`/api/v1/citizen/complaints/${window.COMPLAINT_ID}`);
        const c   = res.data.data;
        renderAll(c);
        document.getElementById('loading-state').style.display  = 'none';
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
    const s   = window.STATUS_CONFIG[c.status] ?? { label:c.status, color:'#374151', bg:'#f3f4f6', border:'#d1d5db' };
    const sev = window.SEV_CONFIG[c.severity]  ?? { label:c.severity, color:'#374151', bg:'#f3f4f6' };

    // ── Header card ─────────────────────────────────────────────────────────
    document.getElementById('card-header').innerHTML = `
        <div style="display:flex; align-items:flex-start; gap:1rem;">
            <div style="font-size:3rem; line-height:1; flex-shrink:0;">${c.category?.icon ?? '📌'}</div>
            <div style="flex:1; min-width:0;">
                <div style="display:flex; flex-wrap:wrap; gap:0.5rem; margin-bottom:0.625rem;">
                    <span style="font-size:0.7rem; font-family:monospace; background:#f3f4f6; color:#6b7280; padding:0.2rem 0.625rem; border-radius:9999px;">${c.complaint_number}</span>
                    <span style="font-size:0.7rem; font-weight:700; padding:0.25rem 0.75rem; border-radius:9999px; background:${s.bg}; color:${s.color}; border:1px solid ${s.border};">${s.label}</span>
                    <span style="font-size:0.7rem; font-weight:700; padding:0.25rem 0.625rem; border-radius:9999px; background:${sev.bg}; color:${sev.color};">${sev.label}</span>
                    ${c.is_anonymous ? '<span style="font-size:0.7rem; font-weight:600; padding:0.25rem 0.625rem; border-radius:9999px; background:#f3f4f6; color:#6b7280;">👤 Anonymous</span>' : ''}
                </div>
                <h1 style="font-size:1.25rem; font-weight:700; color:#111827; margin:0 0 0.375rem;">${c.title}</h1>
                <p style="font-size:0.8125rem; color:#6b7280; margin:0;">
                    📍 ${c.location} &nbsp;·&nbsp; ${c.category?.name ?? ''} &nbsp;·&nbsp; Submitted ${timeAgo(c.created_at)}
                </p>
            </div>
        </div>
        <div style="margin-top:1.25rem; padding-top:1.25rem; border-top:1px solid #f3f4f6; font-size:0.875rem; color:#374151; line-height:1.7;">
            ${escHtml(c.description)}
        </div>`;

    // ── Progress pipeline ────────────────────────────────────────────────────
    const isRejected  = c.status === 'rejected';
    const currentStep = window.PIPELINE.findIndex(p => p.key === c.status);

    if (isRejected) {
        document.getElementById('card-pipeline').innerHTML = `
            <div style="background:#fef2f2; border:1px solid #fca5a5; border-radius:0.75rem; padding:1.25rem;">
                <h2 style="font-size:0.9375rem; font-weight:700; color:#7f1d1d; margin:0 0 0.25rem;">❌ Complaint Rejected</h2>
                <p style="font-size:0.875rem; color:#991b1b; margin:0;">Please contact support if you have questions.</p>
            </div>`;
    } else {
        const stepsHtml = window.PIPELINE.map((step, idx) => {
            const done   = currentStep >= idx;
            const circle = done
                ? `<div style="width:48px;height:48px;border-radius:9999px;display:flex;align-items:center;justify-content:center;font-size:1.25rem;background:#4f46e5;box-shadow:0 0 0 4px #c7d2fe;">${step.icon}</div>`
                : `<div style="width:48px;height:48px;border-radius:9999px;display:flex;align-items:center;justify-content:center;font-size:1.25rem;background:#f3f4f6;border:2px solid #e5e7eb;">○</div>`;
            const connector = idx < window.PIPELINE.length - 1
                ? `<div style="flex:1;height:3px;background:${currentStep > idx ? '#4f46e5' : '#e5e7eb'};border-radius:9999px;margin-bottom:1.5rem;"></div>`
                : '';
            return `<div style="display:flex;flex-direction:column;align-items:center;flex:1;">${circle}<p style="font-size:0.7rem;font-weight:${done?'700':'500'};color:${done?'#4f46e5':'#9ca3af'};margin:0.5rem 0 0;text-align:center;">${step.label}</p></div>${connector}`;
        }).join('');
        document.getElementById('card-pipeline').innerHTML =
            `<h2 class="rw-section-title">📊 Progress</h2><div style="display:flex;align-items:center;margin-top:1rem;">${stepsHtml}</div>`;
    }

    // ── Media gallery ────────────────────────────────────────────────────────
    if (c.media && c.media.length > 0) {
        const mediaHtml = c.media.map(m => m.file_type === 'image'
            ? `<a href="${m.cloud_url}" target="_blank" style="display:block;aspect-ratio:4/3;"><img src="${m.cloud_url}" alt="${escHtml(m.original_name)}" style="width:100%;height:100%;object-fit:cover;border-radius:0.625rem;border:1px solid #e5e7eb;transition:transform 0.15s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'"></a>`
            : `<div style="background:#1f2937;border-radius:0.625rem;aspect-ratio:4/3;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:0.5rem;"><div style="font-size:2rem;">🎥</div><a href="${m.cloud_url}" target="_blank" style="font-size:0.75rem;color:#60a5fa;">Watch video</a></div>`
        ).join('');
        const card = document.getElementById('card-media');
        card.innerHTML = `<h2 class="rw-section-title">📷 Attached Media</h2><div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0.75rem;margin-top:1rem;">${mediaHtml}</div>`;
        card.style.display = 'block';
    }

    // ── Leaflet mini-map ─────────────────────────────────────────────────────
    if (!window.leafletMap) {
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
            iconUrl:       'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
            shadowUrl:     'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
        });
        window.leafletMap = L.map('detail-map', { zoomControl:true, dragging:false, scrollWheelZoom:false })
                      .setView([c.latitude, c.longitude], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution:'© OpenStreetMap' }).addTo(window.leafletMap);
        L.marker([c.latitude, c.longitude]).addTo(window.leafletMap)
         .bindPopup(`<strong>${escHtml(c.title)}</strong><br>${escHtml(c.location)}`).openPopup();
    }

    // ── Details sidebar ──────────────────────────────────────────────────────
    const rows = [
        ['Category',  c.category?.name ?? '—'],
        ['Severity',  sev.label],
        ['Status',    s.label],
        ['Submitted', formatDate(c.created_at)],
        ['Views',     c.views_count + ' views'],
        ['Votes',     c.votes_count + ' upvotes'],
        ...(c.resolved_at ? [['Resolved on', formatDate(c.resolved_at)]] : []),
    ];
    document.getElementById('card-details').innerHTML = `
        <h2 class="rw-section-title">ℹ️ Details</h2>
        <div style="margin-top:0.875rem;display:flex;flex-direction:column;gap:0.625rem;">
            ${rows.map(([k,v],i) => `
            <div style="display:flex;justify-content:space-between;align-items:center;
                        padding:0.5rem 0;${i<rows.length-1?'border-bottom:1px solid #f9fafb;'
                        }font-size:0.8125rem;">
                <span style="color:#6b7280;font-weight:500;">${k}</span>
                <span style="color:#111827;font-weight:600;text-align:right;">${v}</span>
            </div>`).join('')}
        </div>`;

    // ── Status timeline ──────────────────────────────────────────────────────
    const histories = c.status_histories ?? [];
    const timelineItems = histories.length === 0
        ? '<p style="font-size:0.8125rem;color:#9ca3af;margin-top:0.875rem;">No status changes yet.</p>'
        : `<div style="margin-top:1rem;position:relative;">
            <div style="position:absolute;left:15px;top:8px;bottom:8px;width:2px;background:#e5e7eb;"></div>
            <div style="display:flex;flex-direction:column;gap:1.25rem;">
                ${histories.map(h => {
                    const hs = window.STATUS_CONFIG[h.new_status] ?? { border:'#e5e7eb' };
                    const firstChar = (window.STATUS_CONFIG[h.new_status]?.label ?? '•')[0];
                    return `<div style="display:flex;gap:1rem;align-items:flex-start;position:relative;">
                        <div style="width:32px;height:32px;border-radius:9999px;background:#fff;border:3px solid ${hs.border};flex-shrink:0;z-index:1;display:flex;align-items:center;justify-content:center;font-size:0.875rem;">${firstChar}</div>
                        <div style="flex:1;min-width:0;padding-top:0.125rem;">
                            <div style="font-size:0.8125rem;font-weight:700;color:#111827;">
                                ${capWords(h.old_status)} → ${capWords(h.new_status)}
                            </div>
                            ${h.remarks ? `<p style="font-size:0.75rem;color:#6b7280;margin:0.25rem 0 0;font-style:italic;">"${escHtml(h.remarks)}"</p>` : ''}
                            <div style="display:flex;gap:0.75rem;margin-top:0.25rem;font-size:0.7rem;color:#9ca3af;">
                                <span>${formatDate(h.created_at)}</span>
                                ${h.changed_by ? `<span>by ${escHtml(h.changed_by.name)}</span>` : ''}
                            </div>
                        </div>
                    </div>`;
                }).join('')}
            </div>
          </div>`;

    document.getElementById('card-timeline').innerHTML =
        `<h2 class="rw-section-title">🕐 Status Timeline</h2>${timelineItems}`;

    // ── Feedback card ────────────────────────────────────────────────────────
    if (c.status === 'resolved') {
        let feedbackHtml = '';
        if (c.feedback) {
            const stars = Array.from({length:5}, (_,i) => i < c.feedback.rating ? '⭐' : '☆').join('');
            feedbackHtml = `
                <div class="rw-card" style="border:1px solid #86efac;background:#f0fdf4;">
                    <h2 class="rw-section-title" style="color:#15803d;">⭐ Your Feedback</h2>
                    <div style="margin-top:0.875rem;text-align:center;">
                        <div style="font-size:2rem;margin-bottom:0.25rem;">${stars}</div>
                        <p style="font-size:0.813rem;color:#166534;font-weight:600;margin:0;">You rated this ${c.feedback.rating}/5</p>
                        ${c.feedback.comment ? `<p style="font-size:0.8125rem;color:#15803d;font-style:italic;margin:0.5rem 0 0;">"${escHtml(c.feedback.comment)}"</p>` : ''}
                    </div>
                </div>`;
        } else {
            feedbackHtml = `
                <div class="rw-card" style="border:1px solid #86efac;background:#f0fdf4;" id="feedback-card">
                    <h2 class="rw-section-title" style="color:#15803d;">⭐ Rate the Resolution</h2>
                    <div style="margin-top:0.875rem;">
                        <p style="font-size:0.875rem;color:#166534;margin:0 0 0.75rem;text-align:center;">How satisfied are you with the resolution?</p>
                        <div style="display:flex;justify-content:center;gap:0.5rem;margin-bottom:1rem;" id="stars-row">
                            ${[1,2,3,4,5].map(i=>`<span class="star-btn" data-val="${i}" onclick="handleStarClick(${i})">☆</span>`).join('')}
                        </div>
                        <textarea id="feedback-comment" placeholder="Optional comment..." rows="3"
                                  style="width:100%;border:1.5px solid #86efac;border-radius:0.625rem;padding:0.625rem;
                                         font-size:0.8125rem;resize:none;outline:none;background:#fff;font-family:inherit;box-sizing:border-box;"></textarea>
                        <p id="feedback-error" style="color:#dc2626;font-size:0.813rem;display:none;">Please select a star rating.</p>
                        <button onclick="submitFeedback()" id="feedback-btn"
                                style="margin-top:0.75rem;width:100%;background:#16a34a;color:#fff;border:none;
                                       border-radius:0.625rem;padding:0.625rem;font-size:0.875rem;font-weight:600;cursor:pointer;font-family:inherit;">
                            Submit Feedback
                        </button>
                    </div>
                </div>`;
        }
        document.getElementById('card-feedback').innerHTML = feedbackHtml;
    }
}

// ── Star rating interactions ────────────────────────────────────────────────
window.selectedRating = 0;
window.handleStarClick = function(n) {
    window.selectedRating = n;
    document.querySelectorAll('.star-btn').forEach((s,i) => s.textContent = i < n ? '⭐' : '☆');
};

// ── Submit feedback via API ─────────────────────────────────────────────────
window.submitFeedback = async function() {
    if (!window.selectedRating) {
        document.getElementById('feedback-error').style.display = 'block'; return;
    }
    const btn = document.getElementById('feedback-btn');
    btn.disabled = true; btn.textContent = '⏳ Submitting...';
    try {
        await axios.post(`/api/v1/citizen/complaints/${window.COMPLAINT_ID}/feedback`, {
            rating:  window.selectedRating,
            comment: document.getElementById('feedback-comment').value || null,
        });
        window.loadComplaint();
    } catch (err) {
        btn.disabled = false; btn.textContent = 'Submit Feedback';
        alert(err.response?.data?.message ?? 'Failed to submit feedback.');
    }
};

// ── Helpers ─────────────────────────────────────────────────────────────────
function escHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function timeAgo(iso) {
    const diff = Date.now() - new Date(iso);
    const m = Math.floor(diff/60000), h = Math.floor(m/60), d = Math.floor(h/24);
    return d > 0 ? `${d}d ago` : h > 0 ? `${h}h ago` : `${m}m ago`;
}
function formatDate(iso) {
    return new Date(iso).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
}
function capWords(str) {
    return str.replace(/_/g,' ').replace(/\b\w/g, c => c.toUpperCase());
}

document.addEventListener('DOMContentLoaded', () => window.loadComplaint());
</script>

@endsection

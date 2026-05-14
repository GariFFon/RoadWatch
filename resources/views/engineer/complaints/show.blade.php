@extends('layouts.engineer')
@section('title', 'Complaint Detail')
@section('page-title', '🔍 Complaint Detail')

@section('content')

{{-- Back link --}}
<div style="margin-bottom:1.5rem;">
    <a href="{{ route('engineer.complaints.index') }}"
       style="display:inline-flex;align-items:center;gap:.375rem;font-size:.875rem;font-weight:600;
              color:#6b7280;text-decoration:none;transition:color .15s;"
       onmouseover="this.style.color='#0ea5e9'" onmouseout="this.style.color='#6b7280'">
        ← Back to My Assignments
    </a>
</div>

{{-- Loading skeleton --}}
<div id="detail-loading" style="display:flex;flex-direction:column;gap:1.25rem;">
    <div style="background:#f3f4f6;border-radius:1rem;height:140px;animation:pulse 1.4s infinite;"></div>
    <div style="display:grid;grid-template-columns:1fr 340px;gap:1.25rem;">
        <div style="background:#f3f4f6;border-radius:1rem;height:260px;animation:pulse 1.4s infinite .05s;"></div>
        <div style="background:#f3f4f6;border-radius:1rem;height:260px;animation:pulse 1.4s infinite .1s;"></div>
    </div>
</div>

{{-- Main content --}}
<div id="detail-content" style="display:none;">

    {{-- Header card --}}
    <div class="eng-card" style="padding:1.75rem;margin-bottom:1.5rem;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.75rem;flex-wrap:wrap;">
                    <span id="d-category-icon" style="font-size:1.75rem;"></span>
                    <div>
                        <h1 id="d-title" style="font-size:1.375rem;font-weight:800;color:#111827;margin:0;"></h1>
                        <p id="d-number" style="font-size:.8125rem;color:#9ca3af;margin:.1rem 0 0;"></p>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                    <span id="d-status-badge"></span>
                    <span id="d-severity-badge"></span>
                    <span id="d-category-badge"></span>
                </div>
            </div>
            {{-- Status update panel --}}
            <div id="status-update-panel" style="flex-shrink:0;"></div>
        </div>
        <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid #f3f4f6;display:flex;gap:2rem;flex-wrap:wrap;">
            <div><p style="font-size:.72rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.25rem;">Location</p>
                 <p id="d-location" style="font-size:.875rem;font-weight:600;color:#374151;"></p></div>
            <div><p style="font-size:.72rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.25rem;">Reported By</p>
                 <p id="d-reporter" style="font-size:.875rem;font-weight:600;color:#374151;"></p></div>
            <div><p style="font-size:.72rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.25rem;">Filed On</p>
                 <p id="d-date" style="font-size:.875rem;font-weight:600;color:#374151;"></p></div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem;align-items:start;">

        {{-- Left: description + before media + after media + timeline --}}
        <div style="display:flex;flex-direction:column;gap:1.5rem;">

            {{-- Description --}}
            <div class="eng-card" style="padding:1.5rem;">
                <h2 style="font-size:.9375rem;font-weight:700;color:#111827;margin-bottom:.875rem;">📝 Description</h2>
                <p id="d-description" style="font-size:.9rem;color:#374151;line-height:1.75;"></p>
            </div>

            {{-- Before Media (citizen submitted) --}}
            <div class="eng-card" style="padding:1.5rem;">
                <h2 style="font-size:.9375rem;font-weight:700;color:#111827;margin-bottom:.875rem;">📸 Before — Citizen Photos</h2>
                <div id="d-before-media" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:.75rem;"></div>
            </div>

            {{-- After Media (engineer uploaded) --}}
            <div class="eng-card" style="padding:1.5rem;" id="after-media-card">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.875rem;">
                    <h2 style="font-size:.9375rem;font-weight:700;color:#111827;">✅ After — Work Evidence</h2>
                    <span id="after-count" style="font-size:.75rem;font-weight:600;color:#6b7280;background:#f3f4f6;padding:.2rem .5rem;border-radius:9999px;"></span>
                </div>
                <div id="d-after-media" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:.75rem;margin-bottom:.875rem;"></div>
                <p id="after-empty" style="color:#9ca3af;font-size:.875rem;display:none;">No evidence uploaded yet. Use the upload panel →</p>
            </div>

            {{-- Status Timeline --}}
            <div class="eng-card" style="padding:1.5rem;">
                <h2 style="font-size:.9375rem;font-weight:700;color:#111827;margin-bottom:1.25rem;">📈 Status Timeline</h2>
                <div id="d-timeline" style="display:flex;flex-direction:column;gap:0;"></div>
            </div>
        </div>

        {{-- Right: upload evidence + status update + citizen --}}
        <div id="right-col" style="position:sticky;top:76px;display:flex;flex-direction:column;gap:1.25rem;">

            {{-- Upload Work Evidence --}}
            <div class="eng-card" style="padding:1.5rem;" id="upload-card">
                <h2 style="font-size:.9375rem;font-weight:700;color:#111827;margin-bottom:.25rem;">📤 Upload Work Evidence</h2>
                <p style="font-size:.78rem;color:#9ca3af;margin-bottom:.75rem;">Photos/videos after completing the work. Admin will review these before marking resolved.</p>

                {{-- ⚠ Permanent upload warning --}}
                <div style="background:#fffbeb;border:1.5px solid #fcd34d;border-radius:.625rem;
                            padding:.5rem .75rem;margin-bottom:1rem;display:flex;align-items:flex-start;gap:.5rem;">
                    <span style="font-size:1rem;flex-shrink:0;">⚠️</span>
                    <p style="font-size:.75rem;color:#92400e;font-weight:600;margin:0;line-height:1.4;">
                        Upload with care — files <strong>cannot be removed</strong> once uploaded.
                        Make sure your evidence is clear and correct before submitting.
                    </p>
                </div>

                {{-- Uploaded files strip (shown when files exist) --}}
                <div id="uploaded-strip" style="display:none;">
                    <div id="strip-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(80px,1fr));gap:.5rem;margin-bottom:.75rem;"></div>
                    <button onclick="toggleAddMore()" id="add-more-btn"
                            style="width:100%;background:#f0f9ff;border:1.5px dashed #7dd3fc;border-radius:.625rem;
                                   padding:.5rem;font-size:.8125rem;font-weight:600;color:#0284c7;cursor:pointer;
                                   display:flex;align-items:center;justify-content:center;gap:.4rem;transition:background .15s;"
                            onmouseover="this.style.background='#e0f2fe'" onmouseout="this.style.background='#f0f9ff'">
                        ➕ Add Photos / Videos
                    </button>
                </div>

                {{-- Drop zone (shown when no files, or add-more clicked) --}}
                <div id="drop-zone"
                     style="border:2px dashed #e5e7eb;border-radius:.875rem;padding:1.5rem;text-align:center;
                            cursor:pointer;transition:all .2s;background:#fafafa;"
                     onclick="document.getElementById('file-input').click()"
                     ondragover="event.preventDefault();this.style.borderColor='#0ea5e9';this.style.background='#f0f9ff';"
                     ondragleave="this.style.borderColor='#e5e7eb';this.style.background='#fafafa';"
                     ondrop="handleDrop(event)">
                    <div style="font-size:2rem;margin-bottom:.5rem;">📷</div>
                    <p style="font-size:.8125rem;font-weight:600;color:#374151;margin:0;">Click or drag files here</p>
                    <p style="font-size:.75rem;color:#9ca3af;margin:.25rem 0 0;">JPG, PNG, WebP, MP4, MOV · Max 50 MB each</p>
                </div>
                <input type="file" id="file-input" multiple accept="image/*,video/*" style="display:none;" onchange="handleFiles(this.files)">

                {{-- Preview grid (new files pending upload) --}}
                <div id="preview-grid" style="display:none;display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;margin-top:.75rem;"></div>

                {{-- Upload btn --}}
                <button id="upload-btn" onclick="uploadEvidence()"
                        style="display:none;width:100%;justify-content:center;margin-top:.875rem;"
                        class="eng-btn eng-btn-primary">
                    📤 Upload Evidence
                </button>
                <div id="upload-error" style="display:none;color:#dc2626;font-size:.8125rem;margin-top:.5rem;"></div>
                <div id="upload-progress" style="display:none;margin-top:.75rem;">
                    <div style="height:6px;background:#f3f4f6;border-radius:9999px;overflow:hidden;">
                        <div id="progress-bar" style="height:100%;width:0%;background:linear-gradient(90deg,#0ea5e9,#6366f1);border-radius:9999px;transition:width .3s;"></div>
                    </div>
                    <p id="progress-text" style="font-size:.75rem;color:#6b7280;margin-top:.375rem;text-align:center;"></p>
                </div>
            </div>

            {{-- Update Status --}}
            <div class="eng-card" style="padding:1.5rem;" id="update-card">
                <h2 style="font-size:.9375rem;font-weight:700;color:#111827;margin-bottom:1.25rem;">🔄 Update Status</h2>
                <div id="update-form-area"></div>
            </div>

            {{-- Citizen info --}}
            <div class="eng-card" style="padding:1.5rem;">
                <h2 style="font-size:.9375rem;font-weight:700;color:#111827;margin-bottom:1rem;">👤 Citizen</h2>
                <div id="d-citizen-info" style="font-size:.875rem;color:#374151;"></div>
            </div>
        </div>

    </div>
</div>

{{-- Status update modal for confirmation --}}
<div class="eng-modal-bg" id="confirm-modal">
    <div class="eng-modal">
        <h3 style="font-size:1rem;font-weight:700;color:#111827;margin-bottom:1rem;">✅ Confirm Status Change</h3>
        <div id="confirm-body" style="background:#f9fafb;border-radius:.75rem;padding:.875rem;margin-bottom:1.25rem;font-size:.875rem;color:#374151;"></div>
        <div id="confirm-error" style="display:none;color:#dc2626;font-size:.8125rem;margin-bottom:.75rem;"></div>
        <div style="display:flex;gap:.75rem;justify-content:flex-end;">
            <button onclick="closeConfirm()" class="eng-btn eng-btn-outline">Cancel</button>
            <button onclick="executeUpdate()" id="confirm-btn" class="eng-btn eng-btn-primary">Confirm Update</button>
        </div>
    </div>
</div>

<style>
.timeline-step{display:flex;gap:.875rem;position:relative;}
.timeline-step::before{content:'';position:absolute;left:11px;top:24px;bottom:-1px;width:2px;background:#f3f4f6;}
.timeline-step:last-child::before{display:none;}
.timeline-dot{width:24px;height:24px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;margin-top:.125rem;}
</style>

<script>
const STATUS_CFG = {
    pending:               {label:'Pending',               color:'#92400e', bg:'#fffbeb', icon:'⏳'},
    under_review:          {label:'Under Review',          color:'#1e40af', bg:'#eff6ff', icon:'🔍'},
    in_progress:           {label:'In Progress',           color:'#0c4a6e', bg:'#e0f2fe', icon:'🔧'},
    awaiting_verification: {label:'Awaiting Verification', color:'#c2410c', bg:'#fff7ed', icon:'🕐'},
    verified:              {label:'Verified ✓',            color:'#14532d', bg:'#f0fdf4', icon:'✅'},
    rejected:              {label:'Rejected',              color:'#7f1d1d', bg:'#fef2f2', icon:'❌'},
};
const SEV_CFG = {
    low:       {bg:'#d1fae5',color:'#065f46'},
    medium:    {bg:'#fef9c3',color:'#713f12'},
    high:      {bg:'#ffedd5',color:'#7c2d12'},
    emergency: {bg:'#fee2e2',color:'#7f1d1d'},
};
// Engineer-visible transitions only — backend is the source of truth via next_statuses
const TRANSITIONS = {
    pending:               [],
    under_review:          [],
    in_progress:           ['awaiting_verification'],
    awaiting_verification: [],  // engineer waits for admin to verify
    verified:              [],
    rejected:              [],
};

const complaintId = {{ request()->route('complaint') }};
let pendingNewStatus = null;
let pendingRemarks   = null;

function esc(s){return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
function fmt(iso){return new Date(iso).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});}

function badge(s){
    const cfg = STATUS_CFG[s]??{label:s,color:'#374151',bg:'#f3f4f6',icon:'📌'};
    return `<span style="background:${cfg.bg};color:${cfg.color};font-size:.72rem;font-weight:700;padding:.2rem .625rem;border-radius:9999px;">${cfg.icon} ${cfg.label}</span>`;
}

async function loadDetail() {
    try {
        const res = await axios.get(`/api/v1/engineer/complaints/${complaintId}`);
        const c   = res.data.data;

        document.title = `${c.title} — RoadWatch Engineer`;

        // ── Header ──
        document.getElementById('d-category-icon').textContent = c.category?.icon ?? '📌';
        document.getElementById('d-title').textContent         = c.title;
        document.getElementById('d-number').textContent        = c.complaint_number;

        const s   = STATUS_CFG[c.status]  ?? {label:c.status, color:'#374151', bg:'#f3f4f6', icon:'📌'};
        const sev = SEV_CFG[c.severity]   ?? {bg:'#f3f4f6', color:'#374151'};
        document.getElementById('d-status-badge').innerHTML   = `<span style="background:${s.bg};color:${s.color};font-size:.8rem;font-weight:700;padding:.25rem .75rem;border-radius:9999px;">${s.icon} ${s.label}</span>`;
        document.getElementById('d-severity-badge').innerHTML = `<span style="background:${sev.bg};color:${sev.color};font-size:.8rem;font-weight:700;padding:.25rem .75rem;border-radius:9999px;">${(c.severity??'').charAt(0).toUpperCase()+(c.severity??'').slice(1)}</span>`;
        if (c.category?.name) document.getElementById('d-category-badge').innerHTML = `<span style="background:#f3f4f6;color:#6b7280;font-size:.8rem;font-weight:600;padding:.25rem .75rem;border-radius:9999px;">${esc(c.category.name)}</span>`;

        document.getElementById('d-location').textContent = c.location ?? '—';
        document.getElementById('d-reporter').textContent = c.submitted_by ?? 'Anonymous';
        document.getElementById('d-date').textContent     = fmt(c.created_at);

        // ── Description ──
        document.getElementById('d-description').textContent = c.description ?? 'No description provided.';

        // ── Media: split before / after ──
        const before = (c.media??[]).filter(m => m.stage === 'before');
        const after  = (c.media??[]).filter(m => m.stage === 'after');

        const beforeEl = document.getElementById('d-before-media');
        beforeEl.innerHTML = before.length
            ? before.map(m => mediaThumb(m, false)).join('')
            : '<p style="color:#9ca3af;font-size:.875rem;">No photos submitted by citizen.</p>';

        renderAfterMedia(after, false); // evidence is permanent — no deletion allowed

        // ── Citizen info ──
        document.getElementById('d-citizen-info').innerHTML = c.submitted_by
            ? `<div style="display:flex;align-items:center;gap:.625rem;">
                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#0ea5e9,#6366f1);
                            display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;flex-shrink:0;">
                    ${(c.submitted_by[0]??'?').toUpperCase()}
                </div>
                <div>
                    <p style="font-weight:600;color:#111827;margin:0;">${esc(c.submitted_by)}</p>
                    <p style="font-size:.75rem;color:#9ca3af;margin:.1rem 0 0;">Citizen</p>
                </div>
               </div>`
            : '<p style="color:#9ca3af;font-size:.875rem;">Anonymous report</p>';

        // ── Timeline ──
        const timeline  = c.status_histories ?? [];
        const timelineEl = document.getElementById('d-timeline');
        if (!timeline.length) {
            timelineEl.innerHTML = '<p style="color:#9ca3af;font-size:.875rem;">No history yet.</p>';
        } else {
            timelineEl.innerHTML = timeline.map((h, i) => {
                const ns = STATUS_CFG[h.new_status] ?? {label:h.new_status, color:'#374151', bg:'#f3f4f6', icon:'📌'};
                const isWorkRejection = h.old_status === 'awaiting_verification' && h.new_status === 'in_progress';
                const isVerified      = h.new_status === 'verified';
                const actor = h.changed_by ? `${esc(h.changed_by.name)} (ID: ${h.changed_by.id})` : 'Admin';
                const isLast = i === timeline.length - 1;

                let dotBg = ns.bg, dotColor = ns.color, badgeBg = ns.bg, badgeColor = ns.color, dotIcon = ns.icon;
                let labelHtml, remarksHtml = '';

                if (isWorkRejection) {
                    dotBg = '#fef2f2'; dotColor = '#991b1b'; dotIcon = '🚫';
                    badgeBg = '#fef2f2'; badgeColor = '#991b1b';
                    labelHtml = '🚫 Work Rejected by Admin';
                    remarksHtml = `
                        <div style="background:#fef2f2;border-left:3px solid #fca5a5;border-radius:0 .375rem .375rem 0;
                                    padding:.5rem .75rem;margin:.375rem 0;font-size:.8125rem;">
                            <div><span style="font-weight:700;color:#991b1b;">Rejected by:</span>
                                 <span style="color:#374151;"> ${actor}</span></div>
                            ${h.remarks ? `<div style="margin-top:.25rem;"><span style="font-weight:700;color:#991b1b;">Reason:</span>
                                 <span style="color:#374151;font-style:italic;"> "${esc(h.remarks)}"</span></div>` : ''}
                        </div>`;
                } else if (isVerified) {
                    dotBg = '#f0fdf4'; dotColor = '#14532d'; dotIcon = '✅';
                    badgeBg = '#f0fdf4'; badgeColor = '#14532d';
                    labelHtml = '✅ Task Completed — Verified by Admin';
                    remarksHtml = `
                        <div style="background:#f0fdf4;border-left:3px solid #86efac;border-radius:0 .375rem .375rem 0;
                                    padding:.5rem .75rem;margin:.375rem 0;font-size:.8125rem;">
                            <div><span style="font-weight:700;color:#14532d;">Verified by:</span>
                                 <span style="color:#374151;"> ${actor}</span></div>
                            ${h.remarks ? `<div style="margin-top:.25rem;color:#6b7280;font-style:italic;">"${esc(h.remarks)}"</div>` : ''}
                        </div>`;
                } else if (!h.old_status) {
                    labelHtml = '📋 Complaint Filed';
                    if (h.remarks) remarksHtml = `<p style="font-size:.8125rem;color:#6b7280;margin:.25rem 0;">"${esc(h.remarks)}"</p>`;
                } else {
                    const from = h.old_status.replace(/_/g,' ').replace(/\b\w/g, c => c.toUpperCase());
                    const to   = h.new_status.replace(/_/g,' ').replace(/\b\w/g, c => c.toUpperCase());
                    labelHtml = `${from} → ${to}`;
                    if (h.remarks) remarksHtml = `<p style="font-size:.8125rem;color:#6b7280;margin:.25rem 0;font-style:italic;">"${esc(h.remarks)}"</p>`;
                }

                return `<div class="timeline-step" style="padding-bottom:${isLast?'0':'1.25rem'};">
                    <div class="timeline-dot" style="background:${dotBg};color:${dotColor};">${dotIcon}</div>
                    <div style="flex:1;min-width:0;">
                        <div style="margin-bottom:.25rem;">
                            <span style="background:${badgeBg};color:${badgeColor};font-size:.8rem;font-weight:700;
                                         padding:.2rem .625rem;border-radius:9999px;">${labelHtml}</span>
                        </div>
                        ${remarksHtml}
                        <p style="font-size:.75rem;color:#9ca3af;margin:.25rem 0 0;">
                            ${fmt(h.created_at)}${!isWorkRejection && !isVerified && h.changed_by ? ' · by '+esc(h.changed_by.name) : ''}
                        </p>
                    </div>
                </div>`;
            }).join('');
        }

        // ── Update form: use LOCAL transition map for engineers.
        // awaiting_verification always = [] on the engineer side (admin-only decision).
        const engineerAllowed = TRANSITIONS[c.status] ?? [];
        renderUpdateForm(c.status, engineerAllowed);

        // ── READ-ONLY mode for verified complaints ──────────────────────────
        if (c.status === 'verified') {
            // 1. Hide interactive right-panel cards
            document.getElementById('upload-card').style.display = 'none';
            document.getElementById('update-card').style.display = 'none';

            // 2. Inject a read-only banner into the sticky right column
            const rightCol = document.getElementById('right-col');
            if (rightCol) {
                const ro = document.createElement('div');
                ro.innerHTML = `
                    <div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:2px solid #86efac;
                                border-radius:1rem;padding:1.5rem;text-align:center;">
                        <div style="font-size:2.5rem;margin-bottom:.625rem;">🏆</div>
                        <p style="font-size:.9375rem;font-weight:800;color:#14532d;margin:0;">Task Verified & Closed</p>
                        <p style="font-size:.78rem;color:#16a34a;margin:.375rem 0 0;">
                            This complaint has been officially verified by the admin.<br>
                            No further actions are available.
                        </p>
                        ${(()=>{
                            const vEntry = (c.status_histories??[]).slice().reverse().find(h=>h.new_status==='verified');
                            if (!vEntry) return '';
                            const who  = vEntry.changed_by ? `${esc(vEntry.changed_by.name)} (ID: ${vEntry.changed_by.id})` : 'Admin';
                            const when = new Date(vEntry.created_at).toLocaleString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
                            return `<div style="margin-top:1rem;padding:.75rem;background:#fff;border-radius:.625rem;border:1px solid #bbf7d0;font-size:.78rem;text-align:left;">
                                <div style="color:#6b7280;">Verified by: <strong style="color:#15803d;">${who}</strong></div>
                                <div style="color:#9ca3af;margin-top:.2rem;">🕐 ${when}</div>
                            </div>`;
                        })()}
                        <div id="eng-rating-display" style="display:none;"></div>
                        <a href="{{ route('engineer.complaints.completed') }}"
                           style="display:inline-flex;align-items:center;gap:.375rem;margin-top:1rem;
                                  background:#14532d;color:#fff;border-radius:.5rem;padding:.5rem 1rem;
                                  font-size:.8125rem;font-weight:600;text-decoration:none;">
                            ← Back to Completed Tasks
                        </a>
                    </div>`;
                rightCol.insertBefore(ro, rightCol.firstChild);
            }

            // 3. Add a READ ONLY ribbon to the header
            const headerPanel = document.getElementById('status-update-panel');
            if (headerPanel) {
                headerPanel.innerHTML = `
                    <div style="display:inline-flex;align-items:center;gap:.375rem;background:#f0fdf4;
                                color:#14532d;border:1.5px solid #86efac;border-radius:9999px;
                                padding:.3rem .875rem;font-size:.75rem;font-weight:700;">
                        🔒 READ ONLY — Verified & Closed
                    </div>`;
            }

            // 4. Make after-media non-deletable (re-render without delete buttons)
            renderAfterMedia(after, false);

            // 5. Show admin's rating if already given
            const ratingEl = document.getElementById('eng-rating-display');
            if (ratingEl) {
                if (c.engineer_rating) {
                    const r = c.engineer_rating;
                    const stars = '★'.repeat(r.score) + '☆'.repeat(5 - r.score);
                    ratingEl.style.display = 'block';
                    ratingEl.innerHTML = `
                        <div style="margin-top:1rem;padding:.875rem;background:#fff;border-radius:.75rem;
                                    border:1.5px solid #fde68a;text-align:left;">
                            <p style="font-size:.72rem;font-weight:700;color:#92400e;margin:0 0 .375rem;">⭐ Admin's Performance Rating</p>
                            <div style="font-size:1.5rem;color:#f59e0b;letter-spacing:.08em;margin-bottom:.2rem;">${stars}</div>
                            <div style="font-size:.875rem;font-weight:700;color:#374151;">${r.emoji} ${esc(r.label)} (${r.score}/5)</div>
                            ${r.comment ? `<div style="font-size:.78rem;color:#6b7280;font-style:italic;margin-top:.25rem;">"${esc(r.comment)}"</div>` : ''}
                            <div style="font-size:.7rem;color:#9ca3af;margin-top:.25rem;">
                                Rated by ${esc(r.rated_by?.name ?? 'Admin')}
                            </div>
                        </div>`;
                } else {
                    ratingEl.style.display = 'block';
                    ratingEl.innerHTML = `
                        <div style="margin-top:.875rem;padding:.625rem;background:rgba(255,255,255,.6);
                                    border-radius:.5rem;border:1px dashed #fde68a;text-align:center;font-size:.75rem;color:#92400e;">
                            ⏳ Admin hasn't rated your work yet.
                        </div>`;
                }
            }
        }
        // ────────────────────────────────────────────────────────────────────

        // Show content
        document.getElementById('detail-loading').style.display = 'none';
        document.getElementById('detail-content').style.display = 'block';


    } catch(e) {
        document.getElementById('detail-loading').innerHTML =
            `<div style="text-align:center;padding:4rem;color:#dc2626;">
                <div style="font-size:2.5rem;margin-bottom:1rem;">⚠️</div>
                <p>Failed to load complaint. It may not be assigned to you.</p>
                <a href="{{ route('engineer.complaints.index') }}" style="color:#0ea5e9;font-weight:600;">← Back</a>
             </div>`;
    }
}

function renderUpdateForm(currentStatus, nextStatuses) {
    nextStatuses = nextStatuses ?? [];
    const updateCard = document.getElementById('update-card');

    if (!nextStatuses.length) {
        const isVerified              = currentStatus === 'verified';
        const isRejected              = currentStatus === 'rejected';
        const isAwaitingVerification  = currentStatus === 'awaiting_verification';

        let icon = '✅', color = '#15803d', bg = '#f0fdf4', msg = '', sub = '';
        if (isVerified) {
            icon = '🏆'; msg = 'Work Verified by Admin!';
            sub = 'This complaint has been officially closed. Great job!';
        } else if (isRejected) {
            icon = '❌'; color = '#991b1b'; bg = '#fef2f2';
            msg = 'Complaint Rejected.'; sub = 'This complaint was rejected by admin.';
        } else if (isAwaitingVerification) {
            icon = '🕐'; color = '#c2410c'; bg = '#fff7ed';
            msg = 'Awaiting Admin Verification';
            sub = 'Your work has been submitted. The admin will review and verify shortly.';
        }

        updateCard.innerHTML = `
            <h2 style="font-size:.9375rem;font-weight:700;color:#111827;margin-bottom:.875rem;">🔄 Update Status</h2>
            <div style="text-align:center;padding:1.5rem 1rem;background:${bg};border-radius:.875rem;">
                <div style="font-size:2rem;margin-bottom:.5rem;">${icon}</div>
                <p style="font-size:.875rem;font-weight:700;color:${color};margin:0;">${msg}</p>
                <p style="font-size:.78rem;color:#9ca3af;margin:.375rem 0 0;">${sub}</p>
            </div>`;
        return;
    }

    updateCard.innerHTML = `
        <h2 style="font-size:.9375rem;font-weight:700;color:#111827;margin-bottom:1.25rem;">🔄 Update Status</h2>
        <p style="font-size:.78rem;color:#9ca3af;margin-bottom:.875rem;">Current: ${badge(currentStatus)}</p>
        <label style="font-size:.8125rem;font-weight:600;color:#374151;display:block;margin-bottom:.375rem;">Move to</label>
        <select id="new-status-select" class="eng-input eng-select" style="width:100%;margin-bottom:1rem;">
            <option value="">— Select new status —</option>
            ${nextStatuses.map(ns => {
                const cfg = STATUS_CFG[ns]??{label:ns,icon:'📌'};
                return `<option value="${ns}">${cfg.icon} ${cfg.label}</option>`;
            }).join('')}
        </select>
        <label style="font-size:.8125rem;font-weight:600;color:#374151;display:block;margin-bottom:.375rem;">Remarks <span style="color:#9ca3af;font-weight:400;">(optional)</span></label>
        <textarea id="update-remarks" class="eng-input" rows="3" placeholder="e.g. Crew dispatched, estimated completion 2 days…"
                  style="width:100%;resize:vertical;"></textarea>
        <button onclick="openConfirm()" class="eng-btn eng-btn-primary" style="width:100%;justify-content:center;margin-top:1rem;">
            Update Status →
        </button>`;
}

function openConfirm() {
    const sel     = document.getElementById('new-status-select');
    const remarks = document.getElementById('update-remarks').value.trim();
    if (!sel.value) { sel.style.borderColor = '#dc2626'; setTimeout(() => sel.style.borderColor = '#e5e7eb', 1500); return; }
    pendingNewStatus = sel.value;
    pendingRemarks   = remarks || null;
    const cfg = STATUS_CFG[pendingNewStatus] ?? {label:pendingNewStatus, icon:'📌'};
    document.getElementById('confirm-body').innerHTML =
        `Moving complaint to <strong>${cfg.icon} ${cfg.label}</strong>${remarks ? `<br><em style="color:#6b7280;">"${esc(remarks)}"</em>` : ''}`;
    document.getElementById('confirm-error').style.display = 'none';
    document.getElementById('confirm-modal').classList.add('open');
}
function closeConfirm() {
    document.getElementById('confirm-modal').classList.remove('open');
    pendingNewStatus = null; pendingRemarks = null;
}
document.getElementById('confirm-modal').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeConfirm();
});

async function executeUpdate() {
    const btn = document.getElementById('confirm-btn');
    btn.disabled = true; btn.textContent = '⏳ Saving…';
    try {
        await axios.patch(`/api/v1/engineer/complaints/${complaintId}/status`, {
            status:  pendingNewStatus,
            remarks: pendingRemarks,
        });
        closeConfirm();
        loadDetail(); // refresh page data
    } catch(e) {
        document.getElementById('confirm-error').textContent = e.response?.data?.message ?? 'Update failed.';
        document.getElementById('confirm-error').style.display = 'block';
        btn.disabled = false; btn.textContent = 'Confirm Update';
    }
}

document.addEventListener('DOMContentLoaded', loadDetail);

// ── Media helpers ──────────────────────────────────────────────────────────
function mediaThumb(m, deletable) {
    if (m.file_type === 'video') {
        return `<div style="aspect-ratio:1;border-radius:.75rem;overflow:hidden;background:#000;position:relative;">
            <video src="${esc(m.cloud_url)}" style="width:100%;height:100%;object-fit:cover;" controls></video>
            ${deletable ? `<button onclick="deleteEvidence(${m.id},this)" style="position:absolute;top:4px;right:4px;background:rgba(0,0,0,.6);border:none;color:#fff;border-radius:50%;width:22px;height:22px;cursor:pointer;font-size:.7rem;display:flex;align-items:center;justify-content:center;">✕</button>` : ''}
        </div>`;
    }
    return `<div style="position:relative;">
        <a href="${esc(m.cloud_url)}" target="_blank" style="display:block;aspect-ratio:1;border-radius:.75rem;overflow:hidden;background:#f3f4f6;">
            <img src="${esc(m.cloud_url)}" alt="photo" style="width:100%;height:100%;object-fit:cover;transition:transform .2s;"
                 onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
        </a>
        ${deletable ? `<button onclick="deleteEvidence(${m.id},this)" style="position:absolute;top:4px;right:4px;background:rgba(0,0,0,.55);border:none;color:#fff;border-radius:50%;width:22px;height:22px;cursor:pointer;font-size:.7rem;">✕</button>` : ''}
    </div>`;
}

function renderAfterMedia(items, allowDelete = true) {
    const el    = document.getElementById('d-after-media');
    const empty = document.getElementById('after-empty');
    const count = document.getElementById('after-count');
    count.textContent = items.length ? `${items.length} file(s)` : '';
    if (!items.length) { el.innerHTML = ''; empty.style.display = 'block'; return; }
    empty.style.display = 'none';
    el.innerHTML = items.map(m => mediaThumb(m, allowDelete)).join('');

    // ── Upload card: always show compact strip when files exist (no delete allowed) ──
    const strip    = document.getElementById('uploaded-strip');
    const dropZone = document.getElementById('drop-zone');
    if (!strip || !dropZone) return;

    const stripGrid = document.getElementById('strip-grid');
    stripGrid.innerHTML = items.map(m => {
        const isVid = m.file_type === 'video';
        return `<div style="aspect-ratio:1;border-radius:.625rem;overflow:hidden;background:#111;position:relative;"
                     title="${esc(m.original_name ?? '')}">
            ${isVid
                ? `<video src="${esc(m.cloud_url)}" style="width:100%;height:100%;object-fit:cover;"></video>
                   <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;
                               background:rgba(0,0,0,.35);pointer-events:none;">
                       <span style="font-size:1.25rem;">▶</span></div>`
                : `<img src="${esc(m.cloud_url)}" style="width:100%;height:100%;object-fit:cover;">`}
        </div>`;
    }).join('');
    strip.style.display    = 'block';
    dropZone.style.display = 'none';
}

// Toggle add-more drop zone visibility
function toggleAddMore() {
    const dz  = document.getElementById('drop-zone');
    const btn = document.getElementById('add-more-btn');
    const showing = dz.style.display !== 'none';
    dz.style.display  = showing ? 'none' : 'block';
    btn.textContent   = showing ? '➕ Add Photos / Videos' : '✖ Cancel';
    btn.style.background = showing ? '#f0f9ff' : '#fef2f2';
    btn.style.borderColor = showing ? '#7dd3fc' : '#fca5a5';
    btn.style.color       = showing ? '#0284c7' : '#dc2626';
}

async function deleteEvidence(mediaId, btn) {
    if (!confirm('Remove this file?')) return;
    btn.disabled = true;
    try {
        await axios.delete(`/api/v1/engineer/media/${mediaId}`);
        loadDetail();
    } catch(e) { alert(e.response?.data?.message ?? 'Delete failed.'); btn.disabled = false; }
}

// ── File picker & upload ───────────────────────────────────────────────────
let selectedFiles = [];

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('drop-zone').style.borderColor = '#e5e7eb';
    document.getElementById('drop-zone').style.background  = '#fafafa';
    handleFiles(e.dataTransfer.files);
}

function handleFiles(fileList) {
    selectedFiles = Array.from(fileList);
    if (!selectedFiles.length) return;
    const grid = document.getElementById('preview-grid');
    grid.style.display = 'grid';
    grid.innerHTML = selectedFiles.map((f,i) => {
        const isVid = f.type.startsWith('video');
        const url   = URL.createObjectURL(f);
        return `<div style="aspect-ratio:1;border-radius:.625rem;overflow:hidden;background:#f3f4f6;position:relative;">
            ${isVid
                ? `<video src="${url}" style="width:100%;height:100%;object-fit:cover;"></video>`
                : `<img src="${url}" style="width:100%;height:100%;object-fit:cover;">`}
            <div style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,.5);color:#fff;font-size:.6rem;padding:.2rem .3rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(f.name)}</div>
        </div>`;
    }).join('');
    document.getElementById('upload-btn').style.display = 'flex';
    document.getElementById('upload-error').style.display = 'none';
}

async function uploadEvidence() {
    if (!selectedFiles.length) return;
    const btn  = document.getElementById('upload-btn');
    const prog = document.getElementById('upload-progress');
    const bar  = document.getElementById('progress-bar');
    const txt  = document.getElementById('progress-text');
    const err  = document.getElementById('upload-error');

    btn.disabled = true; btn.textContent = '⏳ Uploading…';
    prog.style.display = 'block'; err.style.display = 'none';
    bar.style.width = '10%'; txt.textContent = 'Preparing…';

    const form = new FormData();
    selectedFiles.forEach(f => form.append('files[]', f));

    try {
        await axios.post(`/api/v1/engineer/complaints/${complaintId}/media`, form, {
            headers: {'Content-Type': 'multipart/form-data'},
            onUploadProgress: e => {
                const pct = Math.round((e.loaded / e.total) * 100);
                bar.style.width = pct + '%';
                txt.textContent = `Uploading… ${pct}%`;
            },
        });
        bar.style.width = '100%'; txt.textContent = 'Upload complete!';
        selectedFiles = [];
        document.getElementById('preview-grid').innerHTML = '';
        document.getElementById('preview-grid').style.display = 'none';
        btn.style.display = 'none';
        setTimeout(() => { prog.style.display = 'none'; }, 1500);
        loadDetail(); // refresh after section
    } catch(e) {
        err.textContent = e.response?.data?.message ?? 'Upload failed. Check file types and sizes.';
        err.style.display = 'block';
        btn.disabled = false; btn.textContent = '📤 Upload Evidence';
        prog.style.display = 'none';
    }
}
</script>
@endsection

@extends('layouts.admin')
@section('title', 'Complaints')
@section('page-title', '📋 Complaints Management')

@section('content')

{{-- Filters bar --}}
<div class="adm-card" style="padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
    <select id="f-status" class="adm-input adm-select" onchange="applyFilters()" disabled>
        <option value="">Loading…</option>
    </select>
    <select id="f-severity" class="adm-input adm-select" onchange="applyFilters()" disabled>
        <option value="">Loading…</option>
    </select>
    <button class="adm-btn adm-btn-outline" onclick="applyFilters()">🔄 Refresh</button>
    <div id="filter-count" style="margin-left:auto;font-size:.8125rem;color:#6b7280;font-weight:600;"></div>
</div>

{{-- Table card --}}
<div class="adm-card">
    <div id="complaints-loading" style="padding:3rem;text-align:center;">
        <div style="display:inline-block;width:32px;height:32px;border:3px solid #e5e7eb;border-top-color:#4f46e5;border-radius:50%;animation:spin .7s linear infinite;"></div>
        <p style="margin-top:.75rem;color:#9ca3af;font-size:.875rem;">Loading complaints…</p>
    </div>
    <div id="complaints-table" style="display:none;overflow-x:auto;"></div>
    <div id="complaints-empty" style="display:none;padding:3rem;text-align:center;color:#9ca3af;">No complaints match your filters.</div>
    <div id="pagination" style="padding:1rem 1.5rem;border-top:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;display:none;">
        <span id="pag-info" style="font-size:.8125rem;color:#6b7280;"></span>
        <div style="display:flex;gap:.5rem;">
            <button id="pag-prev" class="adm-btn adm-btn-outline adm-btn-sm" onclick="changePage(-1)">← Prev</button>
            <button id="pag-next" class="adm-btn adm-btn-outline adm-btn-sm" onclick="changePage(1)">Next →</button>
        </div>
    </div>
</div>

{{-- Assign Engineer Modal --}}
<div class="adm-modal-bg" id="assign-modal">
    <div class="adm-modal">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
            <h3 class="adm-modal-title" style="margin:0;">🔧 Assign Engineer</h3>
            <button onclick="closeAssignModal()" style="background:none;border:none;font-size:1.25rem;cursor:pointer;color:#9ca3af;">✕</button>
        </div>
        <div id="modal-complaint-info" style="background:#f9fafb;border-radius:.75rem;padding:.875rem;margin-bottom:1.25rem;font-size:.875rem;color:#374151;"></div>
        <label style="display:block;font-size:.8125rem;font-weight:600;color:#374151;margin-bottom:.375rem;">Select Engineer</label>
        <select id="engineer-select" class="adm-input adm-select" style="width:100%;margin-bottom:1.25rem;">
            <option value="">Loading engineers…</option>
        </select>
        <div id="assign-error" style="display:none;color:#dc2626;font-size:.8125rem;margin-bottom:.75rem;"></div>
        <div style="display:flex;gap:.75rem;justify-content:flex-end;">
            <button onclick="closeAssignModal()" class="adm-btn adm-btn-outline">Cancel</button>
            <button onclick="submitAssign()" id="assign-btn" class="adm-btn adm-btn-primary">Assign</button>
        </div>
    </div>
</div>

{{-- ── Complaint Detail Slide-over ── --}}
<div id="detail-slideover" style="display:none;position:fixed;inset:0;z-index:300;">
    {{-- Backdrop --}}
    <div id="slideover-backdrop"
         style="position:absolute;inset:0;background:rgba(15,23,42,.5);backdrop-filter:blur(3px);transition:opacity .3s;"
         onclick="closeDetail()"></div>
    {{-- Panel --}}
    <div id="slideover-panel"
         style="position:absolute;top:0;right:0;bottom:0;width:760px;max-width:100vw;
                background:#f8fafc;box-shadow:-16px 0 60px rgba(0,0,0,.18);
                display:flex;flex-direction:column;overflow:hidden;
                transform:translateX(100%);transition:transform .35s cubic-bezier(.4,0,.2,1);">

        {{-- ── Header bar ── --}}
        <div style="padding:1.125rem 1.5rem;background:#fff;border-bottom:1px solid #e5e7eb;
                    display:flex;align-items:center;justify-content:space-between;flex-shrink:0;
                    box-shadow:0 1px 4px rgba(0,0,0,.04);">
            <div style="min-width:0;">
                <div style="display:flex;align-items:center;gap:.625rem;flex-wrap:wrap;">
                    <h2 id="so-title" style="font-size:1.0625rem;font-weight:700;color:#111827;margin:0;"></h2>
                    <span id="so-number" style="font-size:.7rem;font-family:monospace;background:#f3f4f6;color:#6b7280;padding:.15rem .625rem;border-radius:9999px;"></span>
                </div>
                <div id="so-badges" style="display:flex;gap:.375rem;flex-wrap:wrap;margin-top:.5rem;"></div>
            </div>
            <button onclick="closeDetail()"
                    style="background:#f3f4f6;border:none;font-size:1.125rem;cursor:pointer;color:#6b7280;
                           width:34px;height:34px;display:flex;align-items:center;justify-content:center;
                           border-radius:50%;transition:background .15s;flex-shrink:0;margin-left:1rem;"
                    onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">✕</button>
        </div>

        {{-- ── Read-only banner (shown only for verified complaints) ── --}}
        <div id="so-readonly-banner"
             style="display:none;align-items:center;gap:.625rem;
                    background:linear-gradient(90deg,#f0fdf4,#dcfce7);
                    border-bottom:1.5px solid #86efac;
                    padding:.625rem 1.5rem;flex-shrink:0;">
            <span style="font-size:1rem;">🔒</span>
            <span style="font-size:.8125rem;font-weight:700;color:#14532d;">
                READ ONLY — Task Verified &amp; Closed. No further admin actions available.
            </span>
        </div>

        {{-- ── Loading state ── --}}
        <div id="so-loading" style="flex:1;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:.875rem;">
            <div style="width:36px;height:36px;border:3px solid #e5e7eb;border-top-color:#4f46e5;border-radius:50%;animation:spin .7s linear infinite;"></div>
            <p style="color:#9ca3af;font-size:.875rem;margin:0;">Loading complaint…</p>
        </div>

        {{-- ── Scrollable content ── --}}
        <div id="so-content" style="display:none;flex:1;overflow-y:auto;padding:1.25rem;display:flex;flex-direction:column;gap:1rem;">

            {{-- Stats strip --}}
            <div id="so-stats" style="display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;"></div>

            {{-- Media gallery --}}
            <div id="so-media-section" style="background:#fff;border-radius:1rem;border:1px solid #e5e7eb;padding:1.125rem;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <p style="font-size:.72rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin:0 0 .875rem;">📎 Media Attachments</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;align-items:start;">
                    <div>
                        <p style="font-size:.72rem;font-weight:600;color:#6b7280;margin:0 0 .625rem;">📸 Before (Citizen)</p>
                        <div id="so-before" style="display:grid;grid-template-columns:repeat(3,1fr);gap:.4rem;"></div>
                    </div>
                    <div>
                        <p style="font-size:.72rem;font-weight:600;color:#6b7280;margin:0 0 .625rem;">✅ After (Engineer Work)</p>
                        <div id="so-after" style="display:grid;grid-template-columns:repeat(3,1fr);gap:.4rem;"></div>
                    </div>
                </div>
            </div>

            {{-- Description --}}
            <div style="background:#fff;border-radius:1rem;border:1px solid #e5e7eb;padding:1.125rem;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <p style="font-size:.72rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin:0 0 .625rem;">📝 Description</p>
                <p id="so-description" style="font-size:.875rem;color:#374151;line-height:1.75;margin:0;"></p>
            </div>

            {{-- Meta + Map --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div style="background:#fff;border-radius:1rem;border:1px solid #e5e7eb;padding:1.125rem;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                    <p style="font-size:.72rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin:0 0 .875rem;">ℹ️ Details</p>
                    <div id="so-meta" style="display:flex;flex-direction:column;gap:.5rem;"></div>
                </div>
                <div style="background:#fff;border-radius:1rem;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                    <p style="font-size:.72rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin:0;padding:.875rem 1.125rem .5rem;">📍 Location</p>
                    <div id="so-map" style="height:200px;"></div>
                </div>
            </div>

            {{-- Status Timeline --}}
            <div style="background:#fff;border-radius:1rem;border:1px solid #e5e7eb;padding:1.125rem;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <p style="font-size:.72rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin:0 0 1rem;">📈 Status Timeline</p>
                <div id="so-timeline"></div>
            </div>

        </div>

        {{-- ── Footer: Admin status change ── --}}
        <div id="so-footer" style="display:none;padding:1rem 1.5rem;border-top:1px solid #e5e7eb;flex-shrink:0;background:#fff;">
            <p style="font-size:.75rem;font-weight:700;color:#374151;margin:0 0 .625rem;">🛡 Admin: Change Status</p>
            <div style="display:flex;gap:.625rem;align-items:center;flex-wrap:wrap;">
                <select id="so-new-status" class="adm-input adm-select" style="flex:1;min-width:140px;">
                    <option value="">— Select next status —</option>
                </select>
                <input id="so-remarks" type="text" class="adm-input" placeholder="Remarks (optional)" style="flex:2;min-width:160px;">
                <button onclick="adminUpdateStatus()" id="so-update-btn" class="adm-btn adm-btn-primary">✅ Update</button>
            </div>
            <div id="so-update-error" style="display:none;color:#dc2626;font-size:.8125rem;margin-top:.5rem;"></div>
        </div>

        {{-- ── Rating panel (only for verified complaints) ── --}}
        <div id="so-rating-panel" style="display:none;padding:1rem 1.5rem;border-top:1px solid #e5e7eb;flex-shrink:0;
             background:linear-gradient(135deg,#f0fdf4,#fafff8);">
            <p style="font-size:.75rem;font-weight:700;color:#14532d;margin:0 0 .75rem;">⭐ Rate Engineer's Work Quality</p>

            {{-- Already rated display --}}
            <div id="so-rating-display" style="display:none;"></div>

            {{-- Star picker --}}
            <div id="so-rating-form">
                <div style="display:flex;align-items:center;gap:.25rem;margin-bottom:.75rem;" id="so-stars">
                    @foreach([1,2,3,4,5] as $star)
                    <button onclick="selectStar({{ $star }})" id="star-{{ $star }}"
                            style="font-size:1.75rem;background:none;border:none;cursor:pointer;padding:.1rem;
                                   transition:transform .1s;line-height:1;"
                            onmouseover="hoverStar({{ $star }})"
                            onmouseout="unhoverStars()">☆</button>
                    @endforeach
                    <span id="so-star-label" style="font-size:.78rem;font-weight:700;color:#6b7280;margin-left:.5rem;"></span>
                </div>
                <textarea id="so-rating-comment" class="adm-input" placeholder="Optional comment about the engineer's work…"
                          style="width:100%;resize:vertical;min-height:60px;font-size:.8125rem;margin-bottom:.5rem;"></textarea>
                <div style="display:flex;gap:.5rem;align-items:center;">
                    <button onclick="submitRating()" id="so-rating-btn"
                            class="adm-btn adm-btn-primary" style="background:linear-gradient(135deg,#10b981,#059669);">
                        ⭐ Submit Rating
                    </button>
                    <span id="so-rating-error" style="color:#dc2626;font-size:.78rem;display:none;"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes spin{to{transform:rotate(360deg)}}
@keyframes fadeUp{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
.so-timeline-step{display:flex;gap:.75rem;padding-bottom:1.125rem;position:relative;animation:fadeUp .3s ease both;}
.so-timeline-step::before{content:'';position:absolute;left:11px;top:24px;bottom:0;width:2px;background:linear-gradient(to bottom,#e5e7eb,transparent);}
.so-timeline-step:last-child::before{display:none;}
.so-stat-card{background:#fff;border:1px solid #e5e7eb;border-radius:.875rem;padding:.875rem 1rem;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.05);}
.so-meta-row{display:flex;justify-content:space-between;align-items:center;padding:.4rem 0;border-bottom:1px solid #f9fafb;font-size:.8125rem;}
.so-meta-row:last-child{border-bottom:none;}
.so-media-thumb{aspect-ratio:1;border-radius:.625rem;overflow:hidden;border:1px solid #e5e7eb;background:#f3f4f6;cursor:pointer;transition:transform .15s,box-shadow .15s;position:relative;display:block;width:100%;}
.so-media-thumb:hover{transform:scale(1.03);box-shadow:0 4px 12px rgba(0,0,0,.12);}
</style>


<script>
// Will be populated from GET /api/v1/complaint-options
let STATUS_CFG = {};
let SEV_CFG    = {};

let currentPage     = 1;
let totalPages      = 1;
let activeComplaintId = null;
let engineers       = [];


function esc(s){return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
function fmt(iso){return new Date(iso).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'});}

function applyFilters(){ currentPage = 1; loadComplaints(); }
function changePage(dir){
    const next = currentPage + dir;
    if (next >= 1 && next <= totalPages) { currentPage = next; loadComplaints(); }
}

async function loadComplaints() {
    document.getElementById('complaints-loading').style.display = 'block';
    document.getElementById('complaints-table').style.display   = 'none';
    document.getElementById('complaints-empty').style.display   = 'none';

    const params = {
        page:     currentPage,
        per_page: 15,
        status:   document.getElementById('f-status').value   || undefined,
        severity: document.getElementById('f-severity').value || undefined,
    };

    try {
        const res   = await axios.get('/api/v1/admin/complaints', {params});
        const items = res.data.data ?? [];
        const meta  = res.data.meta ?? {};
        totalPages  = meta.last_page ?? 1;

        document.getElementById('filter-count').textContent = `${meta.total ?? items.length} complaint(s)`;
        document.getElementById('complaints-loading').style.display = 'none';

        if (!items.length) {
            document.getElementById('complaints-empty').style.display = 'block';
            document.getElementById('pagination').style.display = 'none';
            return;
        }

        document.getElementById('complaints-table').style.display = 'block';
        document.getElementById('pagination').style.display = 'flex';
        document.getElementById('pag-info').textContent = `Page ${meta.current_page} of ${meta.last_page} (${meta.total} total)`;
        document.getElementById('pag-prev').disabled = currentPage <= 1;
        document.getElementById('pag-next').disabled = currentPage >= totalPages;

        document.getElementById('complaints-table').innerHTML = `
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1.5px solid #f3f4f6;background:#fafafa;">
                        ${['#','Title / Location','Category','Severity','Status','Engineer','Filed','Actions'].map(h=>
                            `<th style="padding:.625rem 1rem;text-align:left;font-size:.72rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap;">${h}</th>`
                        ).join('')}
                    </tr>
                </thead>
                <tbody>
                    ${items.map(c => {
                        const s   = STATUS_CFG[c.status]??{label:c.status,color:'#374151',bg:'#f3f4f6'};
                        const sev = SEV_CFG[c.severity]??{bg:'#f3f4f6',color:'#374151'};
                        return `<tr style="border-bottom:1px solid #f9fafb;transition:background .1s;"
                                    onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
                            <td style="padding:.75rem 1rem;font-size:.72rem;color:#9ca3af;white-space:nowrap;">${esc(c.complaint_number)}</td>
                            <td style="padding:.75rem 1rem;max-width:200px;">
                                <div style="font-size:.875rem;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(c.title)}</div>
                                <div style="font-size:.75rem;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">📍 ${esc(c.location)}</div>
                            </td>
                            <td style="padding:.75rem 1rem;font-size:.8125rem;color:#374151;white-space:nowrap;">${esc(c.category?.name??'—')}</td>
                            <td style="padding:.75rem 1rem;">
                                <span style="background:${sev.bg};color:${sev.color};font-size:.7rem;font-weight:700;padding:.2rem .625rem;border-radius:9999px;white-space:nowrap;">${(c.severity??'').charAt(0).toUpperCase()+(c.severity??'').slice(1)}</span>
                            </td>
                            <td style="padding:.75rem 1rem;">
                                <span style="background:${s.bg};color:${s.color};font-size:.7rem;font-weight:700;padding:.2rem .625rem;border-radius:9999px;white-space:nowrap;">${s.label}</span>
                            </td>
                            <td style="padding:.75rem 1rem;font-size:.8125rem;color:#374151;white-space:nowrap;">
                                ${c.assigned_engineer ? `<span style="display:flex;align-items:center;gap:.25rem;">👷 ${esc(c.assigned_engineer.name)}</span>` : '<span style="color:#9ca3af;">Unassigned</span>'}
                            </td>
                            <td style="padding:.75rem 1rem;font-size:.8125rem;color:#6b7280;white-space:nowrap;">${fmt(c.created_at)}</td>
                            <td style="padding:.75rem 1rem;white-space:nowrap;">
                                <button class="adm-btn adm-btn-sm adm-btn-outline" style="margin-right:.375rem;" onclick="openDetail(${c.id})">
                                    🔍 View
                                </button>
                                ${c.status === 'verified'
                                    ? `<span style="display:inline-flex;align-items:center;gap:.25rem;background:#f0fdf4;color:#14532d;border:1px solid #86efac;border-radius:.375rem;padding:.2rem .625rem;font-size:.72rem;font-weight:700;">🔒 Closed</span>`
                                    : `<button class="adm-btn adm-btn-sm adm-btn-primary" onclick="openAssignModal(${c.id},'${esc(c.title)}','${esc(c.complaint_number)}')">
                                            👷 Assign
                                       </button>`
                                }
                            </td>
                        </tr>`;
                    }).join('')}
                </tbody>
            </table>`;
    } catch(e) {
        document.getElementById('complaints-loading').innerHTML =
            '<div style="padding:2rem;text-align:center;color:#dc2626;">Failed to load. <button onclick="loadComplaints()" style="color:#4f46e5;background:none;border:none;cursor:pointer;font-weight:600;">Retry</button></div>';
    }
}

// ── Load engineers list for modal ──
async function loadEngineers() {
    try {
        const res = await axios.get('/api/v1/admin/users', {params:{role:'engineer',per_page:100}});
        engineers = (res.data.data ?? res.data).filter(u => u.role === 'engineer');
        const sel = document.getElementById('engineer-select');
        sel.innerHTML = engineers.length
            ? '<option value="">— Select engineer —</option>' + engineers.map(e => `<option value="${e.id}">👷 ${esc(e.name)} (${esc(e.email)})</option>`).join('')
            : '<option value="">No engineers available</option>';
    } catch(_) {
        document.getElementById('engineer-select').innerHTML = '<option>Failed to load</option>';
    }
}

function openAssignModal(id, title, number) {
    activeComplaintId = id;
    document.getElementById('modal-complaint-info').innerHTML =
        `<strong>${esc(title)}</strong><br><span style="color:#9ca3af;font-size:.78rem;">${esc(number)}</span>`;
    document.getElementById('assign-error').style.display = 'none';
    document.getElementById('assign-modal').classList.add('open');
    loadEngineers();
}
function closeAssignModal() {
    document.getElementById('assign-modal').classList.remove('open');
    activeComplaintId = null;
}
document.getElementById('assign-modal').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeAssignModal();
});

async function submitAssign() {
    const engineerId = document.getElementById('engineer-select').value;
    if (!engineerId) {
        document.getElementById('assign-error').textContent = 'Please select an engineer.';
        document.getElementById('assign-error').style.display = 'block';
        return;
    }
    const btn = document.getElementById('assign-btn');
    btn.disabled = true; btn.textContent = '⏳ Assigning…';
    try {
        await axios.patch(`/api/v1/admin/complaints/${activeComplaintId}/assign`, {engineer_id: engineerId});
        closeAssignModal();
        loadComplaints();
    } catch(e) {
        document.getElementById('assign-error').textContent = e.response?.data?.message ?? 'Assignment failed.';
        document.getElementById('assign-error').style.display = 'block';
        btn.disabled = false; btn.textContent = 'Assign';
    }
}

// Status badge configs keyed by value
const STATUS_COLORS = {
    pending:               {color:'#92400e', bg:'#fffbeb'},
    under_review:          {color:'#1e40af', bg:'#eff6ff'},
    in_progress:           {color:'#1d4ed8', bg:'#dbeafe'},
    awaiting_verification: {color:'#c2410c', bg:'#fff7ed'},
    verified:              {color:'#14532d', bg:'#f0fdf4'},
    rejected:              {color:'#7f1d1d', bg:'#fef2f2'},
};
const SEV_COLORS = {
    low:       {bg:'#d1fae5', color:'#065f46'},
    medium:    {bg:'#fef9c3', color:'#713f12'},
    high:      {bg:'#ffedd5', color:'#7c2d12'},
    emergency: {bg:'#fee2e2', color:'#7f1d1d'},
};

async function loadOptions() {
    try {
        const res  = await fetch('/api/v1/complaint-options');
        const data = await res.json();

        // Build runtime lookup maps
        data.statuses.forEach(s => {
            STATUS_CFG[s.value] = {
                label: s.label,
                icon:  s.icon,
                ...(STATUS_COLORS[s.value] ?? {color:'#374151', bg:'#f3f4f6'})
            };
        });
        data.severities.forEach(s => {
            SEV_CFG[s.value] = {
                label: s.label,
                icon:  s.icon,
                ...(SEV_COLORS[s.value] ?? {bg:'#f3f4f6', color:'#374151'})
            };
        });

        // Populate status dropdown
        const fStatus = document.getElementById('f-status');
        fStatus.innerHTML =
            '<option value="">All Statuses</option>' +
            data.statuses.map(s =>
                `<option value="${s.value}">${s.icon} ${s.label}</option>`
            ).join('');
        fStatus.disabled = false;

        // Populate severity dropdown
        const fSev = document.getElementById('f-severity');
        fSev.innerHTML =
            '<option value="">All Severities</option>' +
            data.severities.map(s =>
                `<option value="${s.value}">${s.icon} ${s.label}</option>`
            ).join('');
        fSev.disabled = false;

    } catch(_) {
        // Fallback — re-enable selects with basic options
        ['f-status','f-severity'].forEach(id => {
            document.getElementById(id).disabled = false;
            document.getElementById(id).innerHTML =
                '<option value="">(Failed to load)</option>';
        });
    }

    // Load table after options are ready
    loadComplaints();
}

document.addEventListener('DOMContentLoaded', loadOptions);

// ── Slide-over detail ─────────────────────────────────────────────────────────
let detailComplaintId = null;

let soMap = null; // Leaflet map instance

async function openDetail(id) {
    detailComplaintId = id;
    const so = document.getElementById('detail-slideover');
    const panel = document.getElementById('slideover-panel');
    so.style.display = 'block';
    setTimeout(() => panel.style.transform = 'translateX(0)', 10);
    document.getElementById('so-loading').style.display  = 'flex';
    document.getElementById('so-content').style.display  = 'none';
    document.getElementById('so-footer').style.display   = 'none';
    document.getElementById('so-readonly-banner').style.display = 'none';

    try {
        const detailRes = await axios.get(`/api/v1/admin/complaints/${id}`);
        const c = detailRes.data.data;
        const s   = STATUS_CFG[c.status]  ?? {label:c.status,  color:'#374151', bg:'#f3f4f6', icon:'📌'};
        const sev = SEV_CFG[c.severity]   ?? {bg:'#f3f4f6', color:'#374151', label:c.severity, icon:'•'};

        // ── Header ──────────────────────────────────────────────────────────
        document.getElementById('so-title').textContent  = c.title;
        document.getElementById('so-number').textContent = c.complaint_number;
        document.getElementById('so-badges').innerHTML =
            `<span style="background:${s.bg};color:${s.color};border:1px solid ${s.color}22;font-size:.75rem;font-weight:700;padding:.25rem .75rem;border-radius:9999px;">${s.icon} ${s.label}</span>` +
            `<span style="background:${sev.bg};color:${sev.color};font-size:.75rem;font-weight:700;padding:.25rem .75rem;border-radius:9999px;">${sev.icon??''} ${sev.label??c.severity}</span>` +
            (c.category ? `<span style="background:#f3f4f6;color:#6b7280;font-size:.75rem;font-weight:600;padding:.25rem .75rem;border-radius:9999px;">${esc(c.category.icon??'')} ${esc(c.category.name)}</span>` : '') +
            (c.is_anonymous ? `<span style="background:#fef3c7;color:#92400e;font-size:.75rem;font-weight:600;padding:.25rem .75rem;border-radius:9999px;">👤 Anonymous</span>` : '');

        // ── Stats strip ──────────────────────────────────────────────────────
        const filed = new Date(c.created_at).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'});
        const updated = new Date(c.updated_at).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'});
        document.getElementById('so-stats').innerHTML = [
            {icon:'👁️', value:c.views_count??0, label:'Views',   color:'#4f46e5'},
            {icon:'👍', value:c.votes_count??0, label:'Votes',   color:'#0284c7'},
            {icon:'📅', value:filed,            label:'Filed',   color:'#16a34a'},
            {icon:'🔄', value:updated,          label:'Updated', color:'#d97706'},
        ].map(st=>`
            <div class="so-stat-card">
                <div style="font-size:1.25rem;margin-bottom:.25rem;">${st.icon}</div>
                <div style="font-size:1rem;font-weight:800;color:${st.color};line-height:1.1;">${st.value}</div>
                <div style="font-size:.65rem;font-weight:600;color:#9ca3af;margin-top:.125rem;text-transform:uppercase;">${st.label}</div>
            </div>`).join('');

        // ── Media ────────────────────────────────────────────────────────────
        const before = (c.media??[]).filter(m=>m.stage==='before');
        const after  = (c.media??[]).filter(m=>m.stage==='after');

        const renderMedia = items => items.length ? items.map(m => m.file_type==='video'
            ? `<a href="${esc(m.cloud_url)}" target="_blank" class="so-media-thumb" title="${esc(m.original_name??'')}">
                   <video src="${esc(m.cloud_url)}" style="width:100%;height:100%;object-fit:cover;pointer-events:none;" muted playsinline
                          onmouseover="this.play()" onmouseout="this.pause();this.currentTime=0;"></video>
                   <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none;">
                       <span style="background:rgba(0,0,0,.55);color:#fff;border-radius:9999px;padding:.2rem .5rem;font-size:.65rem;font-weight:700;">▶ VIDEO</span>
                   </div>
               </a>`
            : `<a href="${esc(m.cloud_url)}" target="_blank" class="so-media-thumb" title="${esc(m.original_name??'')}">
                   <img src="${esc(m.cloud_url)}" style="width:100%;height:100%;object-fit:cover;" loading="lazy">
               </a>`
        ).join('')
        : `<div style="aspect-ratio:1;border-radius:.625rem;background:#f9fafb;border:1.5px dashed #e5e7eb;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:.25rem;">
               <span style="font-size:1.5rem;opacity:.4;">📷</span>
               <span style="font-size:.65rem;color:#9ca3af;">No media</span>
           </div>`;

        document.getElementById('so-before').innerHTML = renderMedia(before);
        document.getElementById('so-after').innerHTML  = renderMedia(after) === renderMedia([])
            ? `<div style="aspect-ratio:1;border-radius:.625rem;background:#f0fdf4;border:1.5px dashed #86efac;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:.25rem;">
                   <span style="font-size:1.5rem;opacity:.5;">🔧</span>
                   <span style="font-size:.65rem;color:#16a34a;">Awaiting fix</span>
               </div>`
            : renderMedia(after);

        // ── Description ──────────────────────────────────────────────────────
        document.getElementById('so-description').textContent = c.description ?? '—';

        // ── Meta rows ────────────────────────────────────────────────────────
        document.getElementById('so-meta').innerHTML = [
            ['📍 Location',    c.location??'—'],
            ['👤 Reported By', c.submitted_by??(c.is_anonymous?'Anonymous':'—')],
            ['👷 Engineer',    c.assigned_engineer?.name??'Unassigned'],
            ['📂 Category',    c.category?.name??'—'],
            ['🔢 Complaint #', c.complaint_number],
            ...(c.resolved_at ? [['✅ Resolved', new Date(c.resolved_at).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'})]] : []),
        ].map(([k,v])=>`
            <div class="so-meta-row">
                <span style="color:#9ca3af;font-size:.75rem;font-weight:500;">${k}</span>
                <span style="color:#111827;font-size:.8125rem;font-weight:600;text-align:right;max-width:55%;word-break:break-word;">${esc(String(v))}</span>
            </div>`).join('');

        // ── Leaflet mini-map ─────────────────────────────────────────────────
        if (c.latitude && c.longitude) {
            setTimeout(() => {
                if (soMap) { soMap.remove(); soMap = null; }
                soMap = L.map('so-map', {zoomControl:false, dragging:false, scrollWheelZoom:false})
                          .setView([c.latitude, c.longitude], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                    {attribution:'© OpenStreetMap'}).addTo(soMap);
                L.marker([c.latitude, c.longitude]).addTo(soMap)
                 .bindPopup(`<strong>${esc(c.title)}</strong><br>${esc(c.location)}`).openPopup();
            }, 50);
        } else {
            document.getElementById('so-map').innerHTML =
                '<div style="height:100%;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:.8rem;">No location data</div>';
        }

        // ── Status Timeline ───────────────────────────────────────────────────
        const timeline = c.status_histories ?? [];
        document.getElementById('so-timeline').innerHTML = timeline.length
            ? timeline.map((h, i) => {
                const ns = STATUS_CFG[h.new_status]??{label:h.new_status,color:'#374151',bg:'#f3f4f6',icon:'📌'};
                const isWorkRejection = h.old_status === 'awaiting_verification' && h.new_status === 'in_progress';
                const isVerified      = h.new_status === 'verified';
                const actor = h.changed_by ? `${esc(h.changed_by.name)} (ID: ${h.changed_by.id})` : 'Admin';

                let dotBg = ns.bg, dotColor = ns.color, dotIcon = ns.icon;
                let badgeBg = ns.bg, badgeColor = ns.color;
                let labelHtml, remarksHtml = '';

                if (isWorkRejection) {
                    dotBg = '#fef2f2'; dotColor = '#991b1b'; dotIcon = '🚫';
                    badgeBg = '#fef2f2'; badgeColor = '#991b1b';
                    labelHtml = '🚫 Work Rejected';
                    remarksHtml = `
                        <div style="background:#fef2f2;border-left:3px solid #fca5a5;border-radius:0 .375rem .375rem 0;
                                    padding:.5rem .75rem;margin:.375rem 0;font-size:.78rem;">
                            <div><span style="font-weight:700;color:#991b1b;">Rejected by:</span>
                                 <span style="color:#374151;"> ${actor}</span></div>
                            ${h.remarks ? `<div style="margin-top:.25rem;"><span style="font-weight:700;color:#991b1b;">Reason:</span>
                                 <span style="color:#374151;font-style:italic;"> "${esc(h.remarks)}"</span></div>` : ''}
                        </div>`;
                } else if (isVerified) {
                    dotBg = '#f0fdf4'; dotColor = '#14532d'; dotIcon = '✅';
                    badgeBg = '#f0fdf4'; badgeColor = '#14532d';
                    labelHtml = '✅ Task Completed — Verified';
                    remarksHtml = `
                        <div style="background:#f0fdf4;border-left:3px solid #86efac;border-radius:0 .375rem .375rem 0;
                                    padding:.5rem .75rem;margin:.375rem 0;font-size:.78rem;">
                            <div><span style="font-weight:700;color:#14532d;">Verified by:</span>
                                 <span style="color:#374151;"> ${actor}</span></div>
                            ${h.remarks ? `<div style="margin-top:.25rem;color:#6b7280;font-style:italic;">"${esc(h.remarks)}"</div>` : ''}
                        </div>`;
                } else if (!h.old_status) {
                    labelHtml = '📋 Complaint Filed';
                    if (h.remarks) remarksHtml = `<p style="font-size:.78rem;color:#6b7280;margin:.25rem 0 0;font-style:italic;">"${esc(h.remarks)}"</p>`;
                } else {
                    const from = h.old_status.replace(/_/g,' ').replace(/\b\w/g, c => c.toUpperCase());
                    const to   = h.new_status.replace(/_/g,' ').replace(/\b\w/g, c => c.toUpperCase());
                    labelHtml = `${from} → ${to}`;
                    if (h.remarks) remarksHtml = `<p style="font-size:.78rem;color:#6b7280;margin:.25rem 0 0;font-style:italic;">"${esc(h.remarks)}"</p>`;
                }

                const dateStr = new Date(h.created_at).toLocaleString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
                return `<div class="so-timeline-step" style="animation-delay:${i*0.05}s;">
                    <div style="width:24px;height:24px;border-radius:50%;background:${dotBg};color:${dotColor};
                                display:flex;align-items:center;justify-content:center;font-size:.75rem;
                                font-weight:700;flex-shrink:0;border:2px solid ${dotColor}33;">${dotIcon}</div>
                    <div style="flex:1;min-width:0;padding-top:.1rem;">
                        <span style="background:${badgeBg};color:${badgeColor};font-size:.7rem;font-weight:700;
                                     padding:.2rem .625rem;border-radius:9999px;display:inline-block;">${labelHtml}</span>
                        ${remarksHtml}
                        <p style="font-size:.7rem;color:#9ca3af;margin:.2rem 0 0;">
                            ${dateStr}${!isWorkRejection && !isVerified && h.changed_by ? ` · <strong>${esc(h.changed_by.name)}</strong>` : ''}
                        </p>
                    </div>
                </div>`;
            }).join('')
            : `<div style="display:flex;align-items:center;gap:.75rem;color:#9ca3af;font-size:.875rem;padding:.5rem 0;">
                   <span style="font-size:1.5rem;">📭</span> No history yet.
               </div>`;


        // ── Footer status dropdown — only valid next transitions from backend ──
        const nextStatuses = c.next_statuses ?? [];
        const sel = document.getElementById('so-new-status');
        if (nextStatuses.length === 0) {
            // Terminal state — nothing to transition to
            sel.innerHTML = `<option value="" disabled selected>✅ No further actions available</option>`;
            sel.disabled = true;
            document.getElementById('so-update-btn').disabled = true;
            document.getElementById('so-update-btn').style.opacity = '0.45';
        } else {
            sel.disabled = false;
            document.getElementById('so-update-btn').disabled = false;
            document.getElementById('so-update-btn').style.opacity = '1';
            sel.innerHTML =
                '<option value="">— Select next status —</option>' +
                nextStatuses.map(statusKey => {
                    const cfg = STATUS_CFG[statusKey] ?? {label: statusKey.replace(/_/g,' '), icon:'📌'};
                    return `<option value="${statusKey}">${cfg.icon} ${cfg.label}</option>`;
                }).join('');
        }
        document.getElementById('so-remarks').value = '';
        document.getElementById('so-update-error').style.display = 'none';

        // ── Rating panel & read-only mode for verified complaints ──
        const ratingPanel = document.getElementById('so-rating-panel');
        const soFooter    = document.getElementById('so-footer');
        const soReadOnly  = document.getElementById('so-readonly-banner');

        if (c.status === 'verified') {
            // Show rating, hide status-change footer
            ratingPanel.style.display = 'block';
            renderRatingPanel(c.engineer_rating);
            soFooter.style.display    = 'none';
            if (soReadOnly) soReadOnly.style.display = 'flex';
        } else {
            ratingPanel.style.display = 'none';
            soFooter.style.display    = 'block';
            if (soReadOnly) soReadOnly.style.display = 'none';
        }

        // Show
        document.getElementById('so-loading').style.display = 'none';
        document.getElementById('so-content').style.display = 'flex';

    } catch(e) {
        document.getElementById('so-loading').innerHTML =
            `<div style="text-align:center;color:#dc2626;padding:2rem;">
                <div style="font-size:2rem;margin-bottom:.5rem;">⚠️</div>
                <p style="margin:0 0 1rem;">Failed to load complaint.</p>
                <button onclick="openDetail(detailComplaintId)" style="background:#4f46e5;color:#fff;border:none;border-radius:.625rem;padding:.5rem 1.25rem;font-size:.875rem;cursor:pointer;">Retry</button>
             </div>`;
    }
}


function closeDetail() {
    const panel = document.getElementById('slideover-panel');
    panel.style.transform = 'translateX(100%)';
    setTimeout(() => document.getElementById('detail-slideover').style.display = 'none', 300);
    detailComplaintId = null;
}

async function adminUpdateStatus() {
    const newStatus = document.getElementById('so-new-status').value;
    const remarks   = document.getElementById('so-remarks').value.trim();
    if (!newStatus) {
        document.getElementById('so-update-error').textContent = 'Please select a status.';
        document.getElementById('so-update-error').style.display = 'block';
        return;
    }
    const btn = document.getElementById('so-update-btn');
    btn.disabled = true; btn.textContent = '⏳ Saving…';
    try {
        await axios.patch(`/api/v1/admin/complaints/${detailComplaintId}/status`, {status: newStatus, remarks: remarks||null});
        btn.textContent = '✅ Updated!';
        setTimeout(() => { btn.disabled = false; btn.textContent = '✅ Update'; }, 1500);
        openDetail(detailComplaintId); // refresh panel
        loadComplaints();              // refresh table
    } catch(e) {
        document.getElementById('so-update-error').textContent = e.response?.data?.message ?? 'Update failed.';
        document.getElementById('so-update-error').style.display = 'block';
        btn.disabled = false; btn.textContent = '✅ Update';
    }
}

// ── Star rating helpers ─────────────────────────────────────────────────────
let selectedStarValue = 0;
const STAR_LABELS = {1:'Very Poor 😡',2:'Poor 😞',3:'Average 😐',4:'Good 😊',5:'Excellent 😍'};

function renderRatingPanel(existingRating) {
    selectedStarValue = existingRating ? existingRating.score : 0;
    const display = document.getElementById('so-rating-display');
    const form    = document.getElementById('so-rating-form');

    if (existingRating) {
        display.style.display = 'block';
        const stars  = '★'.repeat(existingRating.score) + '☆'.repeat(5 - existingRating.score);
        const ratedBy = existingRating.rated_by ? esc(existingRating.rated_by.name) : 'Admin';
        display.innerHTML = `
            <div style="background:#fff;border:1px solid #bbf7d0;border-radius:.625rem;padding:.625rem .875rem;
                        margin-bottom:.75rem;font-size:.8125rem;">
                <div style="font-size:1.25rem;color:#f59e0b;letter-spacing:.1em;">${stars}</div>
                <div style="font-weight:700;color:#15803d;">${esc(existingRating.label)} — ${existingRating.score}/5</div>
                ${existingRating.comment ? `<div style="color:#6b7280;margin-top:.2rem;font-style:italic;">"${esc(existingRating.comment)}"</div>` : ''}
                <div style="color:#9ca3af;font-size:.72rem;margin-top:.2rem;">Rated by ${ratedBy}</div>
            </div>
            <p style="font-size:.72rem;color:#6b7280;margin:0 0 .5rem;">Update rating:</p>`;
    } else {
        display.style.display = 'none';
    }

    form.style.display = 'block';
    renderStars(selectedStarValue);
    document.getElementById('so-rating-comment').value = existingRating ? (existingRating.comment ?? '') : '';
    document.getElementById('so-rating-btn').textContent = existingRating ? '⭐ Update Rating' : '⭐ Submit Rating';
}

function renderStars(filled) {
    for (let i = 1; i <= 5; i++) {
        const btn = document.getElementById('star-' + i);
        if (!btn) continue;
        btn.textContent = i <= filled ? '★' : '☆';
        btn.style.color = i <= filled ? '#f59e0b' : '#d1d5db';
        btn.style.transform = i <= filled ? 'scale(1.15)' : 'scale(1)';
    }
    const label = document.getElementById('so-star-label');
    if (label) label.textContent = filled ? STAR_LABELS[filled] : 'Select a rating';
}

function selectStar(n) { selectedStarValue = n; renderStars(n); }
function hoverStar(n)  { renderStars(n); }
function unhoverStars(){ renderStars(selectedStarValue); }

async function submitRating() {
    const err = document.getElementById('so-rating-error');
    if (!selectedStarValue) {
        err.textContent = 'Please select a star rating first.';
        err.style.display = 'inline';
        return;
    }
    const btn     = document.getElementById('so-rating-btn');
    const comment = document.getElementById('so-rating-comment').value.trim();
    btn.disabled  = true;
    btn.textContent = '⏳ Saving…';
    err.style.display = 'none';
    try {
        await axios.post(`/api/v1/admin/complaints/${detailComplaintId}/rate`, {
            rating:  selectedStarValue,
            comment: comment || null,
        });
        btn.textContent = '✅ Saved!';
        setTimeout(() => openDetail(detailComplaintId), 1000); // refresh panel
    } catch(e) {
        err.textContent = e.response?.data?.message ?? 'Failed to save rating.';
        err.style.display = 'inline';
        btn.disabled = false;
        btn.textContent = '⭐ Submit Rating';
    }
}
</script>
@endsection

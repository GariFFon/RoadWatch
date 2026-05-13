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
<div id="detail-slideover"
     style="display:none;position:fixed;inset:0;z-index:300;display:none;">
    {{-- Backdrop --}}
    <div id="slideover-backdrop"
         style="position:absolute;inset:0;background:rgba(0,0,0,.45);"
         onclick="closeDetail()"></div>
    {{-- Panel --}}
    <div id="slideover-panel"
         style="position:absolute;top:0;right:0;bottom:0;width:720px;max-width:100vw;
                background:#fff;box-shadow:-8px 0 40px rgba(0,0,0,.15);
                display:flex;flex-direction:column;overflow:hidden;
                transform:translateX(100%);transition:transform .3s ease;">
        {{-- Header --}}
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <div>
                <h2 id="so-title" style="font-size:1rem;font-weight:700;color:#111827;margin:0;"></h2>
                <p id="so-number" style="font-size:.78rem;color:#9ca3af;margin:.1rem 0 0;"></p>
            </div>
            <button onclick="closeDetail()"
                    style="background:none;border:none;font-size:1.375rem;cursor:pointer;color:#9ca3af;
                           width:32px;height:32px;display:flex;align-items:center;justify-content:center;
                           border-radius:50%;transition:background .15s;"
                    onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='none'">✕</button>
        </div>

        {{-- Loading --}}
        <div id="so-loading" style="flex:1;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:.75rem;">
            <div style="width:32px;height:32px;border:3px solid #e5e7eb;border-top-color:#4f46e5;border-radius:50%;animation:spin .7s linear infinite;"></div>
            <p style="color:#9ca3af;font-size:.875rem;">Loading complaint…</p>
        </div>

        {{-- Content --}}
        <div id="so-content" style="display:none;flex:1;overflow-y:auto;padding:1.5rem;display:flex;flex-direction:column;gap:1.5rem;">

            {{-- Badges row --}}
            <div id="so-badges" style="display:flex;gap:.5rem;flex-wrap:wrap;"></div>

            {{-- Before / After photos --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div>
                    <p style="font-size:.72rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.625rem;">📸 Before (Citizen)</p>
                    <div id="so-before" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:.5rem;"></div>
                </div>
                <div>
                    <p style="font-size:.72rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.625rem;">✅ After (Engineer Work)</p>
                    <div id="so-after" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:.5rem;"></div>
                </div>
            </div>

            {{-- Description --}}
            <div>
                <p style="font-size:.72rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.5rem;">📝 Description</p>
                <p id="so-description" style="font-size:.875rem;color:#374151;line-height:1.7;background:#f9fafb;border-radius:.75rem;padding:.875rem;"></p>
            </div>

            {{-- Meta row --}}
            <div id="so-meta" style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;background:#f9fafb;border-radius:.875rem;padding:1rem;"></div>

            {{-- Timeline --}}
            <div>
                <p style="font-size:.72rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.875rem;">📈 Status Timeline</p>
                <div id="so-timeline" style="display:flex;flex-direction:column;"></div>
            </div>

        </div>

        {{-- Footer: admin status change --}}
        <div id="so-footer" style="display:none;padding:1.25rem 1.5rem;border-top:1px solid #f3f4f6;flex-shrink:0;background:#fafafa;">
            <p style="font-size:.78rem;font-weight:700;color:#374151;margin-bottom:.625rem;">🛡 Admin: Change Status</p>
            <div style="display:flex;gap:.75rem;align-items:flex-end;flex-wrap:wrap;">
                <div style="flex:1;min-width:140px;">
                    <select id="so-new-status" class="adm-input adm-select" style="width:100%;">
                        <option value="">— Select new status —</option>
                    </select>
                </div>
                <div style="flex:2;min-width:180px;">
                    <input id="so-remarks" type="text" class="adm-input" placeholder="Remarks (optional)" style="width:100%;">
                </div>
                <button onclick="adminUpdateStatus()" id="so-update-btn" class="adm-btn adm-btn-primary">✅ Update</button>
            </div>
            <div id="so-update-error" style="display:none;color:#dc2626;font-size:.8125rem;margin-top:.5rem;"></div>
        </div>
    </div>
</div>

<style>
@keyframes spin{to{transform:rotate(360deg)}}
.so-timeline-step{display:flex;gap:.75rem;padding-bottom:1rem;position:relative;}
.so-timeline-step::before{content:'';position:absolute;left:10px;top:22px;bottom:0;width:2px;background:#f3f4f6;}
.so-timeline-step:last-child::before{display:none;}
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
                                <button class="adm-btn adm-btn-sm adm-btn-primary" onclick="openAssignModal(${c.id},'${esc(c.title)}','${esc(c.complaint_number)}')">
                                    👷 Assign
                                </button>
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
    pending:      {color:'#92400e', bg:'#fffbeb'},
    under_review: {color:'#1e40af', bg:'#eff6ff'},
    in_progress:  {color:'#1d4ed8', bg:'#dbeafe'},
    resolved:     {color:'#14532d', bg:'#f0fdf4'},
    rejected:     {color:'#7f1d1d', bg:'#fef2f2'},
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

async function openDetail(id) {
    detailComplaintId = id;
    const so = document.getElementById('detail-slideover');
    const panel = document.getElementById('slideover-panel');
    so.style.display = 'block';
    setTimeout(() => panel.style.transform = 'translateX(0)', 10);
    document.getElementById('so-loading').style.display  = 'flex';
    document.getElementById('so-content').style.display  = 'none';
    document.getElementById('so-footer').style.display   = 'none';

    try {
        const [detailRes, optRes] = await Promise.all([
            axios.get(`/api/v1/admin/complaints/${id}`),
            fetch('/api/v1/complaint-options').then(r => r.json()),
        ]);
        const c = detailRes.data.data;

        // Header
        document.getElementById('so-title').textContent  = c.title;
        document.getElementById('so-number').textContent = c.complaint_number;

        // Badges
        const s   = STATUS_CFG[c.status]  ?? {label:c.status,  color:'#374151', bg:'#f3f4f6', icon:'📌'};
        const sev = SEV_CFG[c.severity]   ?? {bg:'#f3f4f6', color:'#374151'};
        document.getElementById('so-badges').innerHTML =
            `<span style="background:${s.bg};color:${s.color};font-size:.78rem;font-weight:700;padding:.25rem .75rem;border-radius:9999px;">${s.icon} ${s.label}</span>` +
            `<span style="background:${sev.bg};color:${sev.color};font-size:.78rem;font-weight:700;padding:.25rem .75rem;border-radius:9999px;">${(c.severity??'').charAt(0).toUpperCase()+(c.severity??'').slice(1)}</span>` +
            (c.category ? `<span style="background:#f3f4f6;color:#6b7280;font-size:.78rem;font-weight:600;padding:.25rem .75rem;border-radius:9999px;">${esc(c.category.name)}</span>` : '');

        // Photos
        const before = (c.media??[]).filter(m => m.stage==='before');
        const after  = (c.media??[]).filter(m => m.stage==='after');
        document.getElementById('so-before').innerHTML = before.length
            ? before.map(m => `<a href="${esc(m.cloud_url)}" target="_blank" style="display:block;aspect-ratio:1;border-radius:.625rem;overflow:hidden;background:#f3f4f6;"><img src="${esc(m.cloud_url)}" style="width:100%;height:100%;object-fit:cover;"></a>`).join('')
            : '<p style="color:#9ca3af;font-size:.78rem;">No photos.</p>';
        document.getElementById('so-after').innerHTML = after.length
            ? after.map(m => m.file_type==='video'
                ? `<video src="${esc(m.cloud_url)}" controls style="width:100%;aspect-ratio:1;border-radius:.625rem;background:#000;object-fit:cover;"></video>`
                : `<a href="${esc(m.cloud_url)}" target="_blank" style="display:block;aspect-ratio:1;border-radius:.625rem;overflow:hidden;background:#f3f4f6;"><img src="${esc(m.cloud_url)}" style="width:100%;height:100%;object-fit:cover;"></a>`
            ).join('')
            : '<p style="color:#9ca3af;font-size:.78rem;">Engineer has not uploaded evidence yet.</p>';

        // Description
        document.getElementById('so-description').textContent = c.description ?? '—';

        // Meta
        document.getElementById('so-meta').innerHTML = [
            {label:'Location',   value:c.location??'—'},
            {label:'Reported By', value:c.submitted_by??'Anonymous'},
            {label:'Engineer',   value:c.assigned_engineer?.name??'Unassigned'},
        ].map(m => `<div>
            <p style="font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.25rem;">${m.label}</p>
            <p style="font-size:.8125rem;font-weight:600;color:#374151;">${esc(m.value)}</p>
        </div>`).join('');

        // Timeline
        const timeline = c.status_histories ?? [];
        document.getElementById('so-timeline').innerHTML = timeline.length
            ? timeline.map((h, i) => {
                const ns = STATUS_CFG[h.new_status]??{label:h.new_status,color:'#374151',bg:'#f3f4f6',icon:'📌'};
                return `<div class="so-timeline-step">
                    <div style="width:22px;height:22px;border-radius:50%;background:${ns.bg};color:${ns.color};display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;flex-shrink:0;">${ns.icon}</div>
                    <div style="flex:1;min-width:0;">
                        <span style="background:${ns.bg};color:${ns.color};font-size:.7rem;font-weight:700;padding:.15rem .5rem;border-radius:9999px;">${ns.label}</span>
                        ${h.remarks ? `<p style="font-size:.78rem;color:#6b7280;margin:.2rem 0;">"${esc(h.remarks)}"</p>` : ''}
                        <p style="font-size:.72rem;color:#9ca3af;">${new Date(h.created_at).toLocaleString('en-IN')}${h.changed_by?' · '+esc(h.changed_by.name):''}</p>
                    </div>
                </div>`;
            }).join('')
            : '<p style="color:#9ca3af;font-size:.875rem;">No history yet.</p>';

        // Status dropdown in footer — all statuses available to admin
        const sel = document.getElementById('so-new-status');
        sel.innerHTML = '<option value="">— Select new status —</option>' +
            optRes.statuses.map(st => `<option value="${st.value}">${st.icon} ${st.label}</option>`).join('');
        document.getElementById('so-remarks').value = '';
        document.getElementById('so-update-error').style.display = 'none';

        // Show
        document.getElementById('so-loading').style.display = 'none';
        document.getElementById('so-content').style.display = 'flex';
        document.getElementById('so-footer').style.display  = 'block';

    } catch(e) {
        document.getElementById('so-loading').innerHTML =
            '<p style="color:#dc2626;">Failed to load. <button onclick="openDetail(detailComplaintId)" style="color:#4f46e5;background:none;border:none;cursor:pointer;font-weight:600;">Retry</button></p>';
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
</script>
@endsection

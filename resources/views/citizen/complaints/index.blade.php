@extends('layouts.citizen')

@section('title', 'My Complaints')

@section('content')

{{-- ── Success Snackbar ── --}}
<div id="snackbar"
     style="display:none;position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%) translateY(20px);
            background:#111827;color:#fff;padding:.75rem 1.375rem;border-radius:9999px;
            font-size:.875rem;font-weight:600;box-shadow:0 8px 24px rgba(0,0,0,.22);
            z-index:9999;display:none;align-items:center;gap:.625rem;
            transition:transform .35s cubic-bezier(.34,1.56,.64,1),opacity .3s ease;
            opacity:0;pointer-events:none;white-space:nowrap;">
    <span style="font-size:1.1rem;">✅</span>
    Complaint filed successfully!
    <button onclick="dismissSnackbar()"
            style="background:rgba(255,255,255,.15);border:none;color:#fff;border-radius:9999px;
                   width:20px;height:20px;font-size:.75rem;cursor:pointer;display:flex;
                   align-items:center;justify-content:center;margin-left:.25rem;">✕</button>
</div>

<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:2rem;flex-wrap:wrap;gap:1rem;">
    <div>
        <h1 style="font-size:1.5rem;font-weight:700;color:#111827;margin:0;">My Complaints</h1>
        <p style="font-size:0.875rem;color:#6b7280;margin:0.25rem 0 0;">Track all your submitted road issues</p>
    </div>
    <a href="{{ route('citizen.complaints.create') }}" class="rw-btn-primary">🚨 Report New Issue</a>
</div>

{{-- Stats strip --}}
<div id="stats-strip" style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2rem;">
    @foreach([['Total','#4f46e5','#eef2ff','📋'],['Pending','#d97706','#fffbeb','⏳'],['In Progress','#0284c7','#eff6ff','🔧'],['Resolved','#16a34a','#f0fdf4','✅']] as [$label,$color,$bg,$icon])
    <div style="background:{{ $bg }};border:1px solid {{ $color }}22;border-radius:1rem;padding:1.125rem 1rem;">
        <div style="font-size:1.5rem;">{{ $icon }}</div>
        <div id="stat-{{ strtolower(str_replace(' ','-',$label)) }}" style="font-size:1.75rem;font-weight:800;color:{{ $color }};line-height:1.1;">—</div>
        <div style="font-size:0.75rem;font-weight:600;color:{{ $color }};opacity:0.8;margin-top:0.125rem;">{{ $label }}</div>
    </div>
    @endforeach
</div>

{{-- List --}}
<div id="complaints-container">
    @for($i=0;$i<3;$i++)
    <div style="height:80px;border-radius:1rem;background:#f3f4f6;margin-bottom:1rem;animation:pulse 1.5s infinite;"></div>
    @endfor
</div>
<div id="pagination-wrap" style="margin-top:1.5rem;display:flex;justify-content:center;gap:0.5rem;"></div>

{{-- ── MODAL ── --}}
<div id="complaint-modal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,0.45);backdrop-filter:blur(4px);overflow-y:auto;padding:2rem 1rem;" onclick="closeModal(event)">
    <div id="modal-box" style="background:#fff;border-radius:1.25rem;max-width:780px;margin:0 auto;box-shadow:0 24px 64px rgba(0,0,0,0.18);position:relative;overflow:hidden;">
        {{-- Close btn --}}
        <button onclick="closeModal(null)" style="position:absolute;top:1rem;right:1rem;background:#f3f4f6;border:none;border-radius:50%;width:36px;height:36px;font-size:1.25rem;cursor:pointer;z-index:10;display:flex;align-items:center;justify-content:center;color:#6b7280;">×</button>

        {{-- Loading --}}
        <div id="modal-loading" style="padding:3rem;text-align:center;">
            <div style="width:40px;height:40px;border:3px solid #e5e7eb;border-top-color:#4f46e5;border-radius:50%;animation:spin 0.7s linear infinite;margin:0 auto 1rem;"></div>
            <p style="color:#9ca3af;font-size:0.875rem;margin:0;">Loading complaint…</p>
        </div>

        {{-- Error --}}
        <div id="modal-error" style="display:none;padding:3rem;text-align:center;">
            <div style="font-size:2.5rem;margin-bottom:0.75rem;">⚠️</div>
            <p id="modal-error-msg" style="color:#dc2626;font-size:0.875rem;margin:0 0 1rem;">Failed to load.</p>
            <button onclick="retryLoad()" style="background:#4f46e5;color:#fff;border:none;border-radius:0.625rem;padding:0.5rem 1.25rem;font-size:0.875rem;cursor:pointer;">Retry</button>
        </div>

        {{-- Content --}}
        <div id="modal-content" style="display:none;"></div>
    </div>
</div>

<style>
.rw-btn-primary{display:inline-flex;align-items:center;gap:.375rem;background:#4f46e5;color:#fff;text-decoration:none;border-radius:.75rem;padding:.625rem 1.25rem;font-size:.875rem;font-weight:600;box-shadow:0 2px 8px rgba(79,70,229,.25);transition:background .15s,transform .1s;}
.rw-btn-primary:hover{background:#4338ca;transform:translateY(-1px);}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
@keyframes spin{to{transform:rotate(360deg)}}
@keyframes modalIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.complaint-card{display:flex;align-items:stretch;background:#fff;border:1px solid #e5e7eb;border-radius:1rem;overflow:hidden;text-decoration:none;color:inherit;margin-bottom:1rem;box-shadow:0 1px 3px rgba(0,0,0,.06);transition:border-color .15s,box-shadow .15s;cursor:pointer;}
.complaint-card:hover{border-color:#a5b4fc;box-shadow:0 4px 12px rgba(79,70,229,.12);}
.page-btn{padding:.375rem .75rem;border:1px solid #e5e7eb;border-radius:.5rem;background:#fff;cursor:pointer;font-size:.875rem;color:#374151;}
.page-btn:hover{background:#eef2ff;border-color:#a5b4fc;color:#4f46e5;}
.page-btn.active{background:#4f46e5;color:#fff;border-color:#4f46e5;}
</style>

<script>
const STATUS_CONFIG = {
    pending:      {label:'⏳ Pending',      color:'#92400e',bg:'#fffbeb',border:'#fcd34d'},
    under_review: {label:'🔍 Under Review', color:'#1e40af',bg:'#eff6ff',border:'#93c5fd'},
    in_progress:  {label:'🔧 In Progress',  color:'#1d4ed8',bg:'#dbeafe',border:'#60a5fa'},
    resolved:     {label:'✅ Resolved',     color:'#14532d',bg:'#f0fdf4',border:'#86efac'},
    rejected:     {label:'❌ Rejected',     color:'#7f1d1d',bg:'#fef2f2',border:'#fca5a5'},
};
const SEV_CONFIG = {
    low:      {label:'🟢 Low',      color:'#065f46',bg:'#d1fae5'},
    medium:   {label:'🟡 Medium',   color:'#713f12',bg:'#fef9c3'},
    high:     {label:'🟠 High',     color:'#7c2d12',bg:'#ffedd5'},
    emergency:{label:'🔴 Emergency',color:'#7f1d1d',bg:'#fee2e2'},
};
const PIPELINE = [
    {key:'pending',label:'Submitted',icon:'📋'},
    {key:'under_review',label:'Under Review',icon:'🔍'},
    {key:'in_progress',label:'In Progress',icon:'🔧'},
    {key:'resolved',label:'Resolved',icon:'✅'},
];

let currentPage = 1;
let currentComplaintId = null;

// ── Load list ──────────────────────────────────────────────────────────────
async function loadComplaints(page=1){
    currentPage = page;
    const box = document.getElementById('complaints-container');
    try {
        const res  = await axios.get(`/api/v1/citizen/complaints?page=${page}&per_page=10`);
        const data = res.data;
        updateStats(data.meta?.total||0, data.data||[]);
        box.innerHTML = data.data?.length ? data.data.map(renderCard).join('') : renderEmpty();
        renderPagination(data.meta);
    } catch(e) {
        box.innerHTML = `<div style="text-align:center;color:#dc2626;padding:2rem;">Failed to load. <button onclick="loadComplaints()" style="color:#4f46e5;background:none;border:none;cursor:pointer;">Retry</button></div>`;
    }
}

function updateStats(total, list){
    document.getElementById('stat-total').textContent    = total;
    document.getElementById('stat-pending').textContent  = list.filter(c=>c.status==='pending').length;
    document.getElementById('stat-in-progress').textContent = list.filter(c=>['under_review','in_progress'].includes(c.status)).length;
    document.getElementById('stat-resolved').textContent = list.filter(c=>c.status==='resolved').length;
}

function renderCard(c){
    const s   = STATUS_CONFIG[c.status]   || {label:c.status,color:'#374151',bg:'#f3f4f6',border:'#d1d5db'};
    const sev = SEV_CONFIG[c.severity]    || {label:c.severity,color:'#374151',bg:'#f3f4f6'};
    const date= new Date(c.created_at).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'});
    return `
    <div class="complaint-card" onclick="openModal(${c.id})">
        <div style="width:5px;background:${s.border};flex-shrink:0;"></div>
        <div style="display:flex;align-items:center;justify-content:center;width:64px;padding:1rem;flex-shrink:0;font-size:2rem;">${c.category?.icon??'📌'}</div>
        <div style="flex:1;padding:1rem 1rem 1rem 0;min-width:0;">
            <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:.375rem;">
                <span style="font-size:.7rem;font-family:monospace;background:#f3f4f6;color:#6b7280;padding:.125rem .5rem;border-radius:9999px;">${c.complaint_number}</span>
                <span style="font-size:.7rem;font-weight:600;padding:.2rem .625rem;border-radius:9999px;background:${s.bg};color:${s.color};border:1px solid ${s.border};">${s.label}</span>
                <span style="font-size:.7rem;font-weight:600;padding:.2rem .5rem;border-radius:9999px;background:${sev.bg};color:${sev.color};">${sev.label}</span>
            </div>
            <h3 style="font-size:.9375rem;font-weight:700;color:#111827;margin:0 0 .25rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(c.title)}</h3>
            <p style="font-size:.8125rem;color:#6b7280;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">📍 ${esc(c.location)}</p>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;justify-content:center;padding:1rem;flex-shrink:0;gap:.375rem;">
            <span style="font-size:.75rem;color:#9ca3af;">${date}</span>
            <span style="font-size:.75rem;color:#6b7280;font-weight:500;">${c.category?.name??''}</span>
            <span style="font-size:.75rem;color:#4f46e5;font-weight:600;">View details →</span>
        </div>
    </div>`;
}

function renderEmpty(){
    return `<div style="background:#fff;border:1px solid #e5e7eb;border-radius:1.25rem;padding:5rem 2rem;text-align:center;">
        <div style="font-size:4rem;margin-bottom:1rem;">🛣️</div>
        <h3 style="font-size:1.125rem;font-weight:700;color:#111827;margin:0 0 .5rem;">No complaints yet</h3>
        <p style="font-size:.875rem;color:#6b7280;margin:0 0 1.5rem;">Help improve your city — report a road issue.</p>
        <a href="/citizen/complaints/create" class="rw-btn-primary">Report your first issue</a>
    </div>`;
}

function renderPagination(meta){
    const wrap = document.getElementById('pagination-wrap');
    if(!meta||meta.last_page<=1){wrap.innerHTML='';return;}
    let h='';
    if(meta.current_page>1) h+=`<button class="page-btn" onclick="loadComplaints(${meta.current_page-1})">← Prev</button>`;
    for(let p=Math.max(1,meta.current_page-2);p<=Math.min(meta.last_page,meta.current_page+2);p++)
        h+=`<button class="page-btn ${p===meta.current_page?'active':''}" onclick="loadComplaints(${p})">${p}</button>`;
    if(meta.current_page<meta.last_page) h+=`<button class="page-btn" onclick="loadComplaints(${meta.current_page+1})">Next →</button>`;
    wrap.innerHTML=h;
}

// ── Modal ──────────────────────────────────────────────────────────────────
function openModal(id){
    currentComplaintId = id;
    document.getElementById('complaint-modal').style.display = 'block';
    document.getElementById('modal-box').style.animation = 'modalIn 0.2s ease';
    document.body.style.overflow = 'hidden';
    showModalLoading();
    fetchComplaint(id);
}

function closeModal(e){
    if(e && e.target !== document.getElementById('complaint-modal')) return;
    document.getElementById('complaint-modal').style.display = 'none';
    document.body.style.overflow = '';
}

function showModalLoading(){
    document.getElementById('modal-loading').style.display = 'block';
    document.getElementById('modal-error').style.display   = 'none';
    document.getElementById('modal-content').style.display = 'none';
}

function retryLoad(){ fetchComplaint(currentComplaintId); }

async function fetchComplaint(id){
    showModalLoading();
    try {
        const res = await axios.get(`/api/v1/citizen/complaints/${id}`);
        renderModal(res.data.data);
    } catch(err){
        document.getElementById('modal-loading').style.display = 'none';
        document.getElementById('modal-error').style.display   = 'block';
        document.getElementById('modal-error-msg').textContent =
            err.response?.status===403 ? 'You do not have access to this complaint.' :
            err.response?.status===404 ? 'Complaint not found.' : 'Failed to load. Please try again.';
    }
}

function renderModal(c){
    const s   = STATUS_CONFIG[c.status]  || {label:c.status,color:'#374151',bg:'#f3f4f6',border:'#d1d5db'};
    const sev = SEV_CONFIG[c.severity]   || {label:c.severity,color:'#374151',bg:'#f3f4f6'};
    const currentStep = PIPELINE.findIndex(p=>p.key===c.status);
    const isRejected  = c.status==='rejected';

    // Pipeline HTML
    let pipelineHtml = isRejected
        ? `<div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:.75rem;padding:1rem;">
               <h3 style="color:#7f1d1d;margin:0 0 .25rem;font-size:.9rem;font-weight:700;">❌ Complaint Rejected</h3>
               <p style="color:#991b1b;margin:0;font-size:.8rem;">Please contact support if you have questions.</p>
           </div>`
        : `<div style="display:flex;align-items:center;">${PIPELINE.map((step,idx)=>{
            const done = currentStep>=idx;
            const circle = done
                ? `<div style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;background:#4f46e5;box-shadow:0 0 0 3px #c7d2fe;">${step.icon}</div>`
                : `<div style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;background:#f3f4f6;border:2px solid #e5e7eb;">○</div>`;
            const connector = idx<PIPELINE.length-1
                ? `<div style="flex:1;height:3px;background:${currentStep>idx?'#4f46e5':'#e5e7eb'};border-radius:9999px;margin-bottom:1.25rem;"></div>` : '';
            return `<div style="display:flex;flex-direction:column;align-items:center;flex:1;">${circle}<p style="font-size:.65rem;font-weight:${done?700:500};color:${done?'#4f46e5':'#9ca3af'};margin:.375rem 0 0;text-align:center;">${step.label}</p></div>${connector}`;
          }).join('')}</div>`;

    // Media HTML
    let mediaHtml = '';
    if(c.media?.length){
        mediaHtml = `<div style="margin-top:1.25rem;border-top:1px solid #f3f4f6;padding-top:1.25rem;">
            <h3 style="font-size:.875rem;font-weight:700;color:#111827;margin:0 0 .75rem;">📷 Attached Media</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:.625rem;">
            ${c.media.map(m=>m.file_type==='image'
                ? `<a href="${m.cloud_url}" target="_blank"><img src="${m.cloud_url}" alt="${esc(m.original_name)}" style="width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:.5rem;border:1px solid #e5e7eb;"></a>`
                : `<a href="${m.cloud_url}" target="_blank" style="display:flex;aspect-ratio:4/3;background:#1f2937;border-radius:.5rem;align-items:center;justify-content:center;flex-direction:column;gap:.25rem;text-decoration:none;"><span style="font-size:1.5rem;">🎥</span><span style="font-size:.7rem;color:#60a5fa;">Watch</span></a>`
            ).join('')}
            </div></div>`;
    }

    // Details rows
    const engineerName = c.assigned_engineer?.name ?? null;
    const rows = [
        ['Complaint #', c.complaint_number],
        ['Category',    c.category?.name??'—'],
        ['Severity',    sev.label],
        ['Status',      s.label],
        ['Location',    c.location],
        ['👷 Engineer',  engineerName ?? '—'],
        ['Submitted',   new Date(c.created_at).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'})],
        ['Views',       c.views_count+' views'],
        ['Votes',       c.votes_count+' upvotes'],
        ...(c.resolved_at?[['Resolved', new Date(c.resolved_at).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'})]]:[] ),
    ];

    // Engineer badge for the header (shown when assigned)
    const engineerBadge = engineerName
        ? `<span style="font-size:.65rem;font-weight:600;padding:.2rem .5rem;border-radius:9999px;background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;">👷 ${esc(engineerName)}</span>`
        : '';


    const html = `
    <div style="padding:1.5rem;">
        {{-- Header --}}
        <div style="display:flex;align-items:flex-start;gap:1rem;margin-bottom:1.25rem;">
            <div style="font-size:2.75rem;line-height:1;flex-shrink:0;">${c.category?.icon??'📌'}</div>
            <div style="flex:1;min-width:0;">
                <div style="display:flex;flex-wrap:wrap;gap:.375rem;margin-bottom:.5rem;">
                    <span style="font-size:.65rem;font-family:monospace;background:#f3f4f6;color:#6b7280;padding:.15rem .5rem;border-radius:9999px;">${c.complaint_number}</span>
                    <span style="font-size:.65rem;font-weight:700;padding:.2rem .625rem;border-radius:9999px;background:${s.bg};color:${s.color};border:1px solid ${s.border};">${s.label}</span>
                    <span style="font-size:.65rem;font-weight:700;padding:.2rem .5rem;border-radius:9999px;background:${sev.bg};color:${sev.color};">${sev.label}</span>
                    ${engineerBadge}
                    ${c.is_anonymous?'<span style="font-size:.65rem;font-weight:600;padding:.2rem .5rem;border-radius:9999px;background:#f3f4f6;color:#6b7280;">👤 Anonymous</span>':''}
                </div>
                <h2 style="font-size:1.125rem;font-weight:700;color:#111827;margin:0 0 .25rem;">${esc(c.title)}</h2>
                <p style="font-size:.8rem;color:#6b7280;margin:0;">📍 ${esc(c.location)} &nbsp;·&nbsp; ${c.category?.name??''}</p>
            </div>
        </div>

        {{-- Description --}}
        <div style="background:#f9fafb;border-radius:.75rem;padding:1rem;font-size:.875rem;color:#374151;line-height:1.7;margin-bottom:1.25rem;">${esc(c.description)}</div>

        {{-- Two-column grid --}}
        <div style="display:grid;grid-template-columns:1fr 220px;gap:1.25rem;">
            <div>
                {{-- Progress --}}
                <div style="background:#fff;border:1px solid #e5e7eb;border-radius:.875rem;padding:1rem;margin-bottom:1rem;">
                    <h3 style="font-size:.875rem;font-weight:700;color:#111827;margin:0 0 .875rem;">📊 Progress</h3>
                    ${pipelineHtml}
                </div>
                ${mediaHtml?`<div style="background:#fff;border:1px solid #e5e7eb;border-radius:.875rem;padding:1rem;">${mediaHtml}</div>`:''}
            </div>
            <div>
                {{-- Details --}}
                <div style="background:#fff;border:1px solid #e5e7eb;border-radius:.875rem;padding:1rem;">
                    <h3 style="font-size:.875rem;font-weight:700;color:#111827;margin:0 0 .75rem;">ℹ️ Details</h3>
                    ${rows.map(([k,v],i)=>`
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;padding:.4rem 0;${i<rows.length-1?'border-bottom:1px solid #f9fafb;':''}font-size:.75rem;">
                        <span style="color:#9ca3af;font-weight:500;flex-shrink:0;">${k}</span>
                        <span style="color:#111827;font-weight:600;text-align:right;margin-left:.5rem;word-break:break-word;">${v}</span>
                    </div>`).join('')}
                </div>
            </div>
        </div>

        {{-- Status Timeline --}}
        <div style="margin-top:1.25rem;background:#fff;border:1px solid #e5e7eb;border-radius:.875rem;padding:1rem;">
            <h3 style="font-size:.875rem;font-weight:700;color:#111827;margin:0 0 1rem;">🕐 Status History</h3>
            ${(()=>{
                const histories = c.status_histories ?? [];
                if(!histories.length) return `
                    <div style="display:flex;align-items:center;gap:.75rem;color:#9ca3af;padding:.25rem 0;">
                        <span style="font-size:1.25rem;">📭</span>
                        <span style="font-size:.875rem;">No history yet.</span>
                    </div>`;
                return `<div style="position:relative;">
                    <div style="position:absolute;left:11px;top:12px;bottom:12px;width:2px;background:linear-gradient(to bottom,#e5e7eb 0%,transparent 100%);"></div>
                    ${histories.map((h,i)=>{
                        const ns = STATUS_CONFIG[h.new_status] || {label:h.new_status,color:'#374151',bg:'#f3f4f6',border:'#d1d5db'};
                        const label = h.old_status
                            ? h.old_status.replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase()) + ' → ' + h.new_status.replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase())
                            : '📋 Complaint Filed';
                        const dateStr = new Date(h.created_at).toLocaleString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
                        return `<div style="display:flex;gap:.875rem;align-items:flex-start;padding-bottom:${i<histories.length-1?'1rem':'0'};position:relative;">
                            <div style="width:24px;height:24px;border-radius:50%;background:${ns.bg};color:${ns.color};
                                        display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;
                                        flex-shrink:0;border:2px solid ${ns.color}33;z-index:1;">
                                ${(ns.label??'')[0]??'•'}
                            </div>
                            <div style="flex:1;min-width:0;padding-top:.125rem;">
                                <span style="background:${ns.bg};color:${ns.color};font-size:.7rem;font-weight:700;
                                             padding:.2rem .625rem;border-radius:9999px;display:inline-block;margin-bottom:.25rem;">${label}</span>
                                ${h.remarks?`<p style="font-size:.75rem;color:#6b7280;margin:.2rem 0;font-style:italic;">"${esc(h.remarks)}"</p>`:''}
                                <p style="font-size:.7rem;color:#9ca3af;margin:0;">
                                    🕐 ${dateStr}${h.changed_by?' · <strong style="color:#6b7280;">'+esc(h.changed_by.name)+'</strong>':''}
                                </p>
                            </div>
                        </div>`;
                    }).join('')}
                </div>`;
            })()}
        </div>
    </div>`;

    const content = document.getElementById('modal-content');
    content.innerHTML = html;
    document.getElementById('modal-loading').style.display = 'none';
    content.style.display = 'block';
}

// ── Helpers ────────────────────────────────────────────────────────────────
function esc(str){
    return String(str??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Close modal on Escape key
document.addEventListener('keydown',e=>{ if(e.key==='Escape') closeModal(null); });

document.addEventListener('DOMContentLoaded',()=>{
    loadComplaints(1);

    // ── Show snackbar if redirected after filing ──
    if (new URLSearchParams(window.location.search).get('filed') === '1') {
        showSnackbar();
        // Clean URL without reloading
        history.replaceState({}, '', '/citizen/complaints');
    }
});

function showSnackbar() {
    const sb = document.getElementById('snackbar');
    sb.style.display = 'flex';
    // Trigger animation next frame
    requestAnimationFrame(() => {
        sb.style.opacity = '1';
        sb.style.transform = 'translateX(-50%) translateY(0)';
        sb.style.pointerEvents = 'auto';
    });
    // Auto-dismiss after 4 seconds
    setTimeout(dismissSnackbar, 4000);
}

function dismissSnackbar() {
    const sb = document.getElementById('snackbar');
    sb.style.opacity = '0';
    sb.style.transform = 'translateX(-50%) translateY(20px)';
    sb.style.pointerEvents = 'none';
    setTimeout(() => sb.style.display = 'none', 350);
}

</script>

@endsection

@extends('layouts.engineer')
@section('title', 'My Assignments')
@section('page-title', 'My Assignments')

@section('content')
<style>
/* ════════════════════════════════════════
   MY ASSIGNMENTS PAGE
════════════════════════════════════════ */
@keyframes fadeInUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
@keyframes livePulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(.85)}}

/* Welcome banner */
.asgn-welcome{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.75rem;flex-wrap:wrap;gap:1rem;}
.asgn-welcome-title{font-size:1.5rem;font-weight:800;color:#0a1628;letter-spacing:-.03em;line-height:1.2;}
.asgn-welcome-sub{font-size:.875rem;color:#6b7280;margin-top:.25rem;}

/* Stats grid */
.asgn-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;margin-bottom:1.75rem;}

/* Stat card */
.asgn-stat{
  border-radius:1.25rem;padding:1.375rem;
  position:relative;overflow:hidden;
  transition:transform .25s cubic-bezier(.4,0,.2,1),box-shadow .25s;
  animation:fadeInUp .4s ease both;
}
.asgn-stat:hover{transform:translateY(-3px);}
.asgn-stat::after{
  content:'';position:absolute;bottom:-18px;right:-18px;
  width:70px;height:70px;border-radius:50%;
  background:rgba(255,255,255,.1);transition:transform .3s;
}
.asgn-stat:hover::after{transform:scale(1.4);}
.asgn-stat-icon{
  width:40px;height:40px;border-radius:.875rem;
  background:rgba(255,255,255,.2);
  display:flex;align-items:center;justify-content:center;
  margin-bottom:.875rem;
}
.asgn-stat-val{font-size:2rem;font-weight:900;color:#fff;line-height:1;letter-spacing:-.03em;}
.asgn-stat-label{font-size:.75rem;font-weight:600;color:rgba(255,255,255,.75);margin-top:.375rem;text-transform:uppercase;letter-spacing:.06em;}
.asgn-stat-live{
  position:absolute;top:.875rem;right:.875rem;
  font-size:.625rem;font-weight:700;
  background:rgba(255,255,255,.2);color:#fff;
  padding:.175rem .5rem;border-radius:9999px;
  display:flex;align-items:center;gap:.3rem;letter-spacing:.05em;
}
.asgn-stat-live::before{content:'';width:5px;height:5px;border-radius:50%;background:#fff;animation:livePulse 1.5s infinite;}

/* Filter bar */
.filter-bar{
  background:#fff;border:1px solid rgba(228,232,246,.8);border-radius:1.25rem;
  padding:1rem 1.375rem;margin-bottom:1.5rem;
  display:flex;align-items:center;gap:.875rem;flex-wrap:wrap;
  box-shadow:0 2px 8px rgba(10,22,40,.04);
}
.filter-label{font-size:.75rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;white-space:nowrap;}
.filter-count-chip{
  margin-left:auto;font-size:.78rem;font-weight:700;
  color:#0ea5e9;background:#f0f9ff;
  padding:.3rem .75rem;border-radius:9999px;
  border:1px solid #bae6fd;white-space:nowrap;
}

/* Complaints grid */
.asgn-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(340px,1fr));
  gap:1.125rem;padding:1.5rem;
}

/* Complaint card */
.asgn-card{
  background:#fafbff;border:1.5px solid #e8edf8;border-radius:1.125rem;
  padding:1.25rem;cursor:pointer;
  transition:all .22s cubic-bezier(.4,0,.2,1);
  position:relative;overflow:hidden;
  animation:fadeInUp .35s ease both;
}
.asgn-card:hover{
  border-color:#7dd3fc;background:#fff;
  box-shadow:0 8px 28px rgba(14,165,233,.12);
  transform:translateY(-2px);
}
.asgn-card-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:.875rem;gap:.5rem;}
.asgn-card-icon{
  width:40px;height:40px;border-radius:.75rem;
  background:#f0f9ff;display:flex;align-items:center;justify-content:center;
  font-size:1.25rem;flex-shrink:0;border:1px solid #bae6fd;
}
.asgn-card-title{font-size:.9375rem;font-weight:700;color:#0a1628;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px;}
.asgn-card-num{font-size:.7rem;color:#9ca3af;margin-top:.125rem;font-family:monospace;}
.asgn-card-loc{font-size:.8rem;color:#6b7280;margin-bottom:.875rem;display:flex;align-items:center;gap:.25rem;}
.asgn-card-footer{display:flex;align-items:center;justify-content:space-between;}

/* Status pill */
.s-pill{
  display:inline-flex;align-items:center;gap:.3rem;
  font-size:.6875rem;font-weight:700;
  padding:.25rem .625rem;border-radius:9999px;
  letter-spacing:.02em;white-space:nowrap;flex-shrink:0;
}
.s-dot{width:5px;height:5px;border-radius:50%;flex-shrink:0;}

/* Update arrow chip */
.update-chip{
  font-size:.72rem;font-weight:700;color:#0ea5e9;
  background:#e0f2fe;padding:.2rem .625rem;border-radius:9999px;
  display:flex;align-items:center;gap:.25rem;
  transition:background .15s;
}
.asgn-card:hover .update-chip{background:#bae6fd;}

/* Severity pill */
.sev-pill{font-size:.6875rem;font-weight:700;padding:.25rem .5rem;border-radius:9999px;}

/* Priority ribbon */
.priority-ribbon{
  position:absolute;top:0;left:0;
  width:4px;height:100%;border-radius:.25rem 0 0 .25rem;
}

/* Loading spinner */
.eng-spinner{
  display:inline-block;width:30px;height:30px;
  border:3px solid #e0f2fe;border-top-color:#0ea5e9;
  border-radius:50%;animation:spin .7s linear infinite;
}

/* Pagination */
.pag-bar{
  padding:1rem 1.5rem;border-top:1px solid #f3f4f6;
  display:flex;align-items:center;justify-content:space-between;gap:1rem;
  flex-wrap:wrap;
}

/* Empty state */
.empty-state{padding:4rem 2rem;text-align:center;}
.empty-icon{
  width:64px;height:64px;border-radius:1.25rem;
  background:linear-gradient(135deg,#f0f9ff,#e0f2fe);
  display:flex;align-items:center;justify-content:center;
  font-size:1.75rem;margin:0 auto 1rem;
  border:1px solid #bae6fd;
}

/* Stagger */
.asgn-stat:nth-child(1){animation-delay:.05s}
.asgn-stat:nth-child(2){animation-delay:.1s}
.asgn-stat:nth-child(3){animation-delay:.15s}

@media(max-width:640px){
  .asgn-stats{grid-template-columns:repeat(2,1fr);}
  .asgn-stat:nth-child(3){grid-column:1/-1;}
  .asgn-grid{grid-template-columns:1fr;padding:1rem;}
  .asgn-welcome-title{font-size:1.25rem;}
}
</style>

{{-- Welcome banner --}}
<div class="asgn-welcome">
    <div>
        <div style="display:inline-flex;align-items:center;gap:.35rem;font-size:.625rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#0ea5e9;background:#e0f2fe;padding:.25rem .625rem;border-radius:9999px;border:1px solid #bae6fd;margin-bottom:.625rem;">
            <span style="width:5px;height:5px;border-radius:50%;background:#0ea5e9;animation:livePulse 1.5s infinite;"></span>
            Live Updates
        </div>
        <div class="asgn-welcome-title">Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }}, {{ explode(' ', auth()->user()->name)[0] }} 👷</div>
        <div class="asgn-welcome-sub">Your active field assignments — {{ now()->format('l, d M Y') }}</div>
    </div>
    <button onclick="applyFilters()" class="eng-btn eng-btn-primary" style="height:40px;">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Refresh
    </button>
</div>

{{-- Stats --}}
<div id="stats-row" class="asgn-stats">
    @foreach([1,2,3] as $i)
    <div class="skeleton" style="height:120px;animation-delay:{{ ($i-1)*0.08 }}s;"></div>
    @endforeach
</div>

{{-- Filter bar --}}
<div class="filter-bar">
    <span class="filter-label">Filter by</span>
    <select id="f-status" class="eng-input eng-select" onchange="applyFilters()" disabled style="max-width:220px;">
        <option>Loading…</option>
    </select>
    <div id="filter-count" class="filter-count-chip" style="display:none;"></div>
</div>

{{-- Complaints list --}}
<div class="eng-card">
    <div id="eng-loading" style="padding:3.5rem;text-align:center;">
        <div class="eng-spinner"></div>
        <p style="margin-top:1rem;color:#9ca3af;font-size:.875rem;font-weight:500;">Loading your assignments…</p>
    </div>
    <div id="eng-list" style="display:none;"></div>
    <div id="eng-empty" style="display:none;" class="empty-state">
        <div class="empty-icon">🎉</div>
        <div style="font-size:1rem;font-weight:700;color:#0a1628;margin-bottom:.375rem;">All caught up!</div>
        <div style="font-size:.875rem;color:#9ca3af;">No complaints assigned to you right now.</div>
    </div>
    <div id="pagination" style="display:none;" class="pag-bar">
        <span id="pag-info" style="font-size:.8125rem;color:#6b7280;font-weight:500;"></span>
        <div style="display:flex;gap:.5rem;">
            <button id="pag-prev" class="eng-btn eng-btn-outline eng-btn-sm" onclick="changePage(-1)">
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                Prev
            </button>
            <button id="pag-next" class="eng-btn eng-btn-outline eng-btn-sm" onclick="changePage(1)">
                Next
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>
</div>

<script>
const STATUS_CFG = {
    pending:               {label:'Pending',               color:'#d97706', bg:'#fffbeb', dot:'#f59e0b', icon:'⏳'},
    under_review:          {label:'Under Review',          color:'#2563eb', bg:'#eff6ff', dot:'#3b82f6', icon:'🔍'},
    in_progress:           {label:'In Progress',           color:'#0369a1', bg:'#e0f2fe', dot:'#0ea5e9', icon:'🔧'},
    awaiting_verification: {label:'Awaiting Verification', color:'#c2410c', bg:'#fff7ed', dot:'#f97316', icon:'🕐'},
    rejected:              {label:'Rejected',              color:'#dc2626', bg:'#fef2f2', dot:'#ef4444', icon:'❌'},
};
const SEV_CFG = {
    low:       {bg:'#ecfdf5',color:'#059669',bar:'#10b981'},
    medium:    {bg:'#fffbeb',color:'#d97706',bar:'#f59e0b'},
    high:      {bg:'#fff7ed',color:'#c2410c',bar:'#f97316'},
    emergency: {bg:'#fef2f2',color:'#dc2626',bar:'#ef4444'},
};
const STAT_META = [
    {key:'total',    label:'Total Assigned',        icon:'clipboard', gradient:'linear-gradient(135deg,#0ea5e9,#06b6d4)', shadow:'rgba(14,165,233,.35)'},
    {key:'progress', label:'In Progress',           icon:'wrench',    gradient:'linear-gradient(135deg,#8b5cf6,#7c3aed)', shadow:'rgba(139,92,246,.35)'},
    {key:'awaiting', label:'Awaiting Verification', icon:'clock',     gradient:'linear-gradient(135deg,#f97316,#ea580c)', shadow:'rgba(249,115,22,.35)'},
];
const ICONS = {
    clipboard:`<svg width="18" height="18" fill="none" stroke="white" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>`,
    wrench:`<svg width="18" height="18" fill="none" stroke="white" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>`,
    clock:`<svg width="18" height="18" fill="none" stroke="white" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
};

let currentPage = 1, totalPages = 1, allItems = [];

function esc(s){return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
function fmt(iso){return new Date(iso).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'});}
function timeAgo(iso){
    const d=Math.floor((Date.now()-new Date(iso))/1000);
    if(d<60)   return 'just now';
    if(d<3600) return Math.floor(d/60)+'m ago';
    if(d<86400)return Math.floor(d/3600)+'h ago';
    return Math.floor(d/86400)+'d ago';
}

async function loadFilters(){
    try {
        const res  = await fetch('/api/v1/complaint-options');
        const data = await res.json();
        const sel  = document.getElementById('f-status');
        const opts = (data.statuses??[]).filter(s=>s.value!=='verified');
        sel.innerHTML = '<option value="">All Statuses</option>' +
            opts.map(s=>`<option value="${s.value}">${s.label}</option>`).join('');
        sel.disabled = false;
    } catch(_) {
        document.getElementById('f-status').innerHTML = '<option value="">All Statuses</option>';
        document.getElementById('f-status').disabled  = false;
    }
}

function applyFilters(){ currentPage=1; loadComplaints(); }
function changePage(dir){ const n=currentPage+dir; if(n>=1&&n<=totalPages){currentPage=n;loadComplaints();} }

async function loadComplaints(){
    document.getElementById('eng-loading').style.display = 'block';
    document.getElementById('eng-list').style.display    = 'none';
    document.getElementById('eng-empty').style.display   = 'none';
    document.getElementById('pagination').style.display  = 'none';

    const params = {page:currentPage, per_page:12};
    const sv = document.getElementById('f-status').value;
    if(sv) params.status = sv;

    try {
        const res   = await axios.get('/api/v1/engineer/complaints', {params});
        const items = res.data.data ?? [];
        const meta  = res.data.meta ?? {};
        totalPages  = meta.last_page ?? 1;
        allItems    = items;

        const total    = meta.total ?? items.length;
        const inprog   = items.filter(c=>c.status==='in_progress').length;
        const awaiting = items.filter(c=>c.status==='awaiting_verification').length;

        // Filter count chip
        const fc = document.getElementById('filter-count');
        fc.textContent = `${total} assignment${total!==1?'s':''}`;
        fc.style.display = '';

        document.getElementById('eng-loading').style.display = 'none';

        // ── Stats ──
        const vals = {total, progress:inprog, awaiting};
        document.getElementById('stats-row').innerHTML = STAT_META.map(s=>`
            <div class="asgn-stat" style="background:${s.gradient};box-shadow:0 8px 28px ${s.shadow};">
                <div class="asgn-stat-live">LIVE</div>
                <div class="asgn-stat-icon">${ICONS[s.icon]}</div>
                <div class="asgn-stat-val">${vals[s.key]}</div>
                <div class="asgn-stat-label">${s.label}</div>
            </div>`).join('');

        if(!items.length){
            document.getElementById('eng-empty').style.display='block';
            return;
        }

        // Pagination
        document.getElementById('pagination').style.display='flex';
        document.getElementById('pag-info').textContent=`Page ${currentPage} of ${totalPages} · ${total} total`;
        document.getElementById('pag-prev').disabled = currentPage<=1;
        document.getElementById('pag-next').disabled = currentPage>=totalPages;

        // Cards
        document.getElementById('eng-list').style.display='block';
        document.getElementById('eng-list').innerHTML=`<div class="asgn-grid">
            ${items.map((c,i)=>{
                const s   = STATUS_CFG[c.status]??{label:c.status,color:'#374151',bg:'#f3f4f6',dot:'#9ca3af',icon:'📌'};
                const sev = SEV_CFG[c.severity]??{bg:'#f3f4f6',color:'#374151',bar:'#9ca3af'};
                const isTerminal = c.status==='awaiting_verification'||c.status==='rejected';
                const catIcon = c.category?.icon ?? '📌';
                return `
                <div class="asgn-card" style="animation-delay:${i*0.04}s"
                     onclick="window.location='/engineer/complaints/${c.id}'">
                    <div class="priority-ribbon" style="background:${sev.bar};"></div>
                    <div class="asgn-card-top">
                        <div style="display:flex;align-items:center;gap:.625rem;flex:1;min-width:0;">
                            <div class="asgn-card-icon">${catIcon}</div>
                            <div style="min-width:0;">
                                <div class="asgn-card-title">${esc(c.title)}</div>
                                <div class="asgn-card-num">${esc(c.complaint_number)}</div>
                            </div>
                        </div>
                        <span class="s-pill" style="background:${s.bg};color:${s.color};">
                            <span class="s-dot" style="background:${s.dot};"></span>
                            ${s.label}
                        </span>
                    </div>
                    <div class="asgn-card-loc">
                        <svg width="12" height="12" fill="none" stroke="#9ca3af" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        ${esc(c.location)}
                    </div>
                    <div class="asgn-card-footer">
                        <span class="sev-pill" style="background:${sev.bg};color:${sev.color};">
                            ${(c.severity??'').charAt(0).toUpperCase()+(c.severity??'').slice(1)}
                        </span>
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <span style="font-size:.75rem;color:#9ca3af;">${timeAgo(c.created_at)}</span>
                            ${!isTerminal?`<span class="update-chip">Update <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg></span>`:''}
                        </div>
                    </div>
                </div>`;
            }).join('')}
        </div>`;

    } catch(e){
        document.getElementById('eng-loading').innerHTML=`
            <div style="padding:2rem;text-align:center;">
                <div style="color:#dc2626;font-size:.875rem;margin-bottom:.75rem;">Failed to load assignments.</div>
                <button onclick="loadComplaints()" class="eng-btn eng-btn-outline eng-btn-sm">Try again</button>
            </div>`;
    }
}

document.addEventListener('DOMContentLoaded', async()=>{
    await loadFilters();
    loadComplaints();
});
</script>
@endsection

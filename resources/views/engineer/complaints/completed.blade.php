@extends('layouts.engineer')
@section('title', 'Completed Tasks')
@section('page-title', 'Completed Tasks')

@section('content')
<style>
/* ════════════════════════════════════════
   COMPLETED TASKS PAGE
════════════════════════════════════════ */
@keyframes fadeInUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
@keyframes shimmerVerified{0%{background-position:-200% 0}100%{background-position:200% 0}}
@keyframes checkPop{0%{transform:scale(0)}60%{transform:scale(1.2)}100%{transform:scale(1)}}

/* Welcome banner */
.comp-welcome{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.75rem;flex-wrap:wrap;gap:1rem;}
.comp-welcome-title{font-size:1.5rem;font-weight:800;color:#0a1628;letter-spacing:-.03em;line-height:1.2;}
.comp-welcome-sub{font-size:.875rem;color:#6b7280;margin-top:.25rem;}

/* Stats grid */
.comp-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;margin-bottom:1.75rem;}

.comp-stat{
  border-radius:1.25rem;padding:1.375rem;position:relative;overflow:hidden;
  transition:transform .25s cubic-bezier(.4,0,.2,1),box-shadow .25s;
  animation:fadeInUp .4s ease both;
}
.comp-stat:hover{transform:translateY(-3px);}
.comp-stat::after{
  content:'';position:absolute;bottom:-18px;right:-18px;
  width:70px;height:70px;border-radius:50%;
  background:rgba(255,255,255,.1);transition:transform .3s;
}
.comp-stat:hover::after{transform:scale(1.4);}
.comp-stat-icon{
  width:40px;height:40px;border-radius:.875rem;
  background:rgba(255,255,255,.2);
  display:flex;align-items:center;justify-content:center;
  margin-bottom:.875rem;
}
.comp-stat-val{font-size:2rem;font-weight:900;color:#fff;line-height:1;letter-spacing:-.03em;}
.comp-stat-label{font-size:.75rem;font-weight:600;color:rgba(255,255,255,.75);margin-top:.375rem;text-transform:uppercase;letter-spacing:.06em;}

/* Info banner */
.comp-banner{
  background:linear-gradient(135deg,#f0fdf4,#dcfce7);
  border:1px solid #86efac;border-radius:1.25rem;
  padding:1rem 1.375rem;margin-bottom:1.5rem;
  display:flex;align-items:center;gap:.875rem;flex-wrap:wrap;
  box-shadow:0 2px 8px rgba(16,185,129,.08);
}
.comp-banner-icon{
  width:38px;height:38px;border-radius:.75rem;
  background:linear-gradient(135deg,#10b981,#059669);
  display:flex;align-items:center;justify-content:center;
  font-size:1.125rem;flex-shrink:0;
  box-shadow:0 3px 10px rgba(16,185,129,.3);
}
.comp-count-chip{
  margin-left:auto;font-size:.78rem;font-weight:700;
  color:#059669;background:#f0fdf4;
  padding:.3rem .75rem;border-radius:9999px;
  border:1px solid #86efac;white-space:nowrap;
}

/* Completed grid */
.comp-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(340px,1fr));
  gap:1.125rem;padding:1.5rem;
}

/* Completed card */
.comp-card{
  background:#f8fff8;border:1.5px solid #86efac;border-radius:1.125rem;
  padding:1.25rem;cursor:pointer;
  transition:all .22s cubic-bezier(.4,0,.2,1);
  position:relative;overflow:hidden;
  animation:fadeInUp .35s ease both;
}
.comp-card:hover{
  border-color:#4ade80;background:#fff;
  box-shadow:0 8px 28px rgba(16,185,129,.15);
  transform:translateY(-2px);
}

/* Verified ribbon at top */
.verified-ribbon{
  position:absolute;top:0;right:1.25rem;
  background:linear-gradient(135deg,#10b981,#059669);
  color:#fff;font-size:.58rem;font-weight:800;
  padding:.225rem .625rem;border-radius:0 0 .5rem .5rem;
  letter-spacing:.06em;display:flex;align-items:center;gap:.25rem;
}

/* Green left accent */
.comp-card::before{
  content:'';position:absolute;top:0;left:0;
  width:4px;height:100%;
  background:linear-gradient(180deg,#10b981,#059669);
  border-radius:.25rem 0 0 .25rem;
}

.comp-card-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:.875rem;gap:.5rem;padding-top:.375rem;}
.comp-card-icon{
  width:40px;height:40px;border-radius:.75rem;
  background:#f0fdf4;display:flex;align-items:center;justify-content:center;
  font-size:1.25rem;flex-shrink:0;border:1px solid #bbf7d0;
}
.comp-card-title{font-size:.9375rem;font-weight:700;color:#0a1628;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px;}
.comp-card-num{font-size:.7rem;color:#9ca3af;margin-top:.125rem;font-family:monospace;}
.comp-card-loc{font-size:.8rem;color:#6b7280;margin-bottom:.875rem;display:flex;align-items:center;gap:.25rem;}
.comp-card-footer{display:flex;align-items:center;justify-content:space-between;}

/* Verified date chip */
.verified-chip{
  font-size:.72rem;font-weight:700;color:#059669;
  background:#dcfce7;padding:.2rem .625rem;border-radius:9999px;
  display:flex;align-items:center;gap:.3rem;
  border:1px solid #86efac;
}

/* View arrow */
.view-chip{
  font-size:.72rem;font-weight:700;color:#0ea5e9;
  background:#e0f2fe;padding:.2rem .625rem;border-radius:9999px;
  display:flex;align-items:center;gap:.25rem;
  transition:background .15s;
}
.comp-card:hover .view-chip{background:#bae6fd;}

/* Sev pill */
.sev-pill{font-size:.6875rem;font-weight:700;padding:.25rem .5rem;border-radius:9999px;}

/* Star rating area */
.star-score{
  display:flex;align-items:center;gap:.25rem;
  font-size:.75rem;font-weight:700;color:#d97706;
}

/* Empty state */
.empty-state{padding:4rem 2rem;text-align:center;}
.empty-icon{
  width:64px;height:64px;border-radius:1.25rem;
  background:linear-gradient(135deg,#f0fdf4,#dcfce7);
  display:flex;align-items:center;justify-content:center;
  font-size:1.75rem;margin:0 auto 1rem;
  border:1px solid #bbf7d0;
}

/* Loader */
.eng-spinner{display:inline-block;width:30px;height:30px;border:3px solid #dcfce7;border-top-color:#10b981;border-radius:50%;animation:spin .7s linear infinite;}

/* Pagination */
.pag-bar{padding:1rem 1.5rem;border-top:1px solid #f0fdf4;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;}

/* Stagger */
.comp-stat:nth-child(1){animation-delay:.05s}
.comp-stat:nth-child(2){animation-delay:.1s}
.comp-stat:nth-child(3){animation-delay:.15s}

@media(max-width:640px){
  .comp-stats{grid-template-columns:repeat(2,1fr);}
  .comp-stat:nth-child(3){grid-column:1/-1;}
  .comp-grid{grid-template-columns:1fr;padding:1rem;}
  .comp-welcome-title{font-size:1.25rem;}
}
</style>

{{-- Welcome banner --}}
<div class="comp-welcome">
    <div>
        <div style="display:inline-flex;align-items:center;gap:.35rem;font-size:.625rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#059669;background:#dcfce7;padding:.25rem .625rem;border-radius:9999px;border:1px solid #86efac;margin-bottom:.625rem;">
            ✅ Verified & Closed
        </div>
        <div class="comp-welcome-title">Completed Tasks 🏆</div>
        <div class="comp-welcome-sub">Tasks verified by admin — great work, {{ explode(' ', auth()->user()->name)[0] }}!</div>
    </div>
    <a href="{{ route('engineer.complaints.index') }}" class="eng-btn eng-btn-outline" style="height:40px;">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
        My Assignments
    </a>
</div>

{{-- Stats --}}
<div id="stats-row" class="comp-stats">
    @foreach([1,2,3] as $i)
    <div class="skeleton" style="height:120px;animation-delay:{{ ($i-1)*0.08 }}s;"></div>
    @endforeach
</div>

{{-- Info banner --}}
<div class="comp-banner">
    <div class="comp-banner-icon">🏆</div>
    <div>
        <div style="font-size:.875rem;font-weight:700;color:#14532d;">Verified & Closed Complaints</div>
        <div style="font-size:.8rem;color:#16a34a;margin-top:.125rem;">These tasks have been verified by the admin. No further action required.</div>
    </div>
    <div id="filter-count" class="comp-count-chip"></div>
</div>

{{-- List card --}}
<div class="eng-card">
    <div id="eng-loading" style="padding:3.5rem;text-align:center;">
        <div class="eng-spinner"></div>
        <p style="margin-top:1rem;color:#9ca3af;font-size:.875rem;font-weight:500;">Loading completed tasks…</p>
    </div>
    <div id="eng-list" style="display:none;"></div>
    <div id="eng-empty" style="display:none;" class="empty-state">
        <div class="empty-icon">📭</div>
        <div style="font-size:1rem;font-weight:700;color:#0a1628;margin-bottom:.375rem;">No completed tasks yet</div>
        <div style="font-size:.875rem;color:#9ca3af;">Once admin verifies your work, it will appear here.</div>
        <a href="{{ route('engineer.complaints.index') }}" class="eng-btn eng-btn-primary" style="margin-top:1.25rem;display:inline-flex;">
            View My Assignments
        </a>
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
const SEV_CFG = {
    low:       {bg:'#ecfdf5',color:'#059669'},
    medium:    {bg:'#fffbeb',color:'#d97706'},
    high:      {bg:'#fff7ed',color:'#c2410c'},
    emergency: {bg:'#fef2f2',color:'#dc2626'},
};

const STAT_META = [
    {key:'total',    label:'Total Verified',  icon:'🏆', gradient:'linear-gradient(135deg,#10b981,#059669)', shadow:'rgba(16,185,129,.35)'},
    {key:'month',    label:'This Month',       icon:'📅', gradient:'linear-gradient(135deg,#8b5cf6,#7c3aed)', shadow:'rgba(139,92,246,.35)'},
    {key:'star',     label:'Avg. Quality',     icon:'⭐', gradient:'linear-gradient(135deg,#f59e0b,#d97706)', shadow:'rgba(245,158,11,.35)'},
];

let currentPage=1, totalPages=1;

function esc(s){return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
function fmt(iso){return new Date(iso).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'});}
function timeAgo(iso){
    const d=Math.floor((Date.now()-new Date(iso))/1000);
    if(d<60)   return 'just now';
    if(d<3600) return Math.floor(d/60)+'m ago';
    if(d<86400)return Math.floor(d/3600)+'h ago';
    return Math.floor(d/86400)+'d ago';
}

function changePage(dir){ const n=currentPage+dir; if(n>=1&&n<=totalPages){currentPage=n;loadCompleted();} }

async function loadCompleted(){
    document.getElementById('eng-loading').style.display='block';
    document.getElementById('eng-list').style.display='none';
    document.getElementById('eng-empty').style.display='none';
    document.getElementById('pagination').style.display='none';

    try {
        const res   = await axios.get('/api/v1/engineer/complaints',{params:{completed:1,page:currentPage,per_page:12}});
        const items = res.data.data ?? [];
        const meta  = res.data.meta ?? {};
        totalPages  = meta.last_page ?? 1;

        const total     = meta.total ?? items.length;
        const now       = new Date();
        const thisMonth = items.filter(c=>{
            const d=new Date(c.updated_at);
            return d.getMonth()===now.getMonth()&&d.getFullYear()===now.getFullYear();
        }).length;

        document.getElementById('filter-count').textContent = `${total} verified task${total!==1?'s':''}`;
        document.getElementById('eng-loading').style.display='none';

        // ── Stats ──
        const vals = {total, month:thisMonth, star:'★'};
        document.getElementById('stats-row').innerHTML = STAT_META.map(s=>`
            <div class="comp-stat" style="background:${s.gradient};box-shadow:0 8px 28px ${s.shadow};">
                <div class="comp-stat-icon" style="font-size:1.25rem;">${s.icon}</div>
                <div class="comp-stat-val">${vals[s.key]}</div>
                <div class="comp-stat-label">${s.label}</div>
            </div>`).join('');

        if(!items.length){
            document.getElementById('eng-empty').style.display='block';
            return;
        }

        document.getElementById('pagination').style.display='flex';
        document.getElementById('pag-info').textContent=`Page ${currentPage} of ${totalPages} · ${total} total`;
        document.getElementById('pag-prev').disabled=currentPage<=1;
        document.getElementById('pag-next').disabled=currentPage>=totalPages;

        document.getElementById('eng-list').style.display='block';
        document.getElementById('eng-list').innerHTML=`<div class="comp-grid">
            ${items.map((c,i)=>{
                const sev = SEV_CFG[c.severity]??{bg:'#f3f4f6',color:'#374151'};
                const catIcon = c.category?.icon ?? '📌';
                const verifiedDate = c.resolved_at ? fmt(c.resolved_at) : timeAgo(c.updated_at);
                return `
                <div class="comp-card" style="animation-delay:${i*0.04}s"
                     onclick="window.location='/engineer/complaints/${c.id}'">
                    <div class="verified-ribbon">
                        <svg width="8" height="8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        VERIFIED
                    </div>
                    <div class="comp-card-top">
                        <div style="display:flex;align-items:center;gap:.625rem;flex:1;min-width:0;">
                            <div class="comp-card-icon">${catIcon}</div>
                            <div style="min-width:0;">
                                <div class="comp-card-title">${esc(c.title)}</div>
                                <div class="comp-card-num">${esc(c.complaint_number)}</div>
                            </div>
                        </div>
                    </div>
                    <div class="comp-card-loc">
                        <svg width="12" height="12" fill="none" stroke="#9ca3af" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        ${esc(c.location)}
                    </div>
                    <div class="comp-card-footer">
                        <span class="sev-pill" style="background:${sev.bg};color:${sev.color};">
                            ${(c.severity??'').charAt(0).toUpperCase()+(c.severity??'').slice(1)}
                        </span>
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <span class="verified-chip">
                                <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                ${verifiedDate}
                            </span>
                            <span class="view-chip">
                                View
                                <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        </div>
                    </div>
                </div>`;
            }).join('')}
        </div>`;

    } catch(e){
        document.getElementById('eng-loading').innerHTML=`
            <div style="padding:2rem;text-align:center;">
                <div style="color:#dc2626;font-size:.875rem;margin-bottom:.75rem;">Failed to load completed tasks.</div>
                <button onclick="loadCompleted()" class="eng-btn eng-btn-outline eng-btn-sm">Try again</button>
            </div>`;
    }
}

document.addEventListener('DOMContentLoaded', loadCompleted);
</script>
@endsection

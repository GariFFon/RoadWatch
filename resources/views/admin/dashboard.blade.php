@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<style>
/* ════════════════════════════════════════
   DASHBOARD SPECIFIC STYLES
════════════════════════════════════════ */

/* Welcome banner */
.dash-welcome{
  display:flex;align-items:center;justify-content:space-between;
  margin-bottom:1.75rem;flex-wrap:wrap;gap:1rem;
}
.dash-welcome-title{font-size:1.5rem;font-weight:800;color:#0f0e1a;letter-spacing:-.03em;line-height:1.2;}
.dash-welcome-sub{font-size:.875rem;color:#6b7280;margin-top:.25rem;font-weight:400;}

/* Stats grid */
.dash-stats-grid{
  display:grid;grid-template-columns:repeat(4,1fr);
  gap:1.25rem;margin-bottom:1.75rem;
}

/* Stat card */
.dash-stat-card{
  border-radius:1.25rem;padding:1.5rem 1.375rem;
  position:relative;overflow:hidden;cursor:default;
  transition:transform .25s cubic-bezier(.4,0,.2,1),box-shadow .25s;
  animation:fadeInUp .4s ease both;
}
.dash-stat-card:hover{transform:translateY(-3px);}
.dash-stat-card::after{
  content:'';position:absolute;bottom:-20px;right:-20px;
  width:80px;height:80px;border-radius:50%;
  background:rgba(255,255,255,.12);
  transition:transform .3s;
}
.dash-stat-card:hover::after{transform:scale(1.3);}

.dash-stat-icon{
  width:42px;height:42px;border-radius:.875rem;
  background:rgba(255,255,255,.2);
  display:flex;align-items:center;justify-content:center;
  margin-bottom:1rem;backdrop-filter:blur(4px);
}
.dash-stat-val{font-size:2.25rem;font-weight:900;color:#fff;line-height:1;letter-spacing:-.03em;}
.dash-stat-label{font-size:.78125rem;font-weight:600;color:rgba(255,255,255,.75);margin-top:.375rem;text-transform:uppercase;letter-spacing:.06em;}
.dash-stat-live{
  position:absolute;top:.875rem;right:.875rem;
  font-size:.625rem;font-weight:700;
  background:rgba(255,255,255,.2);color:#fff;
  padding:.2rem .5rem;border-radius:9999px;
  display:flex;align-items:center;gap:.3rem;
  backdrop-filter:blur(4px);letter-spacing:.05em;
}
.dash-stat-live::before{
  content:'';width:5px;height:5px;border-radius:50%;background:#fff;
  animation:livePulse 1.5s infinite;
}
@keyframes livePulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(.85)}}

/* Main grid: table + sidebar */
.dash-main-grid{
  display:grid;grid-template-columns:1fr 300px;
  gap:1.25rem;align-items:start;
}

/* Table */
.dash-table-wrap{display:block;width:100%;overflow:hidden;}
.dash-table{width:100%;border-collapse:collapse;}
.dash-table thead th{
  padding:.75rem 1.125rem;text-align:left;
  font-size:.6875rem;font-weight:700;color:#9ca3af;
  text-transform:uppercase;letter-spacing:.07em;white-space:nowrap;
  border-bottom:1px solid #f3f4f6;
}
.dash-table tbody tr{
  border-bottom:1px solid #f9fafb;
  transition:background .15s;
}
.dash-table tbody tr:hover{background:#fafbff;}
.dash-table tbody tr:last-child{border-bottom:none;}
.dash-table tbody td{padding:.75rem 1.125rem;}

/* Mobile cards */
.dash-cards{display:none;flex-direction:column;gap:.75rem;padding:.75rem 1rem 1rem;}

@media(max-width:1024px){.dash-stats-grid{grid-template-columns:repeat(2,1fr);}}
@media(max-width:900px){.dash-main-grid{grid-template-columns:1fr;}}
@media(max-width:640px){
  .dash-stats-grid{grid-template-columns:repeat(2,1fr);gap:.875rem;margin-bottom:1.25rem;}
  .dash-stat-card{padding:1.125rem;}
  .dash-stat-val{font-size:1.75rem;}
  .dash-table-wrap{display:none;}
  .dash-cards{display:flex;}
  .dash-welcome-title{font-size:1.25rem;}
}

/* Badge / pill */
.d-pill{
  display:inline-flex;align-items:center;
  font-size:.6875rem;font-weight:700;
  padding:.25rem .625rem;border-radius:9999px;
  letter-spacing:.02em;
}

/* Complaint card (mobile) */
.dash-ccard{
  background:#fafbff;border:1px solid #ebe9f5;
  border-radius:1rem;padding:.9375rem 1rem;
  transition:box-shadow .2s;
}
.dash-ccard:hover{box-shadow:0 4px 16px rgba(15,14,26,.07);}
.dash-ccard-title{font-size:.875rem;font-weight:700;color:#111827;line-height:1.35;}
.dash-ccard-loc{font-size:.75rem;color:#9ca3af;margin-top:.15rem;}
.dash-ccard-meta{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-top:.625rem;}

/* Status breakdown item */
.status-item{
  display:flex;align-items:center;justify-content:space-between;
  padding:.75rem .875rem;border-radius:.875rem;
  border:1px solid transparent;
  transition:all .2s;
}
.status-item:hover{background:#fafbff;border-color:#ebe9f5;}

/* Mini donut / progress */
.status-bar-track{height:5px;background:#f3f4f6;border-radius:9999px;overflow:hidden;margin-top:.375rem;}
.status-bar-fill{height:100%;border-radius:9999px;transition:width .8s cubic-bezier(.4,0,.2,1);}

/* Complaint number */
.complaint-num{font-size:.75rem;color:#9ca3af;font-family:monospace;}

/* Section header */
.section-header{
  padding:1.25rem 1.375rem;border-bottom:1px solid #f3f4f6;
  display:flex;align-items:center;justify-content:space-between;
  gap:.75rem;
}
.section-title{font-size:.9375rem;font-weight:700;color:#0f0e1a;display:flex;align-items:center;gap:.5rem;}
.section-link{
  font-size:.8125rem;font-weight:600;color:#6366f1;
  text-decoration:none;display:flex;align-items:center;gap:.25rem;
  transition:color .15s;white-space:nowrap;
}
.section-link:hover{color:#4f46e5;}

/* Trend indicator */
.dash-trend{
  display:flex;align-items:center;gap:.25rem;margin-top:.5rem;
  font-size:.72rem;font-weight:600;color:rgba(255,255,255,.75);
}

/* Activity dot */
.activity-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}

/* Mini bar chart */
.mini-bars{display:flex;align-items:flex-end;gap:3px;height:32px;}
.mini-bar{width:6px;border-radius:3px 3px 0 0;background:rgba(255,255,255,.3);transition:height .6s ease;}

/* Stagger animation delays */
.dash-stat-card:nth-child(1){animation-delay:.05s}
.dash-stat-card:nth-child(2){animation-delay:.1s}
.dash-stat-card:nth-child(3){animation-delay:.15s}
.dash-stat-card:nth-child(4){animation-delay:.2s}
</style>

{{-- Welcome Banner --}}
<div class="dash-welcome">
    <div>
        <div class="dash-stat-live" style="display:inline-flex;background:linear-gradient(135deg,#6366f1,#8b5cf6);margin-bottom:.625rem;">
            Live Dashboard
        </div>
        <div class="dash-welcome-title">Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }}, {{ explode(' ', auth()->user()->name)[0] }} 👋</div>
        <div class="dash-welcome-sub">Here's what's happening with RoadWatch today, {{ now()->format('l, d M Y') }}</div>
    </div>
    <a href="{{ route('admin.complaints.index') }}" class="adm-btn adm-btn-primary" style="height:40px;">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        View All Complaints
    </a>
</div>

{{-- Stats row --}}
<div id="stats-row" class="dash-stats-grid">
    @foreach([1,2,3,4] as $i)
    <div class="skeleton" style="height:130px;animation-delay:{{ ($i-1)*0.08 }}s;"></div>
    @endforeach
</div>

{{-- Main grid --}}
<div class="dash-main-grid">

    {{-- Recent Complaints --}}
    <div class="adm-card" style="min-width:0;overflow:hidden;">
        <div class="section-header">
            <div class="section-title">
                <span style="width:30px;height:30px;border-radius:.625rem;background:#eef2ff;display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="#6366f1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </span>
                Recent Complaints
            </div>
            <a href="{{ route('admin.complaints.index') }}" class="section-link">
                View all
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        {{-- Desktop table --}}
        <div class="dash-table-wrap" id="recent-table">
            <div style="padding:3rem;text-align:center;">
                <div class="skeleton" style="height:14px;width:200px;margin:0 auto .75rem;"></div>
                <div class="skeleton" style="height:14px;width:160px;margin:0 auto;"></div>
            </div>
        </div>

        {{-- Mobile cards --}}
        <div class="dash-cards" id="recent-cards"></div>
    </div>

    {{-- Right column --}}
    <div style="display:flex;flex-direction:column;gap:1.25rem;">

        {{-- By Status --}}
        <div class="adm-card" style="padding:1.375rem;min-width:0;">
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1.25rem;">
                <span style="width:30px;height:30px;border-radius:.625rem;background:#faf5ff;display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="#8b5cf6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </span>
                <h2 style="font-size:.9375rem;font-weight:700;color:#0f0e1a;">By Status</h2>
            </div>
            <div id="status-breakdown" style="display:flex;flex-direction:column;gap:.375rem;">
                @foreach([1,2,3,4,5] as $i)
                <div class="skeleton" style="height:48px;animation-delay:{{ ($i-1)*0.06 }}s;"></div>
                @endforeach
            </div>
        </div>

        {{-- Quick Stats Card --}}
        <div class="adm-card" style="padding:1.375rem;min-width:0;" id="quick-stats-card">
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1.125rem;">
                <span style="width:30px;height:30px;border-radius:.625rem;background:#f0fdf4;display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="#22c55e" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </span>
                <h2 style="font-size:.9375rem;font-weight:700;color:#0f0e1a;">Resolution Rate</h2>
            </div>
            <div id="resolution-rate">
                <div class="skeleton" style="height:60px;"></div>
            </div>
        </div>
    </div>

</div>

<script>
const STATUS_CFG = {
    pending:      {label:'Pending',      color:'#d97706', bg:'#fffbeb', dot:'#f59e0b', icon:'⏳', gradient:'linear-gradient(135deg,#f59e0b,#d97706)'},
    under_review: {label:'Under Review', color:'#2563eb', bg:'#eff6ff', dot:'#3b82f6', icon:'🔍', gradient:'linear-gradient(135deg,#3b82f6,#2563eb)'},
    in_progress:  {label:'In Progress',  color:'#7c3aed', bg:'#f5f3ff', dot:'#8b5cf6', icon:'🔧', gradient:'linear-gradient(135deg,#8b5cf6,#7c3aed)'},
    resolved:     {label:'Resolved',     color:'#059669', bg:'#ecfdf5', dot:'#10b981', icon:'✅', gradient:'linear-gradient(135deg,#10b981,#059669)'},
    rejected:     {label:'Rejected',     color:'#dc2626', bg:'#fef2f2', dot:'#ef4444', icon:'❌', gradient:'linear-gradient(135deg,#f87171,#dc2626)'},
};
const SEV_CFG = {
    low:       {bg:'#ecfdf5',color:'#059669'},
    medium:    {bg:'#fffbeb',color:'#d97706'},
    high:      {bg:'#fff7ed',color:'#c2410c'},
    emergency: {bg:'#fef2f2',color:'#dc2626'},
};

const STAT_CARDS = [
    {
        key:'total', label:'Total Complaints', icon:'clipboard',
        gradient:'linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%)',
        shadow:'rgba(99,102,241,.35)',
    },
    {
        key:'pending', label:'Pending', icon:'clock',
        gradient:'linear-gradient(135deg,#f59e0b 0%,#d97706 100%)',
        shadow:'rgba(245,158,11,.35)',
    },
    {
        key:'in_progress', label:'In Progress', icon:'wrench',
        gradient:'linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%)',
        shadow:'rgba(139,92,246,.35)',
    },
    {
        key:'resolved', label:'Resolved', icon:'check',
        gradient:'linear-gradient(135deg,#10b981 0%,#059669 100%)',
        shadow:'rgba(16,185,129,.35)',
    },
];

const ICONS = {
    clipboard:`<svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>`,
    clock:`<svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
    wrench:`<svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>`,
    check:`<svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
};

function esc(s){return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
function fmt(iso){return new Date(iso).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'});}
function count(items, status){return items.filter(c=>c.status===status).length;}

async function loadDashboard() {
    try {
        const res   = await axios.get('/api/v1/admin/complaints', {params:{per_page:50}});
        const items = res.data.data ?? [];
        const meta  = res.data.meta ?? {};
        const total = meta.total ?? items.length;

        // ── Stat Cards ──────────────────────────────────────────────
        const values = {
            total,
            pending:     count(items, 'pending'),
            in_progress: count(items, 'in_progress'),
            resolved:    count(items, 'resolved'),
        };

        document.getElementById('stats-row').innerHTML = STAT_CARDS.map(s => {
            const val = values[s.key];
            const bars = Array.from({length:7},(_,i)=>
                `<div class="mini-bar" style="height:${Math.floor(Math.random()*24+8)}px;width:6px;background:rgba(255,255,255,${0.2+Math.random()*.35});border-radius:3px 3px 0 0;"></div>`
            ).join('');
            return `
            <div class="dash-stat-card" style="background:${s.gradient};box-shadow:0 8px 30px ${s.shadow};">
                <div class="dash-stat-live">LIVE</div>
                <div class="dash-stat-icon">${ICONS[s.icon]}</div>
                <div class="dash-stat-val">${val}</div>
                <div class="dash-stat-label">${s.label}</div>
                <div class="mini-bars" style="margin-top:.75rem;">${bars}</div>
            </div>`;
        }).join('');

        // ── Recent Table (Desktop) ───────────────────────────────────
        if (!items.length) {
            const empty = `<div style="padding:3rem;text-align:center;color:#9ca3af;">
                <svg width="40" height="40" fill="none" stroke="#d1d5db" viewBox="0 0 24 24" style="margin:0 auto .75rem;display:block;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <div style="font-size:.875rem;font-weight:600;color:#6b7280;">No complaints yet</div>
                <div style="font-size:.8rem;color:#9ca3af;margin-top:.25rem;">Reports will appear here once submitted</div>
            </div>`;
            document.getElementById('recent-table').innerHTML = empty;
            document.getElementById('recent-cards').innerHTML = empty;
        } else {
            document.getElementById('recent-table').innerHTML = `
                <table class="dash-table">
                    <thead>
                        <tr>
                            ${['#','Title','Category','Status','Severity','Date'].map(h=>
                                `<th>${h}</th>`
                            ).join('')}
                        </tr>
                    </thead>
                    <tbody>
                        ${items.slice(0,8).map(c => {
                            const s   = STATUS_CFG[c.status]??{label:c.status,color:'#374151',bg:'#f3f4f6',dot:'#9ca3af'};
                            const sev = SEV_CFG[c.severity]??{bg:'#f3f4f6',color:'#374151'};
                            return `<tr>
                                <td class="complaint-num">${esc(c.complaint_number)}</td>
                                <td>
                                    <div style="font-size:.84375rem;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px;">${esc(c.title)}</div>
                                    <div style="font-size:.72rem;color:#9ca3af;margin-top:.125rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px;">📍 ${esc(c.location)}</div>
                                </td>
                                <td style="font-size:.8125rem;color:#6b7280;">${esc(c.category?.name??'—')}</td>
                                <td>
                                    <span class="d-pill" style="background:${s.bg};color:${s.color};">
                                        <span style="width:5px;height:5px;border-radius:50%;background:${s.dot??s.color};margin-right:.3rem;display:inline-block;"></span>
                                        ${s.label}
                                    </span>
                                </td>
                                <td>
                                    <span class="d-pill" style="background:${sev.bg};color:${sev.color};">${(c.severity??'').replace('_',' ')}</span>
                                </td>
                                <td style="font-size:.8rem;color:#9ca3af;white-space:nowrap;">${fmt(c.created_at)}</td>
                            </tr>`;
                        }).join('')}
                    </tbody>
                </table>`;

            // ── Mobile cards ──
            document.getElementById('recent-cards').innerHTML = items.slice(0,8).map(c => {
                const s   = STATUS_CFG[c.status]??{label:c.status,color:'#374151',bg:'#f3f4f6',dot:'#9ca3af',icon:'📌'};
                const sev = SEV_CFG[c.severity]??{bg:'#f3f4f6',color:'#374151'};
                return `<div class="dash-ccard">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;">
                        <div style="min-width:0;">
                            <div class="dash-ccard-title">${esc(c.title)}</div>
                            <div class="dash-ccard-loc">📍 ${esc(c.location)}</div>
                        </div>
                        <span class="d-pill" style="background:${s.bg};color:${s.color};flex-shrink:0;">${s.icon} ${s.label}</span>
                    </div>
                    <div class="dash-ccard-meta">
                        <span class="d-pill" style="background:#f5f3ff;color:#7c3aed;">📂 ${esc(c.category?.name??'—')}</span>
                        <span class="d-pill" style="background:${sev.bg};color:${sev.color};">${(c.severity??'—').replace('_',' ')}</span>
                        <span style="font-size:.72rem;color:#9ca3af;margin-left:auto;">${fmt(c.created_at)}</span>
                    </div>
                </div>`;
            }).join('');
        }

        // ── Status Breakdown ─────────────────────────────────────────
        const statuses   = ['pending','under_review','in_progress','resolved','rejected'];
        const totalItems = Math.max(items.length, 1);

        document.getElementById('status-breakdown').innerHTML = statuses.map(st => {
            const cfg = STATUS_CFG[st];
            const cnt = count(items, st);
            const pct = Math.round((cnt / totalItems) * 100);
            return `<div class="status-item">
                <div style="display:flex;align-items:center;gap:.625rem;flex:1;min-width:0;">
                    <span class="activity-dot" style="background:${cfg.dot??cfg.color};"></span>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:.8125rem;font-weight:600;color:#374151;">${cfg.label}</span>
                            <span style="font-size:.84375rem;font-weight:800;color:${cfg.color};margin-left:.5rem;">${cnt}</span>
                        </div>
                        <div class="status-bar-track">
                            <div class="status-bar-fill" style="width:0%;background:${cfg.gradient??cfg.color};" data-target="${pct}"></div>
                        </div>
                    </div>
                </div>
            </div>`;
        }).join('');

        // Animate bars after render
        requestAnimationFrame(()=>{
            document.querySelectorAll('.status-bar-fill').forEach(el=>{
                const t = el.dataset.target;
                setTimeout(()=>{ el.style.width = t+'%'; }, 100);
            });
        });

        // ── Resolution Rate card ──────────────────────────────────────
        const resolvedCnt = count(items, 'resolved');
        const resRate     = total > 0 ? Math.round((resolvedCnt / total) * 100) : 0;
        const circumference = 2 * Math.PI * 32; // r=32
        const dashOffset    = circumference - (resRate / 100) * circumference;

        document.getElementById('resolution-rate').innerHTML = `
            <div style="display:flex;align-items:center;gap:1.25rem;">
                <div style="position:relative;width:72px;height:72px;flex-shrink:0;">
                    <svg width="72" height="72" viewBox="0 0 80 80">
                        <circle cx="40" cy="40" r="32" fill="none" stroke="#f3f4f6" stroke-width="8"/>
                        <circle cx="40" cy="40" r="32" fill="none" stroke="url(#rg)" stroke-width="8"
                                stroke-linecap="round"
                                stroke-dasharray="${circumference}"
                                stroke-dashoffset="${circumference}"
                                transform="rotate(-90 40 40)"
                                id="rate-circle"
                                style="transition:stroke-dashoffset 1s cubic-bezier(.4,0,.2,1);"/>
                        <defs>
                            <linearGradient id="rg" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" stop-color="#10b981"/>
                                <stop offset="100%" stop-color="#059669"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:.875rem;font-weight:800;color:#059669;">${resRate}%</div>
                </div>
                <div>
                    <div style="font-size:1.25rem;font-weight:800;color:#111827;">${resolvedCnt}<span style="font-size:.8rem;font-weight:500;color:#9ca3af;"> / ${total}</span></div>
                    <div style="font-size:.75rem;color:#9ca3af;margin-top:.125rem;">Complaints resolved</div>
                    <div style="font-size:.75rem;font-weight:600;color:${resRate>=50?'#059669':'#d97706'};margin-top:.375rem;display:flex;align-items:center;gap:.25rem;">
                        ${resRate>=50
                            ? '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg> On track'
                            : '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg> Needs attention'
                        }
                    </div>
                </div>
            </div>`;

        // Animate circle
        setTimeout(()=>{
            const circle = document.getElementById('rate-circle');
            if(circle) circle.setAttribute('stroke-dashoffset', dashOffset);
        }, 200);

    } catch(e) {
        console.error(e);
        document.getElementById('stats-row').innerHTML =
            `<div style="grid-column:1/-1;text-align:center;color:#dc2626;padding:2rem;font-size:.875rem;">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin:0 auto .5rem;display:block;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Failed to load dashboard data. Please refresh.
            </div>`;
    }
}

document.addEventListener('DOMContentLoaded', loadDashboard);
</script>
@endsection

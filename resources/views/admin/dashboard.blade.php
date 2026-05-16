@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', '📊 Dashboard')

@section('content')
<style>
  /* Stats grid: 4-col desktop → 2-col mobile */
  .dash-stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1.25rem;margin-bottom:2rem;}

  /* Main grid: table+sidebar desktop → stacked mobile */
  .dash-main-grid{display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start;}

  /* Table: visible on desktop */
  .dash-table-wrap{display:block;width:100%;overflow:hidden;}
  .dash-table{width:100%;border-collapse:collapse;}

  /* Cards: visible on mobile (hidden desktop) */
  .dash-cards{display:none;flex-direction:column;gap:.75rem;padding:.75rem 1rem 1rem;}

  @media(max-width:900px){
    .dash-main-grid{grid-template-columns:1fr;}
  }
  @media(max-width:640px){
    .dash-stats-grid{grid-template-columns:repeat(2,1fr);gap:.75rem;margin-bottom:1.25rem;}
    .dash-stat-card{padding:1rem !important;}
    .dash-stat-val{font-size:1.625rem !important;}
    /* hide table, show cards on mobile */
    .dash-table-wrap{display:none;}
    .dash-cards{display:flex;}
  }

  /* Complaint card (mobile list item) */
  .dash-ccard{background:#fafafa;border:1.5px solid #f1f1f1;border-radius:.875rem;padding:.875rem 1rem;}
  .dash-ccard-top{display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;margin-bottom:.5rem;}
  .dash-ccard-title{font-size:.875rem;font-weight:700;color:#111827;line-height:1.35;}
  .dash-ccard-loc{font-size:.75rem;color:#9ca3af;margin-top:.15rem;}
  .dash-ccard-meta{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-top:.5rem;}
  .dash-pill{font-size:.7rem;font-weight:700;padding:.2rem .6rem;border-radius:9999px;}
</style>

{{-- Stats row --}}
<div id="stats-row" class="dash-stats-grid">
    @foreach([1,2,3,4] as $i)
    <div style="background:#f3f4f6;border-radius:1rem;height:100px;animation:pulse 1.4s infinite;animation-delay:{{ ($i-1)*0.08 }}s;"></div>
    @endforeach
</div>

{{-- Recent complaints + status breakdown --}}
<div class="dash-main-grid">

    {{-- Recent complaints --}}
    <div class="adm-card" style="min-width:0;overflow:hidden;">
        <div style="padding:1.125rem 1.25rem;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-size:.9375rem;font-weight:700;color:#111827;">📋 Recent Complaints</h2>
            <a href="{{ route('admin.complaints.index') }}"
               style="font-size:.8125rem;font-weight:600;color:#4f46e5;text-decoration:none;white-space:nowrap;">View all →</a>
        </div>

        {{-- Desktop table (hidden on mobile via CSS) --}}
        <div class="dash-table-wrap" id="recent-table">
            <div style="padding:2rem;text-align:center;color:#9ca3af;font-size:.875rem;">Loading…</div>
        </div>

        {{-- Mobile cards (hidden on desktop via CSS) --}}
        <div class="dash-cards" id="recent-cards">
            {{-- populated by JS --}}
        </div>
    </div>

    {{-- Status breakdown --}}
    <div class="adm-card" style="padding:1.25rem;min-width:0;">
        <h2 style="font-size:.9375rem;font-weight:700;color:#111827;margin-bottom:1.25rem;">📈 By Status</h2>
        <div id="status-breakdown" style="display:flex;flex-direction:column;gap:.875rem;">
            <div style="background:#f3f4f6;border-radius:.75rem;height:40px;animation:pulse 1.4s infinite;"></div>
            <div style="background:#f3f4f6;border-radius:.75rem;height:40px;animation:pulse 1.4s infinite .1s;"></div>
            <div style="background:#f3f4f6;border-radius:.75rem;height:40px;animation:pulse 1.4s infinite .2s;"></div>
            <div style="background:#f3f4f6;border-radius:.75rem;height:40px;animation:pulse 1.4s infinite .3s;"></div>
        </div>
    </div>

</div>

<script>
const STATUS_CFG = {
    pending:      {label:'Pending',      color:'#92400e', bg:'#fffbeb', icon:'⏳'},
    under_review: {label:'Under Review', color:'#1e40af', bg:'#eff6ff', icon:'🔍'},
    in_progress:  {label:'In Progress',  color:'#1d4ed8', bg:'#dbeafe', icon:'🔧'},
    resolved:     {label:'Resolved',     color:'#14532d', bg:'#f0fdf4', icon:'✅'},
    rejected:     {label:'Rejected',     color:'#7f1d1d', bg:'#fef2f2', icon:'❌'},
};
const SEV_CFG = {
    low:       {bg:'#d1fae5',color:'#065f46'},
    medium:    {bg:'#fef9c3',color:'#713f12'},
    high:      {bg:'#ffedd5',color:'#7c2d12'},
    emergency: {bg:'#fee2e2',color:'#7f1d1d'},
};

function esc(s){return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
function fmt(iso){return new Date(iso).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'});}

async function loadDashboard() {
    try {
        const res   = await axios.get('/api/v1/admin/complaints', {params:{per_page:10}});
        const items = res.data.data ?? [];
        const meta  = res.data.meta ?? {};
        const total = meta.total ?? items.length;

        // ── Stats cards ──
        const statCards = [
            {label:'Total Complaints', value:total,                                                     icon:'📋', bg:'#eef2ff', color:'#4f46e5'},
            {label:'Pending',          value:items.filter(c=>c.status==='pending').length,              icon:'⏳', bg:'#fffbeb', color:'#92400e'},
            {label:'In Progress',      value:items.filter(c=>c.status==='in_progress').length,          icon:'🔧', bg:'#dbeafe', color:'#1d4ed8'},
            {label:'Resolved',         value:items.filter(c=>c.status==='resolved').length,             icon:'✅', bg:'#f0fdf4', color:'#14532d'},
        ];
        document.getElementById('stats-row').innerHTML = statCards.map(s => `
            <div class="dash-stat-card" style="background:${s.bg};border-radius:1rem;padding:1.375rem 1.25rem;border:1px solid ${s.color}22;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
                    <span style="font-size:1.25rem;">${s.icon}</span>
                    <span style="font-size:.65rem;font-weight:700;color:${s.color};background:${s.color}18;
                                 padding:.15rem .5rem;border-radius:9999px;">LIVE</span>
                </div>
                <div class="dash-stat-val" style="font-size:1.875rem;font-weight:900;color:${s.color};line-height:1;">${s.value}</div>
                <div style="font-size:.75rem;font-weight:600;color:${s.color};opacity:.75;margin-top:.25rem;">${s.label}</div>
            </div>`).join('');

        if (!items.length) {
            const empty = '<div style="padding:2rem;text-align:center;color:#9ca3af;">No complaints yet.</div>';
            document.getElementById('recent-table').innerHTML = empty;
            document.getElementById('recent-cards').innerHTML = empty;
        } else {
            // ── Desktop table ──
            document.getElementById('recent-table').innerHTML = `
                <table class="dash-table">
                    <thead>
                        <tr style="border-bottom:1px solid #f3f4f6;">
                            ${['#','Title','Category','Status','Severity','Date'].map(h=>
                                `<th style="padding:.625rem 1rem;text-align:left;font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap;">${h}</th>`
                            ).join('')}
                        </tr>
                    </thead>
                    <tbody>
                        ${items.slice(0,8).map(c => {
                            const s   = STATUS_CFG[c.status]??{label:c.status,color:'#374151',bg:'#f3f4f6'};
                            const sev = SEV_CFG[c.severity]??{bg:'#f3f4f6',color:'#374151'};
                            return `<tr style="border-bottom:1px solid #f9fafb;transition:background .1s;"
                                        onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
                                <td style="padding:.625rem 1rem;font-size:.75rem;color:#9ca3af;white-space:nowrap;">${esc(c.complaint_number)}</td>
                                <td style="padding:.625rem 1rem;font-size:.8125rem;font-weight:600;color:#111827;max-width:180px;">
                                    <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(c.title)}</div>
                                    <div style="font-size:.72rem;color:#9ca3af;margin-top:.1rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(c.location)}</div>
                                </td>
                                <td style="padding:.625rem 1rem;font-size:.8rem;color:#374151;">${esc(c.category?.name??'—')}</td>
                                <td style="padding:.625rem 1rem;">
                                    <span style="background:${s.bg};color:${s.color};font-size:.68rem;font-weight:700;padding:.2rem .55rem;border-radius:9999px;">${s.label}</span>
                                </td>
                                <td style="padding:.625rem 1rem;">
                                    <span style="background:${sev.bg};color:${sev.color};font-size:.68rem;font-weight:700;padding:.2rem .55rem;border-radius:9999px;">${(c.severity??'').replace('_',' ')}</span>
                                </td>
                                <td style="padding:.625rem 1rem;font-size:.8rem;color:#6b7280;white-space:nowrap;">${fmt(c.created_at)}</td>
                            </tr>`;
                        }).join('')}
                    </tbody>
                </table>`;

            // ── Mobile cards ──
            document.getElementById('recent-cards').innerHTML = items.slice(0,8).map(c => {
                const s   = STATUS_CFG[c.status]??{label:c.status,color:'#374151',bg:'#f3f4f6',icon:'📌'};
                const sev = SEV_CFG[c.severity]??{bg:'#f3f4f6',color:'#374151'};
                return `<div class="dash-ccard">
                    <div class="dash-ccard-top">
                        <div style="min-width:0;">
                            <div class="dash-ccard-title">${esc(c.title)}</div>
                            <div class="dash-ccard-loc">📍 ${esc(c.location)}</div>
                        </div>
                        <span class="dash-pill" style="background:${s.bg};color:${s.color};flex-shrink:0;">${s.icon} ${s.label}</span>
                    </div>
                    <div class="dash-ccard-meta">
                        <span class="dash-pill" style="background:#f3f4f6;color:#374151;">📂 ${esc(c.category?.name??'—')}</span>
                        <span class="dash-pill" style="background:${sev.bg};color:${sev.color};">${(c.severity??'—').replace('_',' ')}</span>
                        <span style="font-size:.72rem;color:#9ca3af;margin-left:auto;">${fmt(c.created_at)}</span>
                    </div>
                </div>`;
            }).join('');
        }

        // ── Status breakdown ──
        const statuses = ['pending','under_review','in_progress','resolved','rejected'];
        const totalAll = items.length || 1;
        document.getElementById('status-breakdown').innerHTML = statuses.map(st => {
            const cfg = STATUS_CFG[st];
            const cnt = items.filter(c => c.status === st).length;
            const pct = Math.round((cnt / totalAll) * 100);
            return `<div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.375rem;">
                    <span style="font-size:.8125rem;font-weight:600;color:#374151;">${cfg.icon} ${cfg.label}</span>
                    <span style="font-size:.8125rem;font-weight:700;color:${cfg.color};">${cnt}</span>
                </div>
                <div style="height:6px;background:#f3f4f6;border-radius:9999px;overflow:hidden;">
                    <div style="height:100%;width:${pct}%;background:${cfg.color};border-radius:9999px;transition:width .6s ease;"></div>
                </div>
            </div>`;
        }).join('');

    } catch(e) {
        document.getElementById('stats-row').innerHTML =
            '<div style="grid-column:1/-1;text-align:center;color:#dc2626;padding:2rem;">Failed to load dashboard data.</div>';
    }
}

document.addEventListener('DOMContentLoaded', loadDashboard);
</script>
@endsection

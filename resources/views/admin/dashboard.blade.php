@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', '📊 Dashboard')

@section('content')

{{-- Stats row --}}
<div id="stats-row" style="display:grid;grid-template-columns:repeat(4,1fr);gap:1.25rem;margin-bottom:2rem;">
    @foreach([1,2,3,4] as $i)
    <div style="background:#f3f4f6;border-radius:1rem;height:100px;animation:pulse 1.4s infinite;animation-delay:{{ ($i-1)*0.08 }}s;"></div>
    @endforeach
</div>

{{-- Recent complaints table + status breakdown --}}
<div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start;">

    {{-- Recent complaints --}}
    <div class="adm-card">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-size:.9375rem;font-weight:700;color:#111827;">📋 Recent Complaints</h2>
            <a href="{{ route('admin.complaints.index') }}"
               style="font-size:.8125rem;font-weight:600;color:#4f46e5;text-decoration:none;">View all →</a>
        </div>
        <div id="recent-table" style="overflow-x:auto;">
            <div style="padding:2rem;text-align:center;color:#9ca3af;font-size:.875rem;">Loading…</div>
        </div>
    </div>

    {{-- Status breakdown --}}
    <div class="adm-card" style="padding:1.25rem;">
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

        // ── Count by status ──
        const counts = {};
        items.forEach(c => { counts[c.status] = (counts[c.status]||0)+1; });

        // ── Stats cards ──
        const statCards = [
            {label:'Total Complaints', value:total,                                icon:'📋', bg:'#eef2ff', color:'#4f46e5'},
            {label:'Pending',          value:items.filter(c=>c.status==='pending').length, icon:'⏳', bg:'#fffbeb', color:'#92400e'},
            {label:'In Progress',      value:items.filter(c=>c.status==='in_progress').length, icon:'🔧', bg:'#dbeafe', color:'#1d4ed8'},
            {label:'Resolved',         value:items.filter(c=>c.status==='resolved').length, icon:'✅', bg:'#f0fdf4', color:'#14532d'},
        ];
        document.getElementById('stats-row').innerHTML = statCards.map(s => `
            <div style="background:${s.bg};border-radius:1rem;padding:1.375rem 1.5rem;
                        border:1px solid ${s.color}22;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.625rem;">
                    <span style="font-size:1.375rem;">${s.icon}</span>
                    <span style="font-size:.7rem;font-weight:700;color:${s.color};background:${s.color}18;
                                 padding:.15rem .5rem;border-radius:9999px;">LIVE</span>
                </div>
                <div style="font-size:2rem;font-weight:900;color:${s.color};line-height:1;">${s.value}</div>
                <div style="font-size:.78rem;font-weight:600;color:${s.color};opacity:.75;margin-top:.25rem;">${s.label}</div>
            </div>`).join('');

        // ── Recent table ──
        if (!items.length) {
            document.getElementById('recent-table').innerHTML =
                '<div style="padding:2rem;text-align:center;color:#9ca3af;">No complaints yet.</div>';
        } else {
            document.getElementById('recent-table').innerHTML = `
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid #f3f4f6;">
                            ${['#','Title','Category','Status','Severity','Submitted'].map(h=>
                                `<th style="padding:.625rem 1.25rem;text-align:left;font-size:.75rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap;">${h}</th>`
                            ).join('')}
                        </tr>
                    </thead>
                    <tbody>
                        ${items.slice(0,8).map(c => {
                            const s   = STATUS_CFG[c.status]??{label:c.status,color:'#374151',bg:'#f3f4f6'};
                            const sev = SEV_CFG[c.severity]??{bg:'#f3f4f6',color:'#374151'};
                            return `<tr style="border-bottom:1px solid #f9fafb;transition:background .1s;"
                                        onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
                                <td style="padding:.75rem 1.25rem;font-size:.78rem;color:#9ca3af;white-space:nowrap;">${esc(c.complaint_number)}</td>
                                <td style="padding:.75rem 1.25rem;font-size:.875rem;font-weight:600;color:#111827;max-width:200px;">
                                    <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(c.title)}</div>
                                    <div style="font-size:.75rem;color:#9ca3af;margin-top:.1rem;">${esc(c.location)}</div>
                                </td>
                                <td style="padding:.75rem 1.25rem;font-size:.8125rem;color:#374151;">${esc(c.category?.name??'—')}</td>
                                <td style="padding:.75rem 1.25rem;">
                                    <span style="background:${s.bg};color:${s.color};font-size:.7rem;font-weight:700;padding:.2rem .625rem;border-radius:9999px;">${s.label}</span>
                                </td>
                                <td style="padding:.75rem 1.25rem;">
                                    <span style="background:${sev.bg};color:${sev.color};font-size:.7rem;font-weight:700;padding:.2rem .625rem;border-radius:9999px;">${(c.severity??'').replace('_',' ')}</span>
                                </td>
                                <td style="padding:.75rem 1.25rem;font-size:.8125rem;color:#6b7280;white-space:nowrap;">${fmt(c.created_at)}</td>
                            </tr>`;
                        }).join('')}
                    </tbody>
                </table>`;
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

@extends('layouts.engineer')
@section('title', 'Completed Tasks')
@section('page-title', '🏆 Completed Tasks')

@section('content')

{{-- Stats row --}}
<div id="stats-row" style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;margin-bottom:2rem;">
    @foreach([1,2,3] as $i)
    <div style="background:#f3f4f6;border-radius:1rem;height:90px;animation:pulse 1.4s infinite;animation-delay:{{ ($i-1)*0.08 }}s;"></div>
    @endforeach
</div>

{{-- Header info bar --}}
<div class="eng-card" style="padding:.875rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;
     background:linear-gradient(135deg,#f0fdf4,#dcfce7);border-color:#86efac;">
    <div style="display:flex;align-items:center;gap:.5rem;">
        <span style="font-size:1.25rem;">🏆</span>
        <span style="font-size:.875rem;font-weight:700;color:#14532d;">Verified & Closed Complaints</span>
    </div>
    <span style="font-size:.8125rem;color:#16a34a;">These tasks have been verified by the admin. No further action required.</span>
    <div id="filter-count" style="margin-left:auto;font-size:.8125rem;color:#059669;font-weight:700;"></div>
</div>

{{-- Complaints list --}}
<div class="eng-card">
    <div id="eng-loading" style="padding:3rem;text-align:center;">
        <div style="display:inline-block;width:32px;height:32px;border:3px solid #e5e7eb;
                    border-top-color:#10b981;border-radius:50%;animation:spin .7s linear infinite;"></div>
        <p style="margin-top:.75rem;color:#9ca3af;font-size:.875rem;">Loading completed tasks…</p>
    </div>
    <div id="eng-list" style="display:none;"></div>
    <div id="eng-empty" style="display:none;padding:4rem;text-align:center;">
        <div style="font-size:3rem;margin-bottom:1rem;">📭</div>
        <p style="font-size:1rem;font-weight:700;color:#111827;margin-bottom:.375rem;">No completed tasks yet.</p>
        <p style="font-size:.875rem;color:#9ca3af;">Once admin verifies your work, it will appear here.</p>
    </div>
    <div id="pagination" style="padding:1rem 1.5rem;border-top:1px solid #f3f4f6;display:none;
                                align-items:center;justify-content:space-between;">
        <span id="pag-info" style="font-size:.8125rem;color:#6b7280;"></span>
        <div style="display:flex;gap:.5rem;">
            <button id="pag-prev" class="eng-btn eng-btn-outline eng-btn-sm" onclick="changePage(-1)">← Prev</button>
            <button id="pag-next" class="eng-btn eng-btn-outline eng-btn-sm" onclick="changePage(1)">Next →</button>
        </div>
    </div>
</div>

<script>
const SEV_CFG = {
    low:       {bg:'#d1fae5',color:'#065f46'},
    medium:    {bg:'#fef9c3',color:'#713f12'},
    high:      {bg:'#ffedd5',color:'#7c2d12'},
    emergency: {bg:'#fee2e2',color:'#7f1d1d'},
};

let currentPage = 1;
let totalPages  = 1;

function esc(s){return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
function fmt(iso){return new Date(iso).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'});}
function timeAgo(iso){
    const d = Math.floor((Date.now()-new Date(iso))/1000);
    if(d<60)   return 'just now';
    if(d<3600) return Math.floor(d/60)+'m ago';
    if(d<86400)return Math.floor(d/3600)+'h ago';
    return Math.floor(d/86400)+'d ago';
}

function changePage(dir){ const n = currentPage+dir; if(n>=1&&n<=totalPages){currentPage=n;loadCompleted();} }

async function loadCompleted() {
    document.getElementById('eng-loading').style.display = 'block';
    document.getElementById('eng-list').style.display    = 'none';
    document.getElementById('eng-empty').style.display   = 'none';

    try {
        const res   = await axios.get('/api/v1/engineer/complaints', {params:{completed:1, page:currentPage, per_page:12}});
        const items = res.data.data ?? [];
        const meta  = res.data.meta ?? {};
        totalPages  = meta.last_page ?? 1;

        const total    = meta.total ?? items.length;
        const thisMonth = items.filter(c => {
            const d = new Date(c.updated_at);
            const now = new Date();
            return d.getMonth()===now.getMonth() && d.getFullYear()===now.getFullYear();
        }).length;

        document.getElementById('filter-count').textContent = `${total} verified task(s)`;
        document.getElementById('eng-loading').style.display = 'none';

        // ── Stats ──
        document.getElementById('stats-row').innerHTML = [
            {label:'Total Verified',    value:total,     icon:'🏆', bg:'#f0fdf4', color:'#14532d'},
            {label:'This Month',        value:thisMonth, icon:'📅', bg:'#ede9fe', color:'#5b21b6'},
            {label:'Great Work!',       value:'★',       icon:'⭐', bg:'#fef9c3', color:'#713f12'},
        ].map(s => `
            <div style="background:${s.bg};border-radius:1rem;padding:1.25rem 1.5rem;">
                <div style="font-size:1.375rem;margin-bottom:.5rem;">${s.icon}</div>
                <div style="font-size:1.875rem;font-weight:900;color:${s.color};line-height:1;">${s.value}</div>
                <div style="font-size:.78rem;font-weight:600;color:${s.color};opacity:.75;margin-top:.25rem;">${s.label}</div>
            </div>`).join('');

        if (!items.length) {
            document.getElementById('eng-empty').style.display = 'block';
            document.getElementById('pagination').style.display = 'none';
            return;
        }

        document.getElementById('pagination').style.display = 'flex';
        document.getElementById('pag-info').textContent     = `Page ${currentPage} of ${totalPages}`;
        document.getElementById('pag-prev').disabled        = currentPage <= 1;
        document.getElementById('pag-next').disabled        = currentPage >= totalPages;

        document.getElementById('eng-list').style.display = 'block';
        document.getElementById('eng-list').innerHTML = `
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:1rem;padding:1.5rem;">
                ${items.map(c => {
                    const sev = SEV_CFG[c.severity] ?? {bg:'#f3f4f6', color:'#374151'};
                    return `
                    <div style="background:#fafff8;border:2px solid #86efac;border-radius:1rem;padding:1.25rem;
                                transition:all .2s;cursor:pointer;position:relative;"
                         onmouseover="this.style.borderColor='#4ade80';this.style.boxShadow='0 4px 20px rgba(16,185,129,.15)'"
                         onmouseout="this.style.borderColor='#86efac';this.style.boxShadow='none'"
                         onclick="window.location='/engineer/complaints/${c.id}'">

                        {{-- Verified ribbon --}}
                        <div style="position:absolute;top:-1px;right:1rem;background:linear-gradient(135deg,#10b981,#059669);
                                    color:#fff;font-size:.6rem;font-weight:800;padding:.2rem .6rem;
                                    border-radius:0 0 .5rem .5rem;letter-spacing:.04em;">✓ VERIFIED</div>

                        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:.875rem;gap:.5rem;">
                            <div style="display:flex;align-items:center;gap:.5rem;flex:1;min-width:0;">
                                <span style="font-size:1.375rem;">${c.category?.icon ?? '📌'}</span>
                                <div style="min-width:0;">
                                    <p style="font-size:.9375rem;font-weight:700;color:#111827;margin:0;
                                               white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(c.title)}</p>
                                    <p style="font-size:.72rem;color:#9ca3af;margin:.1rem 0 0;">${esc(c.complaint_number)}</p>
                                </div>
                            </div>
                        </div>

                        <div style="font-size:.8125rem;color:#6b7280;margin-bottom:.75rem;display:flex;align-items:center;gap:.25rem;">
                            📍 ${esc(c.location)}
                        </div>

                        <div style="display:flex;align-items:center;justify-content:space-between;">
                            <span style="background:${sev.bg};color:${sev.color};font-size:.7rem;font-weight:700;
                                         padding:.2rem .5rem;border-radius:9999px;">
                                ${(c.severity??'').charAt(0).toUpperCase()+(c.severity??'').slice(1)}
                            </span>
                            <div style="display:flex;align-items:center;gap:.5rem;">
                                <span style="font-size:.72rem;color:#059669;font-weight:600;">
                                    ✅ Verified ${c.resolved_at ? fmt(c.resolved_at) : timeAgo(c.updated_at)}
                                </span>
                                <span style="font-size:.72rem;font-weight:600;color:#0ea5e9;
                                    background:#e0f2fe;padding:.15rem .5rem;border-radius:9999px;">View →</span>
                            </div>
                        </div>
                    </div>`;
                }).join('')}
            </div>`;

    } catch(e) {
        document.getElementById('eng-loading').innerHTML =
            '<div style="padding:2rem;text-align:center;color:#dc2626;">Failed to load. <button onclick="loadCompleted()" style="color:#0ea5e9;background:none;border:none;cursor:pointer;font-weight:600;">Retry</button></div>';
    }
}

document.addEventListener('DOMContentLoaded', loadCompleted);
</script>
@endsection

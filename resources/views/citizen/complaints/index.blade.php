@extends('layouts.citizen')

@section('title', 'My Complaints')

@section('content')

{{-- ── Page header ── --}}
<div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1 style="font-size:1.5rem; font-weight:700; color:#111827; margin:0;">My Complaints</h1>
        <p style="font-size:0.875rem; color:#6b7280; margin:0.25rem 0 0;">Track all your submitted road issues</p>
    </div>
    <a href="{{ route('citizen.complaints.create') }}" class="rw-btn-primary">🚨 Report New Issue</a>
</div>

{{-- ── Stats strip (populated by JS) ── --}}
<div id="stats-strip" style="display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:2rem;">
    @foreach([['Total','#4f46e5','#eef2ff','📋'],['Pending','#d97706','#fffbeb','⏳'],['In Progress','#0284c7','#eff6ff','🔧'],['Resolved','#16a34a','#f0fdf4','✅']] as [$label,$color,$bg,$icon])
    <div style="background:{{ $bg }}; border:1px solid {{ $color }}22; border-radius:1rem; padding:1.125rem 1rem;">
        <div style="font-size:1.5rem;">{{ $icon }}</div>
        <div id="stat-{{ strtolower($label) }}" style="font-size:1.75rem; font-weight:800; color:{{ $color }}; line-height:1.1;">—</div>
        <div style="font-size:0.75rem; font-weight:600; color:{{ $color }}; opacity:0.8; margin-top:0.125rem;">{{ $label }}</div>
    </div>
    @endforeach
</div>

{{-- ── List container ── --}}
<div id="complaints-container">
    {{-- Loading skeleton --}}
    @for($i = 0; $i < 3; $i++)
    <div class="skeleton-card" style="height:80px; border-radius:1rem; background:#f3f4f6; margin-bottom:1rem; animation:pulse 1.5s infinite;"></div>
    @endfor
</div>

{{-- ── Pagination ── --}}
<div id="pagination-wrap" style="margin-top:1.5rem; display:flex; justify-content:center; gap:0.5rem;"></div>

<style>
.rw-btn-primary {
    display:inline-flex; align-items:center; gap:0.375rem;
    background:#4f46e5; color:#fff; text-decoration:none;
    border-radius:0.75rem; padding:0.625rem 1.25rem;
    font-size:0.875rem; font-weight:600;
    box-shadow:0 2px 8px rgba(79,70,229,0.25);
    transition:background 0.15s, transform 0.1s;
}
.rw-btn-primary:hover { background:#4338ca; transform:translateY(-1px); }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.5} }

.complaint-card {
    display:flex; align-items:stretch; background:#fff;
    border:1px solid #e5e7eb; border-radius:1rem; overflow:hidden;
    text-decoration:none; color:inherit; margin-bottom:1rem;
    box-shadow:0 1px 3px rgba(0,0,0,0.06);
    transition:border-color 0.15s, box-shadow 0.15s;
}
.complaint-card:hover { border-color:#a5b4fc; box-shadow:0 4px 12px rgba(79,70,229,0.12); }
.page-btn {
    padding:0.375rem 0.75rem; border:1px solid #e5e7eb;
    border-radius:0.5rem; background:#fff; cursor:pointer;
    font-size:0.875rem; color:#374151; transition:background 0.15s;
}
.page-btn:hover { background:#eef2ff; border-color:#a5b4fc; color:#4f46e5; }
.page-btn.active { background:#4f46e5; color:#fff; border-color:#4f46e5; }
</style>

<script>
const STATUS_CONFIG = {
    pending:      { label:'⏳ Pending',       color:'#92400e', bg:'#fffbeb', border:'#fcd34d' },
    under_review: { label:'🔍 Under Review',  color:'#1e40af', bg:'#eff6ff', border:'#93c5fd' },
    in_progress:  { label:'🔧 In Progress',   color:'#1d4ed8', bg:'#dbeafe', border:'#60a5fa' },
    resolved:     { label:'✅ Resolved',      color:'#14532d', bg:'#f0fdf4', border:'#86efac' },
    rejected:     { label:'❌ Rejected',      color:'#7f1d1d', bg:'#fef2f2', border:'#fca5a5' },
};
const SEV_CONFIG = {
    low:       { label:'🟢 Low',       color:'#065f46', bg:'#d1fae5' },
    medium:    { label:'🟡 Medium',    color:'#713f12', bg:'#fef9c3' },
    high:      { label:'🟠 High',      color:'#7c2d12', bg:'#ffedd5' },
    emergency: { label:'🔴 Emergency', color:'#7f1d1d', bg:'#fee2e2' },
};

let currentPage = 1;

async function loadComplaints(page = 1) {
    currentPage = page;
    const container = document.getElementById('complaints-container');

    try {
        const res  = await axios.get(`/api/v1/citizen/complaints?page=${page}&per_page=10`);
        const data = res.data;

        // Update stats
        updateStats(data.meta?.total || data.data?.length || 0, data.data);

        if (!data.data || data.data.length === 0) {
            container.innerHTML = renderEmpty();
            document.getElementById('pagination-wrap').innerHTML = '';
            return;
        }

        container.innerHTML = data.data.map(renderCard).join('');
        renderPagination(data.meta, data.links);

    } catch (err) {
        container.innerHTML = `<div style="text-align:center;color:#dc2626;padding:2rem;">
            Failed to load complaints. <button onclick="loadComplaints()" style="color:#4f46e5;background:none;border:none;cursor:pointer;">Retry</button>
        </div>`;
    }
}

function updateStats(total, complaints) {
    document.getElementById('stat-total').textContent     = total;
    const pending   = complaints.filter(c => c.status === 'pending').length;
    const progress  = complaints.filter(c => ['under_review','in_progress'].includes(c.status)).length;
    const resolved  = complaints.filter(c => c.status === 'resolved').length;
    document.getElementById('stat-pending').textContent     = pending;
    document.getElementById('stat-in progress').textContent = progress;
    document.getElementById('stat-resolved').textContent    = resolved;
}

function renderCard(c) {
    const s   = STATUS_CONFIG[c.status] || { label:c.status, color:'#374151', bg:'#f3f4f6', border:'#d1d5db' };
    const sev = SEV_CONFIG[c.severity] || { label:c.severity, color:'#374151', bg:'#f3f4f6' };
    const icon = c.category?.icon ?? '📌';
    const date = new Date(c.created_at).toLocaleDateString('en-IN',{ day:'2-digit', month:'short', year:'numeric' });

    return `
    <a href="/citizen/complaints/${c.id}" class="complaint-card">
        <div style="width:5px;background:${s.border};flex-shrink:0;"></div>
        <div style="display:flex;align-items:center;justify-content:center;width:64px;padding:1rem;flex-shrink:0;font-size:2rem;">${icon}</div>
        <div style="flex:1;padding:1rem 1rem 1rem 0;min-width:0;">
            <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;margin-bottom:0.375rem;">
                <span style="font-size:0.7rem;font-family:monospace;background:#f3f4f6;color:#6b7280;padding:0.125rem 0.5rem;border-radius:9999px;">${c.complaint_number}</span>
                <span style="font-size:0.7rem;font-weight:600;padding:0.2rem 0.625rem;border-radius:9999px;background:${s.bg};color:${s.color};border:1px solid ${s.border};">${s.label}</span>
                <span style="font-size:0.7rem;font-weight:600;padding:0.2rem 0.5rem;border-radius:9999px;background:${sev.bg};color:${sev.color};">${sev.label}</span>
            </div>
            <h3 style="font-size:0.9375rem;font-weight:700;color:#111827;margin:0 0 0.25rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${c.title}</h3>
            <p style="font-size:0.8125rem;color:#6b7280;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">📍 ${c.location}</p>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;justify-content:center;padding:1rem;flex-shrink:0;text-align:right;gap:0.375rem;">
            <span style="font-size:0.75rem;color:#9ca3af;">${date}</span>
            <span style="font-size:0.75rem;color:#6b7280;font-weight:500;">${c.category?.name ?? ''}</span>
            ${c.votes_count > 0 ? `<span style="font-size:0.75rem;color:#4f46e5;">👍 ${c.votes_count}</span>` : ''}
            <span style="font-size:0.75rem;color:#9ca3af;">View →</span>
        </div>
    </a>`;
}

function renderEmpty() {
    return `<div style="background:#fff;border:1px solid #e5e7eb;border-radius:1.25rem;padding:5rem 2rem;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
        <div style="font-size:4rem;margin-bottom:1rem;">🛣️</div>
        <h3 style="font-size:1.125rem;font-weight:700;color:#111827;margin:0 0 0.5rem;">No complaints submitted yet</h3>
        <p style="font-size:0.875rem;color:#6b7280;margin:0 0 1.5rem;">Help improve your city — report a road issue.</p>
        <a href="/citizen/complaints/create" class="rw-btn-primary">Report your first issue</a>
    </div>`;
}

function renderPagination(meta, links) {
    if (!meta || meta.last_page <= 1) { document.getElementById('pagination-wrap').innerHTML = ''; return; }
    let html = '';
    if (meta.current_page > 1)
        html += `<button class="page-btn" onclick="loadComplaints(${meta.current_page - 1})">← Prev</button>`;
    for (let p = Math.max(1, meta.current_page-2); p <= Math.min(meta.last_page, meta.current_page+2); p++)
        html += `<button class="page-btn ${p === meta.current_page ? 'active' : ''}" onclick="loadComplaints(${p})">${p}</button>`;
    if (meta.current_page < meta.last_page)
        html += `<button class="page-btn" onclick="loadComplaints(${meta.current_page + 1})">Next →</button>`;
    document.getElementById('pagination-wrap').innerHTML = html;
}

document.addEventListener('DOMContentLoaded', () => loadComplaints(1));
</script>

@endsection

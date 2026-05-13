@extends('layouts.admin')
@section('title', 'Categories')
@section('page-title', '🏷️ Categories')

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
    <div>
        <p style="font-size:.9rem;color:#6b7280;margin:0;">Manage complaint categories shown to citizens.</p>
    </div>
</div>

<div class="adm-card">
    <div id="cat-loading" style="padding:3rem;text-align:center;">
        <div style="display:inline-block;width:32px;height:32px;border:3px solid #e5e7eb;
                    border-top-color:#4f46e5;border-radius:50%;animation:spin .7s linear infinite;"></div>
        <p style="margin-top:.75rem;color:#9ca3af;font-size:.875rem;">Loading categories…</p>
    </div>
    <div id="cat-grid" style="display:none;padding:1.5rem;
         display:none;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;"></div>
</div>

<style>@keyframes spin{to{transform:rotate(360deg)}}</style>

<script>
function esc(s){return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}

async function loadCategories() {
    try {
        const res  = await fetch('/api/v1/categories');
        const cats = (await res.json()).data ?? [];

        document.getElementById('cat-loading').style.display = 'none';
        const grid = document.getElementById('cat-grid');
        grid.style.display = 'grid';

        if (!cats.length) {
            grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;color:#9ca3af;padding:2rem;">No categories found. Run the CategorySeeder.</div>';
            return;
        }

        grid.innerHTML = cats.map(c => `
            <div style="background:#fafafa;border:1.5px solid #f1f1f1;border-radius:1rem;
                        padding:1.5rem;text-align:center;transition:all .2s;cursor:default;"
                 onmouseover="this.style.borderColor='#a5b4fc';this.style.background='#f5f3ff';"
                 onmouseout="this.style.borderColor='#f1f1f1';this.style.background='#fafafa';">
                <div style="font-size:2.5rem;margin-bottom:.75rem;">${c.icon ?? '📌'}</div>
                <div style="font-size:.9375rem;font-weight:700;color:#111827;margin-bottom:.25rem;">${esc(c.name)}</div>
                <div style="font-size:.78rem;color:#9ca3af;line-height:1.5;">${esc(c.description ?? '')}</div>
                <div style="margin-top:.875rem;display:inline-flex;align-items:center;gap:.25rem;
                            background:#eef2ff;color:#4f46e5;font-size:.72rem;font-weight:700;
                            padding:.2rem .625rem;border-radius:9999px;">ID: ${c.id}</div>
            </div>`).join('');

    } catch(e) {
        document.getElementById('cat-loading').innerHTML =
            '<div style="padding:2rem;text-align:center;color:#dc2626;">Failed to load. <button onclick="loadCategories()" style="color:#4f46e5;background:none;border:none;cursor:pointer;font-weight:600;">Retry</button></div>';
    }
}

document.addEventListener('DOMContentLoaded', loadCategories);
</script>
@endsection

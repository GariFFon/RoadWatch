@extends('layouts.admin')
@section('title', 'Users')
@section('page-title', '👥 User Management')

@section('content')

{{-- Search + filter bar --}}
<div class="adm-card" style="padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
    <input type="text" id="f-search" class="adm-input" placeholder="🔍  Search name or email…"
           style="flex:1;min-width:200px;" oninput="debounceSearch()">
    <select id="f-role" class="adm-input adm-select" onchange="applyFilters()">
        <option value="">All Roles</option>
        <option value="citizen">👤 Citizen</option>
        <option value="engineer">👷 Engineer</option>
        <option value="admin">🛡 Admin</option>
    </select>
    <button class="adm-btn adm-btn-outline" onclick="applyFilters()">🔄 Refresh</button>
    <div id="user-count" style="margin-left:auto;font-size:.8125rem;color:#6b7280;font-weight:600;"></div>
</div>

{{-- Users table card --}}
<div class="adm-card">
    <div id="users-loading" style="padding:3rem;text-align:center;">
        <div style="display:inline-block;width:32px;height:32px;border:3px solid #e5e7eb;
                    border-top-color:#4f46e5;border-radius:50%;animation:spin .7s linear infinite;"></div>
        <p style="margin-top:.75rem;color:#9ca3af;font-size:.875rem;">Loading users…</p>
    </div>
    <div id="users-table" style="display:none;overflow-x:auto;"></div>
    <div id="users-empty" style="display:none;padding:3rem;text-align:center;color:#9ca3af;">No users match your search.</div>
    <div id="pagination" style="padding:1rem 1.5rem;border-top:1px solid #f3f4f6;
                                display:none;align-items:center;justify-content:space-between;">
        <span id="pag-info" style="font-size:.8125rem;color:#6b7280;"></span>
        <div style="display:flex;gap:.5rem;">
            <button id="pag-prev" class="adm-btn adm-btn-outline adm-btn-sm" onclick="changePage(-1)">← Prev</button>
            <button id="pag-next" class="adm-btn adm-btn-outline adm-btn-sm" onclick="changePage(1)">Next →</button>
        </div>
    </div>
</div>

{{-- Role change confirmation modal --}}
<div class="adm-modal-bg" id="role-modal">
    <div class="adm-modal">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
            <h3 class="adm-modal-title" style="margin:0;">🔄 Change User Role</h3>
            <button onclick="closeRoleModal()" style="background:none;border:none;font-size:1.25rem;cursor:pointer;color:#9ca3af;">✕</button>
        </div>
        <div id="modal-user-info" style="background:#f9fafb;border-radius:.75rem;padding:.875rem;margin-bottom:1.25rem;font-size:.875rem;"></div>
        <label style="display:block;font-size:.8125rem;font-weight:600;color:#374151;margin-bottom:.375rem;">New Role</label>
        <select id="role-select" class="adm-input adm-select" style="width:100%;margin-bottom:1.25rem;">
            <option value="citizen">👤 Citizen</option>
            <option value="engineer">👷 Engineer</option>
            <option value="admin">🛡 Admin</option>
        </select>
        <div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:.75rem;padding:.75rem .875rem;margin-bottom:1.25rem;">
            <p style="font-size:.8125rem;color:#92400e;margin:0;">⚠️ Changing role to <strong>Engineer</strong> will give this user access to the engineer panel.</p>
        </div>
        <div id="role-error" style="display:none;color:#dc2626;font-size:.8125rem;margin-bottom:.75rem;"></div>
        <div style="display:flex;gap:.75rem;justify-content:flex-end;">
            <button onclick="closeRoleModal()" class="adm-btn adm-btn-outline">Cancel</button>
            <button onclick="submitRoleChange()" id="role-btn" class="adm-btn adm-btn-primary">Confirm Change</button>
        </div>
    </div>
</div>

<style>
@keyframes spin{to{transform:rotate(360deg)}}
</style>

<script>
const ROLE_CFG = {
    citizen:  {label:'Citizen',  color:'#374151', bg:'#f3f4f6', icon:'👤'},
    engineer: {label:'Engineer', color:'#1e40af', bg:'#dbeafe', icon:'👷'},
    admin:    {label:'Admin',    color:'#6b21a8', bg:'#f3e8ff', icon:'🛡'},
};

let currentPage   = 1;
let totalPages    = 1;
let activeUserId  = null;
let searchTimer   = null;

function esc(s){return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
function fmt(iso){return new Date(iso).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'});}

function debounceSearch() { clearTimeout(searchTimer); searchTimer = setTimeout(applyFilters, 400); }
function applyFilters()   { currentPage = 1; loadUsers(); }
function changePage(dir)  { const n = currentPage + dir; if (n >= 1 && n <= totalPages) { currentPage = n; loadUsers(); } }

async function loadUsers() {
    document.getElementById('users-loading').style.display = 'block';
    document.getElementById('users-table').style.display   = 'none';
    document.getElementById('users-empty').style.display   = 'none';

    const params = { page: currentPage, per_page: 20 };
    const role   = document.getElementById('f-role').value;
    if (role) params.role = role;

    try {
        const res   = await axios.get('/api/v1/admin/users', {params});
        const raw   = res.data;
        // Support both paginated and plain array
        const items = raw.data ?? raw;
        const meta  = raw.meta ?? raw;
        totalPages  = meta.last_page ?? 1;

        // Client-side search filter
        const q = document.getElementById('f-search').value.trim().toLowerCase();
        const filtered = q ? items.filter(u =>
            (u.name??'').toLowerCase().includes(q) ||
            (u.email??'').toLowerCase().includes(q)
        ) : items;

        document.getElementById('users-loading').style.display = 'none';
        document.getElementById('user-count').textContent = `${meta.total ?? items.length} user(s)`;

        if (!filtered.length) {
            document.getElementById('users-empty').style.display = 'block';
            document.getElementById('pagination').style.display  = 'none';
            return;
        }

        document.getElementById('users-table').style.display   = 'block';
        document.getElementById('pagination').style.display     = 'flex';
        document.getElementById('pag-info').textContent         = `Page ${currentPage} of ${totalPages}`;
        document.getElementById('pag-prev').disabled            = currentPage <= 1;
        document.getElementById('pag-next').disabled            = currentPage >= totalPages;

        document.getElementById('users-table').innerHTML = `
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1.5px solid #f3f4f6;background:#fafafa;">
                        ${['User','Email','Phone','Role','Joined','Status','Actions'].map(h =>
                            `<th style="padding:.625rem 1rem;text-align:left;font-size:.72rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap;">${h}</th>`
                        ).join('')}
                    </tr>
                </thead>
                <tbody>
                    ${filtered.map(u => {
                        const r = ROLE_CFG[u.role] ?? {label:u.role, color:'#374151', bg:'#f3f4f6', icon:'👤'};
                        const initial = (u.name??'U')[0].toUpperCase();
                        return `<tr style="border-bottom:1px solid #f9fafb;transition:background .1s;"
                                    onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
                            <td style="padding:.75rem 1rem;">
                                <div style="display:flex;align-items:center;gap:.75rem;">
                                    <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#7c3aed);
                                                display:flex;align-items:center;justify-content:center;
                                                color:#fff;font-size:.875rem;font-weight:700;flex-shrink:0;">${initial}</div>
                                    <div>
                                        <div style="font-size:.875rem;font-weight:600;color:#111827;">${esc(u.name)}</div>
                                        <div style="font-size:.75rem;color:#9ca3af;">#${u.id}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:.75rem 1rem;font-size:.8125rem;color:#374151;">${esc(u.email)}</td>
                            <td style="padding:.75rem 1rem;font-size:.8125rem;color:#6b7280;">${esc(u.phone??'—')}</td>
                            <td style="padding:.75rem 1rem;">
                                <span style="background:${r.bg};color:${r.color};font-size:.72rem;font-weight:700;
                                             padding:.2rem .625rem;border-radius:9999px;white-space:nowrap;">
                                    ${r.icon} ${r.label}
                                </span>
                            </td>
                            <td style="padding:.75rem 1rem;font-size:.8125rem;color:#6b7280;white-space:nowrap;">${fmt(u.created_at)}</td>
                            <td style="padding:.75rem 1rem;">
                                <span style="background:${u.is_active ? '#f0fdf4' : '#fef2f2'};
                                             color:${u.is_active ? '#15803d' : '#dc2626'};
                                             font-size:.72rem;font-weight:700;padding:.2rem .625rem;border-radius:9999px;">
                                    ${u.is_active ? '🟢 Active' : '🔴 Inactive'}
                                </span>
                            </td>
                            <td style="padding:.75rem 1rem;white-space:nowrap;">
                                <button class="adm-btn adm-btn-sm adm-btn-outline"
                                        onclick="openRoleModal(${u.id},'${esc(u.name)}','${esc(u.email)}','${u.role}')">
                                    🔄 Change Role
                                </button>
                            </td>
                        </tr>`;
                    }).join('')}
                </tbody>
            </table>`;

    } catch(e) {
        document.getElementById('users-loading').innerHTML =
            '<div style="padding:2rem;text-align:center;color:#dc2626;">Failed to load users. <button onclick="loadUsers()" style="color:#4f46e5;background:none;border:none;cursor:pointer;font-weight:600;">Retry</button></div>';
    }
}

// ── Role modal ──
function openRoleModal(id, name, email, currentRole) {
    activeUserId = id;
    document.getElementById('modal-user-info').innerHTML =
        `<strong>${esc(name)}</strong> &nbsp;<span style="color:#9ca3af;font-size:.78rem;">${esc(email)}</span>
         <br><span style="font-size:.78rem;color:#6b7280;">Current role: <strong>${currentRole}</strong></span>`;
    document.getElementById('role-select').value = currentRole;
    document.getElementById('role-error').style.display = 'none';
    document.getElementById('role-modal').classList.add('open');
}
function closeRoleModal() {
    document.getElementById('role-modal').classList.remove('open');
    activeUserId = null;
}
document.getElementById('role-modal').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeRoleModal();
});

async function submitRoleChange() {
    const newRole = document.getElementById('role-select').value;
    const btn     = document.getElementById('role-btn');
    btn.disabled  = true; btn.textContent = '⏳ Saving…';
    try {
        const res = await axios.patch(`/api/v1/admin/users/${activeUserId}/role`, {role: newRole});
        closeRoleModal();
        loadUsers();
        // Toast-style notification
        showToast(res.data.message ?? 'Role updated successfully!');
    } catch(e) {
        document.getElementById('role-error').textContent = e.response?.data?.message ?? 'Update failed.';
        document.getElementById('role-error').style.display = 'block';
        btn.disabled = false; btn.textContent = 'Confirm Change';
    }
}

function showToast(msg) {
    const t = document.createElement('div');
    t.style.cssText = 'position:fixed;bottom:2rem;right:2rem;background:#111827;color:#fff;padding:.875rem 1.25rem;border-radius:.875rem;font-size:.875rem;font-weight:600;z-index:1000;box-shadow:0 8px 24px rgba(0,0,0,.2);animation:slideUp .3s ease;';
    t.textContent = '✅ ' + msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3000);
}

document.addEventListener('DOMContentLoaded', loadUsers);
</script>
@endsection

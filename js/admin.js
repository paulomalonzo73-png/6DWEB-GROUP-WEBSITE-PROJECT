/* ============================================
   js/admin.js
   MEMBER 4 — Role-Based Access Control
   Admin dashboard interactions.
   All actions go through php/admin_actions.php
   which enforces admin_check.php server-side.
   ============================================ */

window.addEventListener('DOMContentLoaded', () => {
    loadSummary();
    loadUsers();
});

/* ---- Load summary KPI cards ---- */
async function loadSummary() {
    try {
        const res  = await fetch('php/admin_actions.php?action=summary');
        const data = await res.json();
        if (!data.success) return;

        const s = data.summary;
        document.getElementById('summaryGrid').innerHTML = `
            <div class="summary-card">
                <div class="sum-val" style="color:var(--accent)">${s.total_users}</div>
                <div class="sum-label">Total Users</div>
            </div>
            <div class="summary-card">
                <div class="sum-val" style="color:#ff4d00">${s.inactive_users}</div>
                <div class="sum-label">Inactive Users</div>
            </div>
            <div class="summary-card">
                <div class="sum-val" style="color:#00aaff">${s.total_plans}</div>
                <div class="sum-label">Total Plans Generated</div>
            </div>
            <div class="summary-card">
                <div class="sum-val" style="color:#ff00aa">${s.new_users_30d}</div>
                <div class="sum-label">New Users (30 days)</div>
            </div>
        `;
    } catch (e) {
        document.getElementById('summaryGrid').innerHTML =
            '<div class="summary-card" style="color:var(--danger)">Failed to load summary.</div>';
    }
}

/* ---- Load users table ---- */
async function loadUsers() {
    document.getElementById('usersTableWrap').innerHTML =
        '<div class="admin-loading">Loading users…</div>';

    try {
        const res  = await fetch('php/admin_actions.php?action=list_users');
        const data = await res.json();
        if (!data.success) throw new Error(data.message);

        if (!data.users || data.users.length === 0) {
            document.getElementById('usersTableWrap').innerHTML =
                '<div class="admin-loading">No users found.</div>';
            return;
        }

        const rows = data.users.map(u => {
            const status     = parseInt(u.is_active) === 1
                ? '<span class="status-badge status-active">Active</span>'
                : '<span class="status-badge status-inactive">Inactive</span>';

            const adminBadge = parseInt(u.is_admin) === 1
                ? '<span class="role-badge">ADMIN</span>'
                : '';

            const joined = new Date(u.created_at).toLocaleDateString('en-US',
                { year: 'numeric', month: 'short', day: 'numeric' });

            const actions = buildActions(u);

            return `
                <tr id="user-row-${u.id}">
                    <td class="user-id-cell">#${String(u.id).padStart(4,'0')}</td>
                    <td>
                        <span class="username-cell">${esc(u.username)}</span>
                        ${adminBadge}
                    </td>
                    <td class="email-cell">${esc(u.email)}</td>
                    <td>${status}</td>
                    <td class="plans-cell">${u.plan_count}</td>
                    <td class="joined-cell">${joined}</td>
                    <td class="actions-cell">${actions}</td>
                </tr>
            `;
        }).join('');

        document.getElementById('usersTableWrap').innerHTML = `
            <div class="table-scroll">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Plans</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        `;
    } catch (e) {
        document.getElementById('usersTableWrap').innerHTML =
            `<div class="admin-loading" style="color:var(--danger)">${e.message}</div>`;
    }
}

/* ---- Build action buttons for each user row ---- */
function buildActions(u) {
    const isActive = parseInt(u.is_active) === 1;
    const isAdmin  = parseInt(u.is_admin)  === 1;

    let btns = '';

    if (isActive) {
        btns += `<button class="action-btn btn-deactivate"
                         onclick="deactivateUser(${u.id})">Deactivate</button>`;
    } else {
        btns += `<button class="action-btn btn-reactivate"
                         onclick="reactivateUser(${u.id})">Reactivate</button>`;
    }

    if (isAdmin) {
        btns += `<button class="action-btn btn-demote"
                         onclick="demoteUser(${u.id})">Demote</button>`;
    } else {
        btns += `<button class="action-btn btn-promote"
                         onclick="promoteUser(${u.id})">Promote</button>`;
    }

    btns += `<button class="action-btn btn-delete-user"
                     onclick="deleteUser(${u.id}, '${esc(u.username)}')">Delete</button>`;

    return btns;
}

/* ---- Action: deactivate ---- */
async function deactivateUser(id) {
    if (!confirm('Deactivate this user? They will not be able to log in.')) return;
    await doAction('deactivate', { user_id: id });
}

/* ---- Action: reactivate ---- */
async function reactivateUser(id) {
    await doAction('reactivate', { user_id: id });
}

/* ---- Action: promote to admin ---- */
async function promoteUser(id) {
    if (!confirm('Grant admin access to this user?')) return;
    await doAction('promote', { user_id: id });
}

/* ---- Action: demote from admin ---- */
async function demoteUser(id) {
    if (!confirm('Remove admin role from this user?')) return;
    await doAction('demote', { user_id: id });
}

/* ---- Action: hard delete ---- */
async function deleteUser(id, name) {
    if (!confirm(`Permanently delete user "${name}"? All their data will be erased. This cannot be undone.`))
        return;
    const data = await doAction('delete_user', { user_id: id }, false);
    if (data?.success) {
        document.getElementById(`user-row-${id}`)?.remove();
        loadSummary();
    }
}

/* ---- Generic POST helper ---- */
async function doAction(action, params, reload = true) {
    const fd = new FormData();
    fd.append('action', action);
    for (const [k, v] of Object.entries(params)) fd.append(k, v);

    try {
        const res  = await fetch('php/admin_actions.php', { method: 'POST', body: fd });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success && reload) {
            loadUsers();
            loadSummary();
        }
        return data;
    } catch (e) {
        showToast('Request failed.', 'error');
    }
}

/* ---- Toast ---- */
function showToast(msg, type = 'info') {
    const t = document.getElementById('adminToast');
    t.textContent = msg;
    t.className   = `admin-toast admin-toast-${type}`;
    t.style.display = 'block';
    clearTimeout(t._timer);
    t._timer = setTimeout(() => t.style.display = 'none', 3500);
}

/* ---- HTML escape helper ---- */
function esc(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

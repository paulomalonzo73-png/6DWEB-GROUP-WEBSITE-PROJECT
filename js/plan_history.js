/* ============================================
   js/plan_history.js
   MEMBER 2 — Plan History with Soft Delete + Restore
   All DB operations go through php/plan_history.php
   ============================================ */

let currentTab = 'active';

/* ---- On page load ---- */
window.addEventListener('DOMContentLoaded', () => {
    loadActive();
});

/* ---- Tab switcher ---- */
function switchTab(tab, el) {
    currentTab = tab;
    document.querySelectorAll('.h-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');

    document.getElementById('activePanel').style.display = tab === 'active' ? 'block' : 'none';
    document.getElementById('trashPanel').style.display  = tab === 'trash'  ? 'block' : 'none';

    if (tab === 'active') loadActive();
    else                  loadTrash();
}

/* ---- Load active plans ---- */
async function loadActive() {
    const container = document.getElementById('activePlans');
    container.innerHTML = '<div class="loading-plans">Loading your plans...</div>';

    try {
        const res  = await fetch('php/plan_history.php?action=list');
        const data = await res.json();

        document.getElementById('activeCount').textContent = data.plans?.length ?? 0;

        if (!data.plans || data.plans.length === 0) {
            container.innerHTML = emptyState('No active plans yet. Generate a plan to see it here.');
            return;
        }

        container.innerHTML = data.plans.map(p => planCard(p, false)).join('');
    } catch (e) {
        container.innerHTML = emptyState('Failed to load plans. Check your connection.');
    }
}

/* ---- Load trash ---- */
async function loadTrash() {
    const container = document.getElementById('trashPlans');
    container.innerHTML = '<div class="loading-plans">Loading trash...</div>';

    try {
        const res  = await fetch('php/plan_history.php?action=trash');
        const data = await res.json();

        document.getElementById('trashCount').textContent = data.plans?.length ?? 0;

        if (!data.plans || data.plans.length === 0) {
            container.innerHTML = emptyState('Trash is empty.');
            return;
        }

        container.innerHTML = data.plans.map(p => planCard(p, true)).join('');
    } catch (e) {
        container.innerHTML = emptyState('Failed to load trash.');
    }
}

/* ---- Render a plan card ---- */
function planCard(p, isTrashed) {
    const date     = new Date(p.created_at).toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric' });
    const deleted  = p.deleted_at ? new Date(p.deleted_at).toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric' }) : '';

    const actions = isTrashed
        ? `<button class="plan-btn btn-restore" onclick="restorePlan(${p.id})">↩ Restore</button>
           <button class="plan-btn btn-purge"   onclick="purgePlan(${p.id})">🗑 Purge</button>`
        : `<button class="plan-btn btn-view"    onclick="viewPlan(${p.id})">👁 View</button>
           <button class="plan-btn btn-delete"  onclick="deletePlan(${p.id})">Delete</button>`;

    return `
        <div class="plan-card" id="plan-${p.id}">
            <div class="plan-card-top">
                <div class="plan-id">PLAN #${String(p.id).padStart(4,'0')}</div>
                <div class="plan-date">${date}</div>
            </div>
            <div class="macro-row">
                <div class="macro-item">
                    <div class="macro-val" style="color:var(--accent)">${p.calories}</div>
                    <div class="macro-lbl">kcal</div>
                </div>
                <div class="macro-item">
                    <div class="macro-val" style="color:#ff4d00">${p.protein}g</div>
                    <div class="macro-lbl">protein</div>
                </div>
                <div class="macro-item">
                    <div class="macro-val" style="color:#00aaff">${p.carbs}g</div>
                    <div class="macro-lbl">carbs</div>
                </div>
                <div class="macro-item">
                    <div class="macro-val" style="color:#ff00aa">${p.fats}g</div>
                    <div class="macro-lbl">fats</div>
                </div>
            </div>
            ${deleted ? `<div class="deleted-date">Deleted: ${deleted}</div>` : ''}
            <div class="plan-actions">${actions}</div>
        </div>
    `;
}

/* ---- View a plan (opens modal overlay) ---- */
async function viewPlan(id) {
    showToast('Loading plan...', 'info');
    try {
        const res  = await fetch(`php/plan_history.php?action=view&id=${id}`);
        const data = await res.json();
        if (!data.success) { showToast(data.message, 'error'); return; }

        const p = data.plan;
        // Build a simple modal popup
        let html = `
            <div class="view-modal-overlay" onclick="closeViewModal(event, this)">
              <div class="view-modal">
                <button class="view-close" onclick="this.closest('.view-modal-overlay').remove()">×</button>
                <div class="view-modal-title">PLAN <span>#${String(p.id).padStart(4,'0')}</span></div>
                <div class="view-macros">
                    <div><span style="color:var(--accent)">${p.calories}</span> kcal</div>
                    <div><span style="color:#ff4d00">${p.protein}g</span> protein</div>
                    <div><span style="color:#00aaff">${p.carbs}g</span> carbs</div>
                    <div><span style="color:#ff00aa">${p.fats}g</span> fats</div>
                </div>
                <div class="view-schedule">
        `;
        for (const [day, info] of Object.entries(p.plan_data)) {
            html += `
                <div class="view-day">
                    <div class="view-day-name">${day} — ${info.name}</div>
                    <div class="view-day-focus">${info.focus}</div>
                    <ul class="view-ex-list">
                        ${info.exercises.map(ex =>
                            `<li>${ex.name} <span>${ex.sets} sets × ${ex.reps}</span></li>`
                        ).join('')}
                    </ul>
                </div>
            `;
        }
        html += '</div></div></div>';
        document.body.insertAdjacentHTML('beforeend', html);
    } catch (e) {
        showToast('Failed to load plan.', 'error');
    }
}

function closeViewModal(event, overlay) {
    if (event.target === overlay) overlay.remove();
}

/* ---- Soft delete a plan ---- */
async function deletePlan(id) {
    if (!confirm('Move this plan to trash? You can restore it later.')) return;

    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('plan_id', id);

    const res  = await fetch('php/plan_history.php', { method: 'POST', body: fd });
    const data = await res.json();

    showToast(data.message, data.success ? 'success' : 'error');
    if (data.success) {
        document.getElementById(`plan-${id}`)?.remove();
        document.getElementById('activeCount').textContent =
            parseInt(document.getElementById('activeCount').textContent || '1') - 1;
    }
}

/* ---- Restore a plan ---- */
async function restorePlan(id) {
    const fd = new FormData();
    fd.append('action', 'restore');
    fd.append('plan_id', id);

    const res  = await fetch('php/plan_history.php', { method: 'POST', body: fd });
    const data = await res.json();

    showToast(data.message, data.success ? 'success' : 'error');
    if (data.success) {
        document.getElementById(`plan-${id}`)?.remove();
    }
}

/* ---- Permanently purge a plan ---- */
async function purgePlan(id) {
    if (!confirm('Permanently delete this plan? This cannot be undone.')) return;

    const fd = new FormData();
    fd.append('action', 'purge');
    fd.append('plan_id', id);

    const res  = await fetch('php/plan_history.php', { method: 'POST', body: fd });
    const data = await res.json();

    showToast(data.message, data.success ? 'success' : 'error');
    if (data.success) {
        document.getElementById(`plan-${id}`)?.remove();
        document.getElementById('trashCount').textContent =
            parseInt(document.getElementById('trashCount').textContent || '1') - 1;
    }
}

/* ---- Toast helper ---- */
function showToast(msg, type = 'info') {
    const t = document.getElementById('historyToast');
    t.textContent = msg;
    t.className   = `h-toast h-toast-${type}`;
    t.style.display = 'block';
    clearTimeout(t._timer);
    t._timer = setTimeout(() => t.style.display = 'none', 3000);
}

/* ---- Empty state helper ---- */
function emptyState(msg) {
    return `<div class="empty-history"><p>${msg}</p></div>`;
}

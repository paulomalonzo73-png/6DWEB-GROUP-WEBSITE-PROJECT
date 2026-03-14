/* ============================================
   js/reports.js
   MEMBER 3 — Report Generation
   Fetches from php/reports.php and renders
   all stat sections dynamically.
   ============================================ */

const GOAL_LABELS = {
    bulking:         'Bulking',
    cutting:         'Cutting',
    endurance:       'Endurance',
    general_fitness: 'General Fitness',
};

const GOAL_COLORS = {
    bulking:         '#e8ff00',
    cutting:         '#ff4d00',
    endurance:       '#00aaff',
    general_fitness: '#ff00aa',
};

window.addEventListener('DOMContentLoaded', loadReports);

async function loadReports() {
    try {
        const res  = await fetch('php/reports.php');
        const data = await res.json();

        if (!data.success) throw new Error(data.message);

        document.getElementById('reportsLoading').style.display = 'none';
        document.getElementById('reportsContent').style.display = 'block';

        renderKPIs(data);
        renderMonthCompare(data.monthly);
        renderWeeklyChart(data.weekly);
        renderGoalDist(data.goal_dist);
        renderTypeDist(data.type_dist);
        renderGlobal(data.global);

    } catch (e) {
        document.getElementById('reportsLoading').style.display = 'none';
        document.getElementById('reportsError').style.display   = 'block';
        console.error(e);
    }
}

/* ---- KPI summary cards ---- */
function renderKPIs(data) {
    const p = data.personal;
    const g = data.top_goal;
    const topGoalLabel = g ? (GOAL_LABELS[g.goal] || g.goal) : '—';

    const kpis = [
        { label: 'Total Plans Generated', value: p.total_plans   || 0,   unit: 'plans',    color: 'var(--accent)' },
        { label: 'Avg. Daily Calories',   value: p.avg_calories  || 0,   unit: 'kcal',     color: 'var(--accent)' },
        { label: 'Avg. Daily Protein',    value: p.avg_protein   || 0,   unit: 'g',        color: '#ff4d00' },
        { label: 'Avg. Daily Carbs',      value: p.avg_carbs     || 0,   unit: 'g',        color: '#00aaff' },
        { label: 'Avg. Daily Fats',       value: p.avg_fats      || 0,   unit: 'g',        color: '#ff00aa' },
        { label: 'Highest Calorie Plan',  value: p.max_calories  || 0,   unit: 'kcal',     color: '#ff4d00' },
        { label: 'Lowest Calorie Plan',   value: p.min_calories  || 0,   unit: 'kcal',     color: '#00aaff' },
        { label: 'Favourite Goal',        value: topGoalLabel,           unit: '',         color: GOAL_COLORS[g?.goal] || '#666' },
    ];

    document.getElementById('kpiGrid').innerHTML = kpis.map(k => `
        <div class="kpi-card">
            <div class="kpi-value" style="color:${k.color}">${k.value}<span class="kpi-unit">${k.unit}</span></div>
            <div class="kpi-label">${k.label}</div>
        </div>
    `).join('');
}

/* ---- Month comparison ---- */
function renderMonthCompare(m) {
    const thisMonth = parseInt(m?.this_month) || 0;
    const lastMonth = parseInt(m?.last_month) || 0;
    const diff      = thisMonth - lastMonth;
    const trend     = diff > 0 ? `▲ +${diff} vs last month` : diff < 0 ? `▼ ${diff} vs last month` : '= same as last month';
    const trendColor = diff > 0 ? 'var(--success)' : diff < 0 ? 'var(--danger)' : 'var(--muted)';

    document.getElementById('monthCompare').innerHTML = `
        <div class="month-card">
            <div class="month-num" style="color:var(--accent)">${thisMonth}</div>
            <div class="month-label">Plans this month</div>
        </div>
        <div class="month-vs">VS</div>
        <div class="month-card">
            <div class="month-num" style="color:var(--muted)">${lastMonth}</div>
            <div class="month-label">Plans last month</div>
        </div>
        <div class="month-trend" style="color:${trendColor}">${trend}</div>
    `;
}

/* ---- Weekly bar chart (pure CSS bars) ---- */
function renderWeeklyChart(weekly) {
    if (!weekly || weekly.length === 0) {
        document.getElementById('weeklyChart').innerHTML =
            '<p style="color:var(--muted);padding:20px;">No data in the last 8 weeks.</p>';
        return;
    }

    const max = Math.max(...weekly.map(w => parseInt(w.plan_count)), 1);

    document.getElementById('weeklyChart').innerHTML = `
        <div class="bar-wrap">
            ${weekly.map(w => {
                const pct = Math.round((parseInt(w.plan_count) / max) * 100);
                return `
                    <div class="bar-group">
                        <div class="bar-count">${w.plan_count}</div>
                        <div class="bar-col">
                            <div class="bar-fill" style="height:${pct}%"></div>
                        </div>
                        <div class="bar-lbl">${w.week_label}</div>
                    </div>
                `;
            }).join('')}
        </div>
    `;
}

/* ---- Goal distribution progress bars ---- */
function renderGoalDist(dist) {
    if (!dist || dist.length === 0) {
        document.getElementById('goalDist').innerHTML = '<p style="color:var(--muted)">No data yet.</p>';
        return;
    }

    const total = dist.reduce((sum, d) => sum + parseInt(d.cnt), 0);

    document.getElementById('goalDist').innerHTML = dist.map(d => {
        const pct   = total > 0 ? Math.round((parseInt(d.cnt) / total) * 100) : 0;
        const color = GOAL_COLORS[d.goal] || '#666';
        const label = GOAL_LABELS[d.goal] || d.goal;
        return `
            <div class="dist-row">
                <div class="dist-label">${label}</div>
                <div class="dist-bar-bg">
                    <div class="dist-bar-fill" style="width:${pct}%;background:${color}"></div>
                </div>
                <div class="dist-pct" style="color:${color}">${pct}%</div>
            </div>
        `;
    }).join('');
}

/* ---- Workout type distribution ---- */
function renderTypeDist(dist) {
    if (!dist || dist.length === 0) {
        document.getElementById('typeDist').innerHTML = '<p style="color:var(--muted)">No data yet.</p>';
        return;
    }

    const total  = dist.reduce((sum, d) => sum + parseInt(d.cnt), 0);
    const colors = { gym: '#e8ff00', home: '#00aaff' };

    document.getElementById('typeDist').innerHTML = dist.map(d => {
        const pct   = total > 0 ? Math.round((parseInt(d.cnt) / total) * 100) : 0;
        const color = colors[d.workout_type] || '#666';
        const label = d.workout_type === 'gym' ? '🏋️ Gym' : '🏠 Home';
        return `
            <div class="dist-row">
                <div class="dist-label">${label}</div>
                <div class="dist-bar-bg">
                    <div class="dist-bar-fill" style="width:${pct}%;background:${color}"></div>
                </div>
                <div class="dist-pct" style="color:${color}">${pct}%</div>
            </div>
        `;
    }).join('');
}

/* ---- Global anonymised platform stats ---- */
function renderGlobal(g) {
    document.getElementById('globalGrid').innerHTML = `
        <div class="global-card">
            <div class="global-val">${g.total_users || 0}</div>
            <div class="global-label">Total Users</div>
        </div>
        <div class="global-card">
            <div class="global-val">${g.total_plans || 0}</div>
            <div class="global-label">Total Plans Generated</div>
        </div>
        <div class="global-card">
            <div class="global-val">${g.global_avg_cal || 0}</div>
            <div class="global-label">Platform Avg. Calories</div>
        </div>
    `;
}

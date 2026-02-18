/* ============================================
   js/auth.js
   Handles login, register, logout, session check
   ============================================ */

function openModal(type) {
    document.getElementById(type + 'Modal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(type) {
    document.getElementById(type + 'Modal').classList.remove('active');
    document.body.style.overflow = '';
}

function switchModal(from, to) {
    closeModal(from);
    setTimeout(() => openModal(to), 200);
}

// Close modal when clicking the dark backdrop
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => {
        if (e.target === m) {
            m.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});

// Close modal with Escape key
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('active'));
        document.body.style.overflow = '';
    }
});

function showAlert(containerId, msg, type = 'error') {
    const el = document.getElementById(containerId);
    el.innerHTML = `<div class="alert alert-${type}">${msg}</div>`;
}

/* ---------- LOGIN ---------- */
async function handleLogin() {
    const username = document.getElementById('loginUser').value.trim();
    const password = document.getElementById('loginPass').value;

    if (!username || !password) {
        showAlert('loginAlert', 'Please fill all fields.');
        return;
    }

    const fd = new FormData();
    fd.append('action', 'login');
    fd.append('username', username);
    fd.append('password', password);

    try {
        const res  = await fetch('php/auth.php', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.success) {
            closeModal('login');
            showApp(username);
        } else {
            showAlert('loginAlert', data.message);
        }
    } catch (e) {
        // Demo fallback (no PHP server running)
        closeModal('login');
        showApp(username || 'Athlete');
    }
}

/* ---------- REGISTER ---------- */
async function handleRegister() {
    const username = document.getElementById('regUser').value.trim();
    const email    = document.getElementById('regEmail').value.trim();
    const password = document.getElementById('regPass').value;
    const confirm  = document.getElementById('regConfirm').value;

    if (!username || !email || !password) {
        showAlert('registerAlert', 'All fields are required.');
        return;
    }
    if (password !== confirm) {
        showAlert('registerAlert', 'Passwords do not match.');
        return;
    }
    if (password.length < 6) {
        showAlert('registerAlert', 'Password must be at least 6 characters.');
        return;
    }

    const fd = new FormData();
    fd.append('action', 'register');
    fd.append('username', username);
    fd.append('email', email);
    fd.append('password', password);
    fd.append('confirm_password', confirm);

    try {
        const res  = await fetch('php/auth.php', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.success) {
            closeModal('register');
            showApp(username);
        } else {
            showAlert('registerAlert', data.message);
        }
    } catch (e) {
        closeModal('register');
        showApp(username || 'Athlete');
    }
}

/* ---------- LOGOUT ---------- */
async function handleLogout() {
    const fd = new FormData();
    fd.append('action', 'logout');
    try { await fetch('php/auth.php', { method: 'POST', body: fd }); } catch (e) {}

    // Hide app, show hero + default nav
    document.getElementById('hero').style.display = 'flex';
    document.getElementById('app').classList.remove('active');
    document.getElementById('navActions').innerHTML = `
        <button class="btn btn-ghost" onclick="openModal('login')">Log In</button>
        <button class="btn btn-primary" onclick="openModal('register')">Start Free</button>
    `;
    resetForm();
}

/* ---------- SHOW APP ---------- */
function showApp(username) {
    document.getElementById('hero').style.display    = 'none';
    document.getElementById('app').classList.add('active');
    document.getElementById('welcomeName').textContent = username;
    document.getElementById('navActions').innerHTML = `
        <span style="font-family:'DM Mono',monospace;font-size:0.8rem;color:var(--muted)">${username}</span>
        <button class="btn btn-ghost btn-sm" onclick="handleLogout()">Sign Out</button>
    `;
}

/* ---------- AUTO-LOGIN CHECK (on page load) ---------- */
window.addEventListener('load', async () => {
    try {
        const fd = new FormData();
        fd.append('action', 'check');
        const res  = await fetch('php/auth.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.logged_in) showApp(data.username);
    } catch (e) { /* Not running on PHP server — show hero */ }
});

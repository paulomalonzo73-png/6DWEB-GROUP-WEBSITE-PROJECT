/* ============================================
   js/login.js
   Handles the landing page (index.php):
   - Open / close modals
   - Switch between Login and Register modal
   - Login and Register form submission
   - On success → redirect to app.php
   ============================================ */

/* ---------- MODAL CONTROLS ---------- */

function openModal(type) {
    document.getElementById(type + 'Modal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(type) {
    document.getElementById(type + 'Modal').classList.remove('active');
    document.body.style.overflow = '';
    // Clear any alerts when closing
    const alert = document.getElementById(type === 'login' ? 'loginAlert' : 'registerAlert');
    if (alert) alert.innerHTML = '';
}

function switchModal(from, to) {
    closeModal(from);
    setTimeout(() => openModal(to), 200);
}

// Close modal when clicking the dark backdrop
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay').forEach(m => {
            m.classList.remove('active');
        });
        document.body.style.overflow = '';
    }
});

// Submit on Enter key
document.addEventListener('keydown', function(e) {
    if (e.key !== 'Enter') return;
    if (document.getElementById('loginModal').classList.contains('active'))    handleLogin();
    if (document.getElementById('registerModal').classList.contains('active')) handleRegister();
});


/* ---------- ALERT HELPER ---------- */

function showAlert(containerId, msg, type = 'error') {
    document.getElementById(containerId).innerHTML =
        `<div class="alert alert-${type}">${msg}</div>`;
}


/* ---------- LOGIN ---------- */

async function handleLogin() {
    const username = document.getElementById('loginUser').value.trim();
    const password = document.getElementById('loginPass').value;

    if (!username || !password) {
        showAlert('loginAlert', 'Please fill in all fields.');
        return;
    }

    const fd = new FormData();
    fd.append('action',   'login');
    fd.append('username', username);
    fd.append('password', password);

    try {
        const res  = await fetch('php/auth.php', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.success) {
            window.location.href = 'app.php';
        } else {
            showAlert('loginAlert', data.message);
        }
    } catch (e) {
        showAlert('loginAlert', 'Cannot connect to server. Make sure XAMPP is running.');
    }
}


/* ---------- REGISTER ---------- */

async function handleRegister() {
    const username = document.getElementById('regUser').value.trim();
    const email    = document.getElementById('regEmail').value.trim();
    const password = document.getElementById('regPass').value;
    const confirm  = document.getElementById('regConfirm').value;

    if (!username || !email || !password || !confirm) {
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
    fd.append('action',           'register');
    fd.append('username',         username);
    fd.append('email',            email);
    fd.append('password',         password);
    fd.append('confirm_password', confirm);

    try {
        const res  = await fetch('php/auth.php', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.success) {
            window.location.href = 'app.php';
        } else {
            showAlert('registerAlert', data.message);
        }
    } catch (e) {
        showAlert('registerAlert', 'Cannot connect to server. Make sure XAMPP is running.');
    }
}

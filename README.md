# LIMITLESS — Fitness Plan Generator
### Dynamic Web Application (6DWEB Course Output)

---

## 🌐 Page Flow

```
http://localhost/limitless/
        │
        ▼
   index.php          ← LOGIN PAGE (the first page users see)
        │
        │  login or register success
        ▼
    app.php           ← MAIN APP (profile form + plan results)
        │
        │  Sign Out
        ▼
   index.php          ← back to login
```

---

## 📁 File Structure

```
limitless-v2/
│
├── index.php                   ← LOGIN PAGE only (first page)
├── app.php                     ← MAIN APP (profile form + results, login-protected)
├── database.sql                ← Database schema + sample data
│
├── css/
│   ├── variables.css           ← CSS variables, resets, shared buttons, alerts
│   ├── login.css               ← Login page layout and form styles
│   ├── navbar.css              ← Navbar + welcome bar styles
│   ├── form.css                ← Multi-step form, option cards, step nav
│   └── results.css             ← Nutrition cards, schedule, responsive
│
├── js/
│   ├── login.js                ← Tab switch, login/register submit, redirect
│   ├── form.js                 ← Step navigation, validation, option cards
│   └── plan.js                 ← Plan calculation, fetch to PHP, render results
│
├── php/
│   ├── config.php              ← DB connection + session_start()
│   ├── auth.php                ← Login / register actions (returns JSON)
│   ├── generate_plan.php       ← BMR formula, plan selection, DB save
│   └── logout.php              ← Destroys session, redirects to index.php
│
└── includes/
    ├── form.php                ← 3-step profile form HTML (used in app.php)
    └── results.php             ← Empty result containers (filled by plan.js)
```

---

## 🚀 Setup (XAMPP)

1. Copy `limitless-v2/` to `C:/xampp/htdocs/limitless-v2/`
2. Open phpMyAdmin → Import `database.sql`
3. Visit `http://localhost/limitless-v2/`

### DB Config (`php/config.php`)
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'limitless_db');
```

---

## 👤 Demo Login
| Username  | Password |
|-----------|----------|
| demo_user | password |

---

## 👥 Team Assignment

| Member | Owns |
|--------|------|
| Frontend/Design | `css/login.css`, `css/navbar.css`, `css/variables.css` |
| Auth dev | `php/auth.php`, `php/logout.php`, `js/login.js` |
| Form dev | `js/form.js`, `includes/form.php`, `css/form.css` |
| Plan/Results dev | `js/plan.js`, `php/generate_plan.php`, `css/results.css` |

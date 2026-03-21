<?php


session_start();

if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$error   = '';
$success = '';

// Check if register.php sent a success message
if (isset($_GET['registered'])) {
    $success = 'Account created! You can now sign in.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $users_file = __DIR__ . '/config/users.json';

    if (file_exists($users_file)) {
        $users = json_decode(file_get_contents($users_file), true);

        
        if (isset($users[$username])) {
            $user_data = $users[$username];

            $hash = is_array($user_data)
                ? $user_data['password']   // new format
                : $user_data;              // old format

            if (password_verify($password, $hash)) {
                // ✅ Login success
                $_SESSION['user']       = $username;
                $_SESSION['login_time'] = time();
                // Store role too if it exists (useful for dashboard later)
                $_SESSION['role'] = is_array($user_data)
                    ? ($user_data['role'] ?? 'user')
                    : 'admin';

                header('Location: dashboard.php');
                exit;
            }
        }
    }

    password_verify('dummy', '$2y$10$invaliddummyhashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');
    $error = 'Incorrect username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — LinuxWebUI</title>
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root {
    --glass-bg:     rgba(255,255,255,0.07);
    --glass-border: rgba(255,255,255,0.12);
    --text:         #f5f5f7;
    --text-2:       rgba(245,245,247,0.6);
    --text-3:       rgba(245,245,247,0.35);
    --blue:         #0a84ff;
    --blue-hover:   #409cff;
    --red:          #ff453a;
    --green:        #30d158;
    --radius:       18px;
    --radius-sm:    12px;
}

*,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }

body {
    min-height: 100vh;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    font-size: 15px;
    background: #0a0a0f;
    color: var(--text);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    overflow: hidden;
}

/* Animated mesh background */
body::before {
    content: '';
    position: fixed;
    inset: -50%;
    background:
        radial-gradient(ellipse at 25% 35%, rgba(10,132,255,0.18) 0%, transparent 50%),
        radial-gradient(ellipse at 75% 65%, rgba(94,92,230,0.15) 0%, transparent 50%),
        radial-gradient(ellipse at 60% 20%, rgba(48,209,88,0.06) 0%, transparent 40%),
        linear-gradient(160deg, #0a0a0f 0%, #0d1117 50%, #0a0d14 100%);
    animation: meshMove 20s ease-in-out infinite alternate;
    z-index: 0;
}

@keyframes meshMove {
    0%   { transform: translate(0,0)   scale(1);    }
    50%  { transform: translate(2%,3%) scale(1.02); }
    100% { transform: translate(-2%,1%) scale(1.01); }
}

.page {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 380px;
    animation: enter 0.45s cubic-bezier(0.25,0.46,0.45,0.94) both;
}

@keyframes enter {
    from { opacity:0; transform: translateY(16px) scale(0.98); }
    to   { opacity:1; transform: translateY(0)    scale(1);    }
}

/* Brand */
.brand {
    text-align: center;
    margin-bottom: 1.75rem;
}

.brand-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 60px; height: 60px;
    border-radius: 16px;
    background: linear-gradient(145deg,rgba(255,255,255,0.12),rgba(255,255,255,0.04));
    border: 1px solid rgba(255,255,255,0.15);
    box-shadow: 0 8px 32px rgba(0,0,0,0.4), 0 1px 0 rgba(255,255,255,0.1) inset;
    margin: 0 auto 0.85rem;
    font-size: 1.5rem;
}

.brand h1 {
    font-size: 1.6rem;
    font-weight: 700;
    letter-spacing: -0.03em;
    margin-bottom: 0.2rem;
}

.brand p {
    font-size: 0.82rem;
    color: var(--text-2);
}

/* Glass card */
.card {
    background: var(--glass-bg);
    backdrop-filter: blur(40px) saturate(180%);
    -webkit-backdrop-filter: blur(40px) saturate(180%);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius);
    padding: 2rem;
    box-shadow:
        0 24px 48px rgba(0,0,0,0.5),
        0 1px 0 rgba(255,255,255,0.1) inset;
}

/* Card heading */
.card-heading {
    font-size: 1.1rem;
    font-weight: 600;
    letter-spacing: -0.02em;
    margin-bottom: 1.5rem;
    color: var(--text);
}

/* Alerts */
.alert {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border-radius: var(--radius-sm);
    padding: 0.65rem 0.9rem;
    font-size: 0.82rem;
    margin-bottom: 1.25rem;
}

.alert-error {
    background: rgba(255,69,58,0.1);
    border: 1px solid rgba(255,69,58,0.25);
    color: #ff6b63;
    animation: shake 0.4s cubic-bezier(0.36,0.07,0.19,0.97);
}

.alert-success {
    background: rgba(48,209,88,0.1);
    border: 1px solid rgba(48,209,88,0.25);
    color: #4cd964;
}

@keyframes shake {
    0%,100% { transform:translateX(0);  }
    20%      { transform:translateX(-5px); }
    40%      { transform:translateX(5px);  }
    60%      { transform:translateX(-4px); }
    80%      { transform:translateX(4px);  }
}

/* Fields */
.field { margin-bottom: 1rem; }

.field-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--text-2);
    letter-spacing: 0.04em;
    text-transform: uppercase;
    margin-bottom: 0.4rem;
}

.input-wrap { position: relative; }

.input-wrap .ico {
    position: absolute;
    left: 0.9rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.82rem;
    color: var(--text-3);
    pointer-events: none;
    transition: color 0.2s;
}

.input-wrap:focus-within .ico { color: var(--blue); }

input {
    width: 100%;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: var(--radius-sm);
    padding: 0.75rem 0.9rem 0.75rem 2.4rem;
    font-family: inherit;
    font-size: 0.95rem;
    color: var(--text);
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    -webkit-appearance: none;
    caret-color: var(--blue);
}

input::placeholder { color: var(--text-3); }

input:focus {
    background: rgba(255,255,255,0.11);
    border-color: var(--blue);
    box-shadow: 0 0 0 3px rgba(10,132,255,0.2);
}

.eye-btn {
    position: absolute;
    right: 0.85rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text-3);
    cursor: pointer;
    font-size: 0.82rem;
    padding: 0.2rem;
    transition: color 0.2s;
}
.eye-btn:hover { color: var(--text-2); }

/* Primary button */
.btn-primary {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.8rem;
    margin-top: 1.25rem;
    border: none;
    border-radius: var(--radius-sm);
    background: var(--blue);
    color: #fff;
    font-family: inherit;
    font-size: 0.95rem;
    font-weight: 600;
    letter-spacing: -0.01em;
    cursor: pointer;
    transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
    box-shadow: 0 4px 16px rgba(10,132,255,0.3);
    -webkit-appearance: none;
}

.btn-primary:hover {
    background: var(--blue-hover);
    box-shadow: 0 6px 20px rgba(10,132,255,0.4);
    transform: translateY(-1px);
}

.btn-primary:active {
    transform: scale(0.98);
    box-shadow: 0 2px 8px rgba(10,132,255,0.3);
}

.btn-primary.loading { opacity:0.7; pointer-events:none; }

/* Divider */
.divider {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 1.5rem 0;
}
.divider::before,
.divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: rgba(255,255,255,0.08);
}
.divider span {
    font-size: 0.72rem;
    color: var(--text-3);
    white-space: nowrap;
}

/* Sign up link button */
.btn-secondary {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.75rem;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: var(--radius-sm);
    background: rgba(255,255,255,0.05);
    color: var(--text-2);
    font-family: inherit;
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: background 0.2s, border-color 0.2s, color 0.2s;
}

.btn-secondary:hover {
    background: rgba(255,255,255,0.09);
    border-color: rgba(255,255,255,0.18);
    color: var(--text);
}

/* Footer */
.footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 1.25rem;
    padding-top: 1.25rem;
    border-top: 1px solid rgba(255,255,255,0.06);
    font-size: 0.72rem;
    color: var(--text-3);
}

.status { display:flex; align-items:center; gap:0.35rem; }

.dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: var(--green);
    box-shadow: 0 0 6px var(--green);
    animation: breathe 3s ease-in-out infinite;
}

@keyframes breathe {
    0%,100% { opacity:1; transform:scale(1); }
    50%      { opacity:0.4; transform:scale(0.8); }
}

.note {
    text-align: center;
    margin-top: 1.25rem;
    font-size: 0.72rem;
    color: var(--text-3);
}

@media(max-width:420px) {
    .card  { padding:1.5rem 1.25rem; }
    .brand h1 { font-size:1.4rem; }
}
</style>
</head>
<body>
<div class="page">

    <div class="brand">
        <div class="brand-icon">
            <i class="fa-brands fa-linux"></i>
        </div>
        <h1>LinuxWebUI</h1>
        <p>System Control Panel</p>
    </div>

    <div class="card">
        <div class="card-heading">Sign in to your account</div>

        <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fa-solid fa-circle-xmark"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i>
            <?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">

            <div class="field">
                <label class="field-label">Username</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-user ico"></i>
                    <input
                        type="text"
                        name="username"
                        placeholder="Enter username"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        required
                        autofocus
                        autocomplete="username"
                    >
                </div>
            </div>

            <div class="field">
                <label class="field-label">Password</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-lock ico"></i>
                    <input
                        type="password"
                        name="password"
                        id="pwField"
                        placeholder="Enter password"
                        required
                        autocomplete="current-password"
                    >
                    <button type="button" class="eye-btn" onclick="togglePw()">
                        <i class="fa-solid fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-primary" id="signInBtn">
                <i class="fa-solid fa-arrow-right-to-bracket"></i>
                Sign In
            </button>

        </form>

        <div class="divider"><span>or</span></div>

        <!-- Link to register page -->
        <a href="register.php" class="btn-secondary">
            <i class="fa-solid fa-user-plus"></i>
            Create an account
        </a>

        <div class="footer">
            <span class="status">
                <span class="dot"></span>
                Online
            </span>
            <span>PHP <?= PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION ?></span>
            <span>v1.0</span>
        </div>
    </div>

    <div class="note">Authorized access only &nbsp;·&nbsp; Sessions are logged</div>
</div>

<script>
function togglePw() {
    const f = document.getElementById('pwField');
    const i = document.getElementById('eyeIcon');
    f.type  = f.type === 'password' ? 'text' : 'password';
    i.className = f.type === 'password'
        ? 'fa-solid fa-eye'
        : 'fa-solid fa-eye-slash';
}

document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = document.getElementById('signInBtn');
    btn.classList.add('loading');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Signing in...';
});
</script>
</body>
</html>
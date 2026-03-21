<?php
// register.php

session_start();


if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$error  = '';
$fields = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username  = trim($_POST['username']  ?? '');
    $password  = $_POST['password']  ?? '';
    $password2 = $_POST['password2'] ?? '';

    $fields = ['username' => $username];


    if (empty($username)) {
        $error = 'Username is required.';

    // Only letters, numbers, underscore — no spaces or symbols
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $error = 'Username must be 3-20 characters. Letters, numbers, underscore only.';

    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';

    } elseif ($password !== $password2) {
        $error = 'Passwords do not match.';

    } else {
        $users_file = __DIR__ . '/config/users.json';

        $users = [];
        if (file_exists($users_file)) {
            $users = json_decode(file_get_contents($users_file), true) ?? [];
        }

        
        if (array_key_exists(strtolower($username), array_change_key_case($users))) {
            $error = 'Username already taken. Please choose another.';
        } else {
            
            $hashed = password_hash($password, PASSWORD_BCRYPT);

            // Add new user in new format
            $users[$username] = [
                'password'   => $hashed,
                'created_at' => date('Y-m-d H:i:s'),
                'role'       => 'user'
            ];

            
            $json = json_encode($users, JSON_PRETTY_PRINT);

            
            if (file_put_contents($users_file, $json) === false) {
                $error = 'Could not save user. Check folder permissions.';
            } else {
                header('Location: login.php?registered=1');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Account — LinuxWebUI</title>
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* Same CSS variables and base styles as login.php */
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

body::before {
    content: '';
    position: fixed;
    inset: -50%;
    background:
        radial-gradient(ellipse at 75% 35%, rgba(10,132,255,0.15) 0%, transparent 50%),
        radial-gradient(ellipse at 25% 65%, rgba(94,92,230,0.15) 0%, transparent 50%),
        radial-gradient(ellipse at 50% 90%, rgba(48,209,88,0.07) 0%, transparent 40%),
        linear-gradient(160deg, #0a0a0f 0%, #0d1117 50%, #0a0d14 100%);
    animation: meshMove 20s ease-in-out infinite alternate;
    z-index: 0;
}

@keyframes meshMove {
    0%   { transform: translate(0,0)    scale(1);    }
    50%  { transform: translate(-2%,3%) scale(1.02); }
    100% { transform: translate(2%,-1%) scale(1.01); }
}

.page {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 380px;
    animation: enter 0.45s cubic-bezier(0.25,0.46,0.45,0.94) both;
}

@keyframes enter {
    from { opacity:0; transform:translateY(16px) scale(0.98); }
    to   { opacity:1; transform:translateY(0) scale(1); }
}

.brand { text-align:center; margin-bottom:1.75rem; }

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

.brand h1 { font-size:1.6rem; font-weight:700; letter-spacing:-0.03em; margin-bottom:0.2rem; }
.brand p  { font-size:0.82rem; color:var(--text-2); }

.card {
    background: var(--glass-bg);
    backdrop-filter: blur(40px) saturate(180%);
    -webkit-backdrop-filter: blur(40px) saturate(180%);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius);
    padding: 2rem;
    box-shadow: 0 24px 48px rgba(0,0,0,0.5), 0 1px 0 rgba(255,255,255,0.1) inset;
}

.card-heading { font-size:1.1rem; font-weight:600; letter-spacing:-0.02em; margin-bottom:1.5rem; }

.alert {
    display: flex;
    align-items: flex-start;
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

@keyframes shake {
    0%,100% { transform:translateX(0);  }
    20%      { transform:translateX(-5px); }
    40%      { transform:translateX(5px);  }
    60%      { transform:translateX(-4px); }
    80%      { transform:translateX(4px);  }
}

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

/* Small hint text under field */
.field-hint {
    font-size: 0.68rem;
    color: var(--text-3);
    margin-top: 0.3rem;
    padding-left: 0.2rem;
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

/* Password strength indicator bar */
.strength-bar {
    height: 3px;
    border-radius: 2px;
    background: rgba(255,255,255,0.08);
    margin-top: 0.4rem;
    overflow: hidden;
}

.strength-fill {
    height: 100%;
    border-radius: 2px;
    width: 0%;
    transition: width 0.3s, background 0.3s;
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
    cursor: pointer;
    transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
    box-shadow: 0 4px 16px rgba(10,132,255,0.3);
    -webkit-appearance: none;
}

.btn-primary:hover {
    background: var(--blue-hover);
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(10,132,255,0.4);
}

.btn-primary:active { transform:scale(0.98); }
.btn-primary.loading { opacity:0.7; pointer-events:none; }

.divider {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 1.5rem 0;
}
.divider::before,.divider::after {
    content:''; flex:1;
    height:1px;
    background:rgba(255,255,255,0.08);
}
.divider span { font-size:0.72rem; color:var(--text-3); white-space:nowrap; }

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
    transition: background 0.2s, border-color 0.2s, color 0.2s;
}

.btn-secondary:hover {
    background: rgba(255,255,255,0.09);
    border-color: rgba(255,255,255,0.18);
    color: var(--text);
}

.note { text-align:center; margin-top:1.25rem; font-size:0.72rem; color:var(--text-3); }

@media(max-width:420px) {
    .card { padding:1.5rem 1.25rem; }
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
        <p>Create your account</p>
    </div>

    <div class="card">
        <div class="card-heading">Create an account</div>

        <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fa-solid fa-circle-xmark" style="flex-shrink:0; margin-top:2px;"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="" id="registerForm">

            <!-- Username -->
            <div class="field">
                <label class="field-label">Username</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-user ico"></i>
                    <input
                        type="text"
                        name="username"
                        placeholder="Choose a username"
                        value="<?= htmlspecialchars($fields['username'] ?? '') ?>"
                        required
                        autofocus
                        autocomplete="username"
                        maxlength="20"
                    >
                </div>
                <div class="field-hint">3–20 characters. Letters, numbers, underscore.</div>
            </div>

            <!-- Password -->
            <div class="field">
                <label class="field-label">Password</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-lock ico"></i>
                    <input
                        type="password"
                        name="password"
                        id="pwField"
                        placeholder="Create a password"
                        required
                        autocomplete="new-password"
                        oninput="checkStrength(this.value)"
                    >
                    <button type="button" class="eye-btn" onclick="togglePw('pwField','eye1')">
                        <i class="fa-solid fa-eye" id="eye1"></i>
                    </button>
                </div>
                <!-- Password strength bar -->
                <div class="strength-bar">
                    <div class="strength-fill" id="strengthFill"></div>
                </div>
                <div class="field-hint" id="strengthText">Minimum 6 characters.</div>
            </div>

            <!-- Confirm Password -->
            <div class="field">
                <label class="field-label">Confirm Password</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-lock-open ico"></i>
                    <input
                        type="password"
                        name="password2"
                        id="pw2Field"
                        placeholder="Repeat your password"
                        required
                        autocomplete="new-password"
                    >
                    <button type="button" class="eye-btn" onclick="togglePw('pw2Field','eye2')">
                        <i class="fa-solid fa-eye" id="eye2"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-primary" id="registerBtn">
                <i class="fa-solid fa-user-plus"></i>
                Create Account
            </button>

        </form>

        <div class="divider"><span>already have an account?</span></div>

        <a href="login.php" class="btn-secondary">
            <i class="fa-solid fa-arrow-right-to-bracket"></i>
            Sign In
        </a>

    </div>

    <div class="note">By registering you agree to use this panel responsibly.</div>

</div>

<script>
// Toggle password visibility
function togglePw(fieldId, iconId) {
    const f = document.getElementById(fieldId);
    const i = document.getElementById(iconId);
    f.type  = f.type === 'password' ? 'text' : 'password';
    i.className = f.type === 'password'
        ? 'fa-solid fa-eye'
        : 'fa-solid fa-eye-slash';
}

// Password strength checker
function checkStrength(val) {
    const fill = document.getElementById('strengthFill');
    const text = document.getElementById('strengthText');
    let score = 0;

    // Each condition adds points
    if (val.length >= 6)  score++;   // minimum length
    if (val.length >= 10) score++;   // longer = stronger
    if (/[A-Z]/.test(val)) score++;  // has uppercase
    if (/[0-9]/.test(val)) score++;  // has number
    if (/[^a-zA-Z0-9]/.test(val)) score++; // has symbol

    // Show different color + label based on score
    const levels = [
        { pct:'0%',   color:'transparent',  label:'Minimum 6 characters.' },
        { pct:'25%',  color:'#ff453a',       label:'Weak' },
        { pct:'50%',  color:'#ff9f0a',       label:'Fair' },
        { pct:'75%',  color:'#30d158',       label:'Good' },
        { pct:'100%', color:'#30d158',       label:'Strong ✓' },
    ];

    const level = levels[Math.min(score, 4)];
    fill.style.width      = level.pct;
    fill.style.background = level.color;
    text.textContent      = level.label;
    text.style.color      = level.color === 'transparent'
        ? 'rgba(245,245,247,0.35)'
        : level.color;
}


document.getElementById('registerForm').addEventListener('submit', function() {
    const btn = document.getElementById('registerBtn');
    btn.classList.add('loading');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating...';
});
</script>
</body>
</html>
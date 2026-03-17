<?php
/**
 * Registration Page
 */
require_once __DIR__ . '/session.php';

$u = SITE_URL;

if (isLoggedIn()) {
    redirect('homepage.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $conn = getDB();
        
        // Check if email exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $error = 'An account with this email already exists.';
        }
        $checkStmt->close();
        
        // Check if username exists
        if (!$error) {
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $checkStmt->bind_param("s", $username);
            $checkStmt->execute();
            if ($checkStmt->get_result()->num_rows > 0) {
                $error = 'This username is already taken.';
            }
            $checkStmt->close();
        }
        
        if (!$error) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user';
            
            $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssss", $username, $email, $passwordHash, $role);
            
            if ($stmt->execute()) {
                setFlash('success', 'Account created successfully! Please login.');
                redirect('login.php');
            } else {
                $error = 'Registration failed. Please try again.';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Serenique | Create Account</title>
    <link rel="stylesheet" href="<?php echo $u; ?>/frontend/auth.css">
    <style>
        .login-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: #fdf8f5;
        }
        .login-topbar {
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            box-shadow: 0 1px 6px rgba(0,0,0,.06);
        }
        .login-topbar .brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            color: #d27b5a;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .login-topbar .back-link { font-size: .85rem; color: #888; text-decoration: none; }
        .login-topbar .back-link:hover { color: #d27b5a; }

        .auth-wrap {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .auth-card {
            display: flex;
            width: 100%;
            max-width: 900px;
            min-height: 580px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,.13);
        }

        /* ── Left: form ── */
        .auth-left {
            flex: 1;
            background: #fff;
            padding: 2.5rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .auth-logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            color: #d27b5a;
            margin-bottom: 1.5rem;
        }
        .auth-left h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            color: #2c2c2c;
            margin-bottom: .25rem;
        }
        .auth-left .subtitle { color: #888; font-size: .9rem; margin-bottom: 1.5rem; }

        .form-group { margin-bottom: .9rem; }
        .form-group label {
            display: block;
            font-size: .78rem;
            font-weight: 600;
            color: #555;
            margin-bottom: .35rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .form-group input {
            width: 100%;
            padding: .7rem 1rem;
            border: 1.5px solid #e8e0da;
            border-radius: 10px;
            font-size: .92rem;
            background: #fdf8f5;
            color: #2c2c2c;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }
        .form-group input:focus {
            border-color: #d27b5a;
            box-shadow: 0 0 0 3px rgba(210,123,90,.12);
            background: #fff;
        }
        .form-group input.input-error { border-color: #dc3545; }

        .password-wrap { position: relative; }
        .password-wrap input { padding-right: 2.8rem; }
        .toggle-pw {
            position: absolute; right: .9rem; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            cursor: pointer; color: #aaa; font-size: .9rem; padding: 0;
        }
        .toggle-pw:hover { color: #d27b5a; }

        /* Password strength bar */
        .strength-bar-wrap { margin-top: .4rem; display: flex; gap: 4px; }
        .strength-seg {
            flex: 1; height: 4px; border-radius: 2px;
            background: #e9ecef; transition: background .3s;
        }
        .strength-label { font-size: .72rem; color: #888; margin-top: .2rem; }

        .btn-register {
            width: 100%;
            padding: .85rem;
            background: linear-gradient(135deg, #d27b5a, #b8654a);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: opacity .2s, transform .15s;
            margin-top: .5rem;
            letter-spacing: .02em;
        }
        .btn-register:hover { opacity: .92; transform: translateY(-1px); }

        .divider {
            text-align: center;
            margin: 1.2rem 0 1rem;
            position: relative;
            color: #bbb;
            font-size: .8rem;
        }
        .divider::before, .divider::after {
            content: ''; position: absolute; top: 50%;
            width: 42%; height: 1px; background: #eee;
        }
        .divider::before { left: 0; }
        .divider::after  { right: 0; }

        .login-link { text-align: center; font-size: .875rem; color: #888; }
        .login-link a { color: #d27b5a; font-weight: 700; text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }

        .alert-box {
            padding: .7rem 1rem;
            border-radius: 10px;
            font-size: .875rem;
            margin-bottom: 1rem;
        }
        .alert-box.error { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

        /* ── Right: brand panel ── */
        .auth-right {
            flex: 1;
            background: linear-gradient(160deg, #2c2c2c 0%, #4a2c1a 50%, #d27b5a 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 2.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .auth-right::before {
            content: '';
            position: absolute;
            width: 300px; height: 300px;
            background: rgba(255,255,255,.05);
            border-radius: 50%;
            top: -60px; right: -60px;
        }
        .auth-right::after {
            content: '';
            position: absolute;
            width: 180px; height: 180px;
            background: rgba(255,255,255,.05);
            border-radius: 50%;
            bottom: -30px; left: -30px;
        }
        .auth-right .brand-name {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            color: #fff;
            margin-bottom: .4rem;
            position: relative; z-index: 1;
        }
        .auth-right .brand-tagline {
            color: rgba(255,255,255,.75);
            font-size: .9rem;
            margin-bottom: 2rem;
            position: relative; z-index: 1;
        }
        .steps {
            list-style: none; padding: 0; margin: 0;
            position: relative; z-index: 1;
            text-align: left;
        }
        .steps li {
            display: flex; align-items: flex-start; gap: .75rem;
            color: rgba(255,255,255,.9);
            font-size: .875rem;
            margin-bottom: 1.1rem;
        }
        .step-num {
            width: 28px; height: 28px;
            background: rgba(255,255,255,.18);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .78rem; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }
        .steps li div strong { display: block; color: #fff; margin-bottom: .1rem; }
        .steps li div span { color: rgba(255,255,255,.65); font-size: .8rem; }

        .badge-free {
            margin-top: 1.8rem;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.3);
            color: #fff;
            padding: .5rem 1.2rem;
            border-radius: 50px;
            font-size: .8rem;
            font-weight: 600;
            position: relative; z-index: 1;
        }

        @media (max-width: 768px) {
            .auth-right { display: none; }
            .auth-left  { padding: 2rem 1.5rem; }
            .auth-card  { max-width: 440px; border-radius: 16px; }
        }
    </style>
</head>
<body>
<div class="login-page">

    <div class="login-topbar">
        <a href="<?php echo $u; ?>/homepage.php" class="brand">🌸 Serenique</a>
        <a href="<?php echo $u; ?>/homepage.php" class="back-link">← Back to store</a>
    </div>

    <div class="auth-wrap">
        <div class="auth-card">

            <!-- LEFT: Form -->
            <div class="auth-left">
                <div class="auth-logo">🌸 Serenique</div>

                <h2>Create your account</h2>
                <p class="subtitle">Join thousands of beauty lovers today — it's free</p>

                <?php if ($error): ?>
                    <div class="alert-box error"><?php echo e($error); ?></div>
                <?php endif; ?>

                <form method="POST" id="registerForm">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required
                               value="<?php echo e($_POST['username'] ?? ''); ?>"
                               placeholder="e.g. roseglow99" minlength="3">
                    </div>

                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="email" id="email" name="email" required
                               value="<?php echo e($_POST['email'] ?? ''); ?>"
                               placeholder="your@email.com">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-wrap">
                            <input type="password" id="password" name="password" required
                                   placeholder="At least 6 characters" minlength="6"
                                   oninput="checkStrength(this.value)">
                            <button type="button" class="toggle-pw" onclick="togglePw('password', this)">👁</button>
                        </div>
                        <div class="strength-bar-wrap">
                            <div class="strength-seg" id="seg1"></div>
                            <div class="strength-seg" id="seg2"></div>
                            <div class="strength-seg" id="seg3"></div>
                            <div class="strength-seg" id="seg4"></div>
                        </div>
                        <div class="strength-label" id="strengthLabel"></div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm password</label>
                        <div class="password-wrap">
                            <input type="password" id="confirm_password" name="confirm_password" required
                                   placeholder="Repeat your password">
                            <button type="button" class="toggle-pw" onclick="togglePw('confirm_password', this)">👁</button>
                        </div>
                    </div>

                    <button type="submit" class="btn-register">Create Free Account</button>
                </form>

                <div class="divider">or</div>
                <p class="login-link">Already have an account? <a href="<?php echo $u; ?>/login.php">Sign in</a></p>
            </div>

            <!-- RIGHT: Brand panel -->
            <div class="auth-right">
                <div class="brand-name">Serenique</div>
                <p class="brand-tagline">3 simple steps to start shopping</p>

                <ol class="steps">
                    <li>
                        <span class="step-num">1</span>
                        <div>
                            <strong>Create your account</strong>
                            <span>Takes less than a minute</span>
                        </div>
                    </li>
                    <li>
                        <span class="step-num">2</span>
                        <div>
                            <strong>Browse 100+ products</strong>
                            <span>Skincare, makeup, haircare & more</span>
                        </div>
                    </li>
                    <li>
                        <span class="step-num">3</span>
                        <div>
                            <strong>Enjoy fast delivery</strong>
                            <span>Free on orders over £30</span>
                        </div>
                    </li>
                    <li>
                        <span class="step-num">★</span>
                        <div>
                            <strong>Leave reviews & earn perks</strong>
                            <span>Help the community & get rewarded</span>
                        </div>
                    </li>
                </ol>

                <div class="badge-free">🎁 Buy 3, Get 1 FREE — new members too!</div>
            </div>

        </div>
    </div>
</div>

<script>
function togglePw(id, btn) {
    const input = document.getElementById(id);
    if (input.type === 'password') { input.type = 'text'; btn.textContent = '🙈'; }
    else                           { input.type = 'password'; btn.textContent = '👁'; }
}

function checkStrength(val) {
    const segs   = [1,2,3,4].map(i => document.getElementById('seg' + i));
    const label  = document.getElementById('strengthLabel');
    const colors = ['#dc3545','#fd7e14','#ffc107','#28a745'];
    const labels = ['Too short','Weak','Good','Strong'];

    let score = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val) && /[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    segs.forEach((s, i) => {
        s.style.background = i < score ? colors[Math.min(score - 1, 3)] : '#e9ecef';
    });
    label.textContent = val.length > 0 ? labels[Math.min(score, 3)] : '';
    label.style.color = score > 0 ? colors[Math.min(score - 1, 3)] : '#888';
}
</script>
</body>
</html>


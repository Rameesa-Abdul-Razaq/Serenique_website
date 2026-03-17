<?php
/**
 * Login Page
 */
require_once __DIR__ . '/session.php';

$u = SITE_URL;

if (isLoggedIn()) {
    redirect('homepage.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $conn = getDB();
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            loginUser($user);
            setFlash('success', 'Welcome back, ' . $user['username'] . '!');
            redirect('homepage.php');
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$success = getFlash('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Serenique | Login</title>
    <link rel="stylesheet" href="<?php echo $u; ?>/frontend/auth.css">
    <style>
        /* ── Page layout ── */
        .login-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: #fdf8f5;
        }

        /* ── Slim top bar ── */
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
        .login-topbar .back-link {
            font-size: .85rem;
            color: #888;
            text-decoration: none;
        }
        .login-topbar .back-link:hover { color: #d27b5a; }

        /* ── Split card ── */
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
            min-height: 520px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,.13);
        }

        /* ── Left: form panel ── */
        .auth-left {
            flex: 1;
            background: #fff;
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .auth-logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            color: #d27b5a;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: .4rem;
        }
        .auth-left h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            color: #2c2c2c;
            margin-bottom: .35rem;
        }
        .auth-left .subtitle {
            color: #888;
            font-size: .9rem;
            margin-bottom: 1.8rem;
        }

        .form-group { margin-bottom: 1.1rem; }
        .form-group label {
            display: block;
            font-size: .82rem;
            font-weight: 600;
            color: #555;
            margin-bottom: .4rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .form-group input {
            width: 100%;
            padding: .75rem 1rem;
            border: 1.5px solid #e8e0da;
            border-radius: 10px;
            font-size: .95rem;
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

        .password-wrap { position: relative; }
        .password-wrap input { padding-right: 2.8rem; }
        .toggle-pw {
            position: absolute; right: .9rem; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            cursor: pointer; color: #aaa; font-size: .9rem;
            padding: 0; line-height: 1;
        }
        .toggle-pw:hover { color: #d27b5a; }

        .btn-signin {
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
            margin-top: .4rem;
            letter-spacing: .02em;
        }
        .btn-signin:hover { opacity: .92; transform: translateY(-1px); }
        .btn-signin:active { transform: translateY(0); }

        .divider {
            text-align: center;
            margin: 1.4rem 0 1.1rem;
            position: relative;
            color: #bbb;
            font-size: .8rem;
        }
        .divider::before, .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 42%;
            height: 1px;
            background: #eee;
        }
        .divider::before { left: 0; }
        .divider::after  { right: 0; }

        .register-link {
            text-align: center;
            font-size: .875rem;
            color: #888;
        }
        .register-link a {
            color: #d27b5a;
            font-weight: 700;
            text-decoration: none;
        }
        .register-link a:hover { text-decoration: underline; }

        .alert-box {
            padding: .75rem 1rem;
            border-radius: 10px;
            font-size: .875rem;
            margin-bottom: 1.2rem;
        }
        .alert-box.error   { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .alert-box.success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }

        /* ── Right: brand panel ── */
        .auth-right {
            flex: 1;
            background: linear-gradient(160deg, #d27b5a 0%, #8b4513 60%, #3d1f0e 100%);
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
            width: 320px; height: 320px;
            background: rgba(255,255,255,.06);
            border-radius: 50%;
            top: -80px; right: -80px;
        }
        .auth-right::after {
            content: '';
            position: absolute;
            width: 200px; height: 200px;
            background: rgba(255,255,255,.05);
            border-radius: 50%;
            bottom: -40px; left: -40px;
        }
        .auth-right .brand-name {
            font-family: 'Playfair Display', serif;
            font-size: 2.4rem;
            color: #fff;
            margin-bottom: .5rem;
            position: relative; z-index: 1;
        }
        .auth-right .brand-tagline {
            color: rgba(255,255,255,.8);
            font-size: .95rem;
            margin-bottom: 2.5rem;
            position: relative; z-index: 1;
        }
        .auth-right .perks {
            list-style: none;
            padding: 0; margin: 0;
            position: relative; z-index: 1;
        }
        .auth-right .perks li {
            color: rgba(255,255,255,.9);
            font-size: .875rem;
            margin-bottom: .9rem;
            display: flex;
            align-items: center;
            gap: .65rem;
            text-align: left;
        }
        .auth-right .perks li .perk-icon {
            width: 32px; height: 32px;
            background: rgba(255,255,255,.15);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .95rem;
            flex-shrink: 0;
        }
        .auth-right .badge-offer {
            margin-top: 2rem;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.3);
            color: #fff;
            padding: .5rem 1.2rem;
            border-radius: 50px;
            font-size: .8rem;
            font-weight: 600;
            position: relative; z-index: 1;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .auth-right { display: none; }
            .auth-left  { padding: 2rem 1.5rem; }
            .auth-card  { max-width: 440px; border-radius: 16px; }
        }
    </style>
</head>
<body>
<div class="login-page">

    <!-- Slim top bar -->
    <div class="login-topbar">
        <a href="<?php echo $u; ?>/homepage.php" class="brand">
            🌸 Serenique
        </a>
        <a href="<?php echo $u; ?>/homepage.php" class="back-link">← Back to store</a>
    </div>

    <div class="auth-wrap">
        <div class="auth-card">

            <!-- LEFT: Form -->
            <div class="auth-left">
                <div class="auth-logo">🌸 Serenique</div>

                <h2>Welcome back</h2>
                <p class="subtitle">Sign in to your account to continue</p>

                <?php if ($success): ?>
                    <div class="alert-box success"><?php echo e($success); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert-box error"><?php echo e($error); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="email" id="email" name="email" required
                               value="<?php echo e($_POST['email'] ?? ''); ?>"
                               placeholder="your@email.com">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-wrap">
                            <input type="password" id="password" name="password" required placeholder="••••••••">
                            <button type="button" class="toggle-pw" onclick="togglePassword()" title="Show/hide password">👁</button>
                        </div>
                    </div>

                    <button type="submit" class="btn-signin">Sign In</button>
                </form>

                <div class="divider">or</div>

                <p class="register-link">
                    Don't have an account? <a href="<?php echo $u; ?>/register.php">Create one free</a>
                </p>
            </div>

            <!-- RIGHT: Brand panel -->
            <div class="auth-right">
                <div class="brand-name">Serenique</div>
                <p class="brand-tagline">Your luxury beauty destination</p>

                <ul class="perks">
                    <li>
                        <span class="perk-icon">💄</span>
                        100+ premium beauty products
                    </li>
                    <li>
                        <span class="perk-icon">🚚</span>
                        Free delivery on orders over £30
                    </li>
                    <li>
                        <span class="perk-icon">⭐</span>
                        Earn points with every purchase
                    </li>
                    <li>
                        <span class="perk-icon">🔒</span>
                        Secure checkout, always
                    </li>
                </ul>

                <div class="badge-offer">🎁 Buy 3, Get 1 FREE right now</div>
            </div>

        </div>
    </div>

</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const btn   = document.querySelector('.toggle-pw');
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '🙈';
    } else {
        input.type = 'password';
        btn.textContent = '👁';
    }
}
</script>
</body>
</html>

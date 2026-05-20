<?php
session_start();
include 'db.php';

if (isset($_SESSION['admin']))  { header("Location: ../admin/dashboard.php");  exit(); }
if (isset($_SESSION['staff']))  { header("Location: ../staff/dashboard.php");  exit(); }
if (isset($_SESSION['user']))   { header("Location: ../user/dashboard.php");   exit(); }

if (isset($_POST['login'])) {
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $result = mysqli_query($conn, "SELECT * FROM MEMBER WHERE Email='$email' AND password='$password'");
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['user']    = $email;
        $_SESSION['user_id'] = $row['MemberID'];
        header("Location: ../user/dashboard.php");
        exit();
    }

    $loginError = "Invalid email or password. Please try again.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Member Login — ODM Library</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

:root {
    --navy: #1a3a5c;
    --navy-dark: #0f2540;
    --accent: #0ea5e9;
    --accent-dark: #0284c7;
    --text: #1e293b;
    --muted: #64748b;
    --border: #e2e8f0;
    --bg: #f0f4f8;
}

body {
    font-family: 'Inter', sans-serif;
    background: var(--bg);
    min-height: 100vh;
    display: grid;
    grid-template-columns: 1fr 1fr;
}

.panel-left {
    background: linear-gradient(160deg, var(--navy-dark) 0%, var(--navy) 55%, #1565c0 100%);
    padding: 48px 44px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    color: #fff;
    position: relative;
    overflow: hidden;
}

.panel-left::before {
    content: '';
    position: absolute;
    top: -100px; right: -100px;
    width: 380px; height: 380px;
    border-radius: 50%;
    background: rgba(255,255,255,0.03);
}

.panel-left::after {
    content: '';
    position: absolute;
    bottom: -60px; left: -60px;
    width: 260px; height: 260px;
    border-radius: 50%;
    background: rgba(14,165,233,0.07);
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 56px;
    position: relative;
    z-index: 1;
}

.brand-icon {
    width: 44px; height: 44px;
    background: var(--accent);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
}

.brand h2 { font-size: 17px; font-weight: 700; line-height: 1.2; }
.brand span { display: block; font-size: 12px; color: rgba(255,255,255,0.5); font-weight: 400; }

.panel-left h1 {
    font-size: 2.1rem; font-weight: 800; line-height: 1.2;
    letter-spacing: -0.5px; margin-bottom: 14px;
    position: relative; z-index: 1;
}

.panel-left h1 em { color: var(--accent); font-style: normal; }

.panel-left > p {
    font-size: 15px; color: rgba(255,255,255,0.65);
    line-height: 1.7; max-width: 380px; margin-bottom: 40px;
    position: relative; z-index: 1;
}

.features { display: flex; flex-direction: column; gap: 14px; position: relative; z-index: 1; }

.feature { display: flex; align-items: center; gap: 12px; font-size: 14px; color: rgba(255,255,255,0.8); }

.feature-icon {
    width: 32px; height: 32px;
    background: rgba(255,255,255,0.1);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; flex-shrink: 0;
}

.panel-right {
    background: #fff;
    display: flex; align-items: center; justify-content: center;
    padding: 48px 40px;
}

.login-box { width: 100%; max-width: 400px; }

.login-box h2 { font-size: 26px; font-weight: 800; color: var(--text); margin-bottom: 6px; }
.login-box > p { font-size: 14px; color: var(--muted); margin-bottom: 32px; }

.form-group { margin-bottom: 18px; }

.form-group label {
    display: block; font-size: 13px; font-weight: 600;
    color: var(--text); margin-bottom: 6px;
}

.form-group input {
    width: 100%; padding: 12px 14px;
    border: 1.5px solid var(--border); border-radius: 9px;
    font-size: 14px; font-family: inherit; color: var(--text);
    outline: none; background: #f8fafc;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-group input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(14,165,233,0.1);
    background: #fff;
}

.form-group input::placeholder { color: #b0c4d4; }

.btn-submit {
    background: var(--navy); color: #fff; border: none;
    padding: 13px 20px; border-radius: 9px; font-size: 14px;
    font-weight: 700; width: 100%; cursor: pointer;
    font-family: inherit; transition: all 0.2s; margin-top: 4px;
}

.btn-submit:hover { background: var(--navy-dark); transform: translateY(-1px); box-shadow: 0 4px 16px rgba(26,58,92,0.25); }

.error-msg {
    background: rgba(239,68,68,0.07); border: 1px solid rgba(239,68,68,0.25);
    color: #dc2626; padding: 11px 14px; border-radius: 8px;
    font-size: 13px; font-weight: 500; margin-bottom: 20px;
}

.back-home { display: block; text-align: center; font-size: 13px; color: var(--muted); margin-top: 22px; text-decoration: none; transition: color 0.2s; }
.back-home:hover { color: var(--navy); }

.portal-link {
    display: block; text-align: center; font-size: 11px; color: #c8d5e0;
    margin-top: 36px; padding-top: 18px; border-top: 1px solid var(--border);
    text-decoration: none; letter-spacing: 0.3px; transition: color 0.2s;
}
.portal-link:hover { color: var(--muted); }

@media (max-width: 768px) {
    body { grid-template-columns: 1fr; }
    .panel-left { display: none; }
    .panel-right { padding: 36px 20px; }
}
</style>
</head>
<body>

<div class="panel-left">
    <div class="brand">
        <div class="brand-icon">📚</div>
        <div>
            <h2>ODM</h2>
            <span>Library</span>
        </div>
    </div>
    <h1>Access Your <em>Library</em> Account</h1>
    <p>Browse thousands of books, track your borrowing history, and manage due dates — all in one place.</p>
    <div class="features">
        <div class="feature"><div class="feature-icon">📖</div><span>Browse &amp; borrow from our full catalog</span></div>
        <div class="feature"><div class="feature-icon">📅</div><span>Track due dates and avoid fines</span></div>
        <div class="feature"><div class="feature-icon">📋</div><span>View your complete borrow history</span></div>
        <div class="feature"><div class="feature-icon">⚡</div><span>Fast catalog search by title or author</span></div>
    </div>
</div>

<div class="panel-right">
    <div class="login-box">
        <h2>Welcome Back</h2>
        <p>Sign in to access your library account.</p>

        <?php if (isset($loginError)): ?>
            <div class="error-msg">⚠️ <?php echo $loginError; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="your@email.com" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" name="login" class="btn-submit">Sign In →</button>
        </form>

        <a href="../index.php" class="back-home">← Back to Home</a>
        <a href="../admin/login.php" class="portal-link">Staff &amp; Admin Portal</a>
    </div>
</div>

</body>
</html>

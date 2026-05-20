<?php
session_start();
include '../includes/db.php';

if (isset($_SESSION['admin']))  { header("Location: dashboard.php");           exit(); }
if (isset($_SESSION['staff']))  { header("Location: ../staff/dashboard.php");  exit(); }
if (isset($_SESSION['user']))   { header("Location: ../user/dashboard.php");   exit(); }

if (isset($_POST['login'])) {
    $role     = $_POST['role'];
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    if ($role === 'admin') {
        $result = mysqli_query($conn, "SELECT * FROM admin WHERE username='$email' AND password='$password'");
        if (mysqli_num_rows($result) > 0) {
            $_SESSION['admin'] = $email;
            header("Location: dashboard.php");
            exit();
        }
    }

    if ($role === 'staff') {
        $result = mysqli_query($conn, "SELECT * FROM STAFF_INFO WHERE Email='$email' AND password='$password'");
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $_SESSION['staff']    = $email;
            $_SESSION['staff_id'] = $row['StaffId'];
            header("Location: ../staff/dashboard.php");
            exit();
        }
    }

    $loginError = "Invalid credentials. Please check your details.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff &amp; Admin Portal — ODM Library</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root { --navy: #1a3a5c; --navy-dark: #0f2540; --accent: #0ea5e9; --text: #1e293b; --muted: #64748b; --border: #e2e8f0; --bg: #f0f4f8; }
body { font-family: 'Inter', sans-serif; background: var(--bg); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 32px 16px; }
.portal-wrap { width: 100%; max-width: 460px; }
.portal-header { text-align: center; margin-bottom: 28px; }
.portal-icon { width: 52px; height: 52px; background: var(--navy); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 14px; }
.portal-header h2 { font-size: 22px; font-weight: 800; color: var(--text); margin-bottom: 4px; }
.portal-header p { font-size: 13px; color: var(--muted); }
.portal-card { background: #fff; border-radius: 16px; padding: 32px; box-shadow: 0 8px 32px rgba(0,0,0,0.09); border: 1px solid var(--border); }
.role-toggle { display: grid; grid-template-columns: 1fr 1fr; background: var(--bg); border-radius: 10px; padding: 4px; margin-bottom: 24px; gap: 4px; }
.role-btn { padding: 10px; border: none; border-radius: 7px; font-family: inherit; font-size: 13px; font-weight: 600; cursor: pointer; background: transparent; color: var(--muted); transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 6px; }
.role-btn.active { background: var(--navy); color: #fff; box-shadow: 0 2px 8px rgba(26,58,92,0.3); }
.role-btn:not(.active):hover { background: rgba(26,58,92,0.06); color: var(--navy); }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 6px; }
.form-group input { width: 100%; padding: 11px 14px; border: 1.5px solid var(--border); border-radius: 9px; font-size: 14px; font-family: inherit; color: var(--text); outline: none; background: #f8fafc; transition: border-color 0.2s, box-shadow 0.2s; }
.form-group input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(14,165,233,0.1); background: #fff; }
.form-group input::placeholder { color: #b0c4d4; }
.label-hint { font-size: 11px; color: var(--muted); font-weight: 400; margin-left: 4px; }
.btn-submit { background: var(--navy); color: #fff; border: none; padding: 12px 20px; border-radius: 9px; font-size: 14px; font-weight: 700; width: 100%; cursor: pointer; font-family: inherit; transition: all 0.2s; margin-top: 4px; }
.btn-submit:hover { background: var(--navy-dark); transform: translateY(-1px); }
.error-msg { background: rgba(239,68,68,0.07); border: 1px solid rgba(239,68,68,0.25); color: #dc2626; padding: 10px 14px; border-radius: 8px; font-size: 13px; font-weight: 500; margin-bottom: 18px; }
.back-link { display: block; text-align: center; font-size: 13px; color: var(--muted); margin-top: 20px; text-decoration: none; }
.back-link:hover { color: var(--navy); }
.restricted-note { display: flex; align-items: center; gap: 8px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 10px 14px; font-size: 12px; color: #92400e; margin-bottom: 20px; }
</style>

<script>
function switchRole(role) {
    document.getElementById('roleInput').value = role;
    document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('btn-' + role).classList.add('active');
    const hint = document.getElementById('usernameHint');
    const label = document.getElementById('emailLabel');
    if (role === 'admin') {
        hint.textContent = '(username)';
        label.textContent = 'Username';
        document.getElementById('emailField').type = 'text';
        document.getElementById('emailField').placeholder = 'Enter admin username';
    } else {
        hint.textContent = '(email)';
        label.textContent = 'Email Address';
        document.getElementById('emailField').type = 'email';
        document.getElementById('emailField').placeholder = 'Enter staff email';
    }
}
</script>
</head>
<body>

<div class="portal-wrap">
    <div class="portal-header">
        <div class="portal-icon">🔐</div>
        <h2>Staff &amp; Admin Portal</h2>
        <p>This portal is for library staff and administrators only.</p>
    </div>

    <div class="portal-card">
        <div class="restricted-note">🔒 Restricted access. Accounts are managed by the Administrator.</div>

        <?php if (isset($loginError)): ?>
            <div class="error-msg">⚠️ <?php echo $loginError; ?></div>
        <?php endif; ?>

        <div class="role-toggle">
            <button type="button" class="role-btn active" id="btn-admin" onclick="switchRole('admin')">🛡️ Admin</button>
            <button type="button" class="role-btn" id="btn-staff" onclick="switchRole('staff')">👨‍🏫 Staff</button>
        </div>

        <form method="POST">
            <input type="hidden" name="role" id="roleInput" value="admin">
            <div class="form-group">
                <label id="emailLabel">Username <span class="label-hint" id="usernameHint">(username)</span></label>
                <input type="text" name="email" id="emailField" placeholder="Enter admin username" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" name="login" class="btn-submit">Sign In →</button>
        </form>

        <a href="../includes/login.php" class="back-link">← Member Login</a>
    </div>
</div>

</body>
</html>

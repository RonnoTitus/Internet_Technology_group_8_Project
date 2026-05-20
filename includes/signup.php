<?php
/**
 * Self-registration is DISABLED.
 * All accounts (Admin, Staff, Members) are created by the Administrator.
 * Redirect anyone who visits this page to the login page with a notice.
 */
session_start();

// If already logged in, send to their dashboard
if (isset($_SESSION['admin']))  { header("Location: ../admin/dashboard.php");  exit(); }
if (isset($_SESSION['staff']))  { header("Location: ../staff/dashboard.php");  exit(); }
if (isset($_SESSION['user']))   { header("Location: ../user/dashboard.php");   exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registration Closed — ODM Library</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Inter', sans-serif;
    background: #f0f4f8;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px 16px;
}

.box {
    background: #fff;
    border-radius: 16px;
    padding: 48px 40px;
    max-width: 440px;
    width: 100%;
    text-align: center;
    box-shadow: 0 8px 32px rgba(0,0,0,0.08);
    border: 1px solid #e2e8f0;
}

.icon {
    width: 64px; height: 64px;
    background: #fef2f2;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px;
    margin: 0 auto 20px;
}

h2 {
    font-size: 22px;
    font-weight: 800;
    color: #1a3a5c;
    margin-bottom: 10px;
}

p {
    font-size: 14px;
    color: #64748b;
    line-height: 1.7;
    margin-bottom: 8px;
}

.info-box {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 10px;
    padding: 14px 18px;
    margin: 20px 0 28px;
    font-size: 13px;
    color: #1d4ed8;
    text-align: left;
    line-height: 1.6;
}

.info-box strong { display: block; margin-bottom: 4px; font-size: 14px; }

.btn {
    display: inline-block;
    background: #1a3a5c;
    color: #fff;
    padding: 12px 28px;
    border-radius: 9px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 700;
    transition: all 0.2s;
}

.btn:hover { background: #0f2540; transform: translateY(-1px); }

.link-back {
    display: block;
    margin-top: 16px;
    font-size: 13px;
    color: #64748b;
    text-decoration: none;
}

.link-back:hover { color: #1a3a5c; }
</style>
</head>
<body>

<div class="box">
    <div class="icon">🔒</div>
    <h2>Registration Closed</h2>
    <p>Self-registration is not available for this system.</p>
    <p>All accounts are created and managed by the Library Administrator.</p>

    <div class="info-box">
        <strong>How to get an account:</strong>
        Visit the library office or contact the administrator at
        <a href="mailto:library@kyu.ac.ug" style="color:#1d4ed8;">library@kyu.ac.ug</a>
        to have your account registered.
    </div>

    <a href="login.php" class="btn">Go to Login →</a>
    <a href="../index.php" class="link-back">← Back to Home</a>
</div>

</body>
</html>

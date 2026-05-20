<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';
requireAdmin();

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM contact_messages WHERE id='$id'");
    header("Location: messages.php"); exit();
}

$result = mysqli_query($conn, "SELECT * FROM contact_messages ORDER BY id DESC");
$total  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM contact_messages"))['n'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Messages — ODM LMS</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
<link rel="stylesheet" href="../styles/main.css">
</head>
<body>
<div class="sidebar">
    <div class="sidebar-brand"><span class="sidebar-brand-icon">📖</span><h3>ODM Library</h3></div>
    <nav class="sidebar-nav">
        <a href="dashboard.php"><span class="nav-icon">⊞</span> Dashboard</a>
        <a href="books.php"><span class="nav-icon">📖</span> Books</a>
        <a href="members.php"><span class="nav-icon">👥</span> Members</a>
        <a href="staff.php"><span class="nav-icon">🪪</span> Staff</a>
        <a href="borrow.php"><span class="nav-icon">📥</span> Borrow Records</a>
        <a href="messages.php" class="active"><span class="nav-icon">✉️</span> Messages</a>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-user"><strong><?php echo htmlspecialchars($_SESSION['admin']); ?></strong><span>Admin</span></div>
        <a href="../includes/logout.php" class="logout-btn">&#x2192;</a>
    </div>
</div>
<div class="main-content">
    <div class="page-header">
        <div class="page-header-left">
            <h2>Messages</h2>
            <p><?php echo $total; ?> messages from visitors — Admin only</p>
        </div>
    </div>
    <div class="card">
        <div class="card-title"><span>✉️</span> Contact Messages</div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>#</th><th>Name</th><th>Email</th><th>Message</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php
                $count = 0;
                while ($row = mysqli_fetch_assoc($result)):
                    $count++;
                ?>
                <tr>
                    <td><span class="muted"><?php echo $row['id']; ?></span></td>
                    <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                    <td><span class="muted"><?php echo htmlspecialchars($row['email']); ?></span></td>
                    <td style="max-width:420px;white-space:normal;font-size:13px;color:#374151;"><?php echo htmlspecialchars($row['message']); ?></td>
                    <td style="display:flex;gap:6px;flex-wrap:wrap;">
                        <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>" class="btn btn-primary btn-sm">Reply</a>
                        <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this message?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if ($count === 0): ?>
                <tr>
                    <td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted);">No messages yet.</td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body></html>

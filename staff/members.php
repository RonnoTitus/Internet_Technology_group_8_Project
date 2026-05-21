<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';
requireStaff();

$email    = $_SESSION['staff'];
$sRow     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT CONCAT(FirstName,' ',LastName) AS full_name FROM STAFF_INFO WHERE Email='$email'"));
$staffName = $sRow['full_name'];

// Staff: VIEW ONLY
$search = ''; $having = '';
if (isset($_GET['search']) && $_GET['search'] !== '') {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $having = "HAVING full_name LIKE '%$search%' OR email LIKE '%$search%'";
}

$result = mysqli_query($conn, "
    SELECT m.MemberID,
           CONCAT(m.FirstName,' ',m.LastName) AS full_name,
           m.Email AS email,
           GROUP_CONCAT(DISTINCT mp.PhoneNo SEPARATOR ', ') AS phones
    FROM MEMBER m
    LEFT JOIN MEMBER_PHONE mp ON mp.MemberID = m.MemberID
    GROUP BY m.MemberID, m.FirstName, m.LastName, m.Email
    $having
    ORDER BY m.MemberID DESC
");
$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM MEMBER"))['n'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Members — ODM LMS</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
<link rel="stylesheet" href="../styles/main.css">
</head>
<body>
<div class="sidebar">
    <div class="sidebar-brand"><span class="sidebar-brand-icon">📖</span><h3>ODM Library</h3></div>
    <nav class="sidebar-nav">
        <a href="dashboard.php"><span class="nav-icon">⊞</span> Dashboard</a>
        <a href="books.php"><span class="nav-icon">📖</span> Books</a>
        <a href="members.php" class="active"><span class="nav-icon">👥</span> Members</a>
        <a href="borrow.php"><span class="nav-icon">📥</span> Borrow Records</a>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-user"><strong><?php echo htmlspecialchars($staffName); ?></strong><span>Staff</span></div>
        <a href="../includes/logout.php" class="logout-btn">&#x2192;</a>
    </div>
</div>
<div class="main-content">
    <div class="page-header">
        <div class="page-header-left"><h2>Members</h2><p><?php echo $total; ?> registered members</p></div>
    </div>
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 16px;font-size:13px;color:#1d4ed8;margin-bottom:20px;display:flex;align-items:center;gap:8px;">
        ℹ️ Staff can view member details only. Adding or removing members is restricted to Admin.
    </div>
    <div class="card">
        <div class="card-title"><span>👥</span> All Members</div>
        <div style="padding:16px 16px 0;">
            <form method="GET">
                <div class="search-row">
                    <input type="text" name="search" class="form-control" placeholder="Search name or email..."
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <?php if ($search): ?><a href="members.php" class="btn btn-ghost">Clear</a><?php endif; ?>
                </div>
            </form>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>#</th><th>Full Name</th><th>Email</th><th>Phone(s)</th><th>Active Borrows</th></tr></thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)):
                    $mid = $row['MemberID'];
                    $borrows = mysqli_fetch_assoc(mysqli_query($conn, "
                        SELECT COUNT(*) AS n FROM BORROWING_TRANSACTION bt
                        JOIN BORROWED_BOOKS bb ON bb.BorrowId = bt.BorrowId
                        WHERE bt.MemberId='$mid' AND bb.Status='Borrowed'
                    "))['n'];
                ?>
                <tr>
                    <td><span class="muted"><?php echo $row['MemberID']; ?></span></td>
                    <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                    <td><span class="muted"><?php echo htmlspecialchars($row['email']); ?></span></td>
                    <td><span class="muted"><?php echo htmlspecialchars($row['phones'] ?? '—'); ?></span></td>
                    <td>
                        <?php if ($borrows > 0): ?>
                        <span class="badge badge-borrowed"><?php echo $borrows; ?> active</span>
                        <?php else: ?>
                        <span class="muted">None</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body></html>

<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';
requireAdmin();

$admin = $_SESSION['admin'];

$totalBooks   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM BOOK"))['n'];
$availBooks   = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS n FROM BOOK b
    WHERE NOT EXISTS (
        SELECT 1 FROM BORROWED_BOOKS bb WHERE bb.BookId = b.BookID AND bb.Status = 'Borrowed'
    )
"))['n'];
$totalMembers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM MEMBER"))['n'];
$totalStaff   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM STAFF_INFO"))['n'];
$activeBorrow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM BORROWED_BOOKS WHERE Status='Borrowed'"))['n'];
$totalReturn  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM BORROWED_BOOKS WHERE Status='Returned'"))['n'];
$overdue      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM BORROWED_BOOKS WHERE Status='Borrowed' AND ReturnDate < CURDATE()"))['n'];
$msgs         = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM contact_messages"))['n'];

$recentBorrows = mysqli_query($conn, "
    SELECT bt.BorrowId,
           bk.Title AS title,
           CONCAT(m.FirstName, ' ', m.LastName) AS member,
           bt.Borrowdate AS borrow_date,
           bb.ReturnDate AS return_date,
           bb.Status AS status
    FROM BORROWING_TRANSACTION bt
    JOIN BORROWED_BOOKS bb ON bb.BorrowId = bt.BorrowId
    JOIN BOOK bk ON bk.BookID = bb.BookId
    JOIN MEMBER m ON m.MemberID = bt.MemberId
    ORDER BY bt.BorrowId DESC LIMIT 6
");

$overdueList = mysqli_query($conn, "
    SELECT bk.Title AS title,
           CONCAT(m.FirstName, ' ', m.LastName) AS member,
           bb.ReturnDate AS return_date,
           bt.BorrowId
    FROM BORROWING_TRANSACTION bt
    JOIN BORROWED_BOOKS bb ON bb.BorrowId = bt.BorrowId
    JOIN BOOK bk ON bk.BookID = bb.BookId
    JOIN MEMBER m ON m.MemberID = bt.MemberId
    WHERE bb.Status = 'Borrowed' AND bb.ReturnDate < CURDATE()
    ORDER BY bb.ReturnDate ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — ODM LMS</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
<link rel="stylesheet" href="../styles/main.css">
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <span class="sidebar-brand-icon">📖</span>
        <h3>ODM Library</h3>
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="active"><span class="nav-icon">⊞</span> Dashboard</a>
        <a href="books.php"><span class="nav-icon">📖</span> Books</a>
        <a href="members.php"><span class="nav-icon">👥</span> Members</a>
        <a href="staff.php"><span class="nav-icon">🪪</span> Staff</a>
        <a href="borrow.php"><span class="nav-icon">📥</span> Borrow Records</a>
        <a href="messages.php"><span class="nav-icon">✉️</span> Messages</a>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <strong><?php echo htmlspecialchars($admin); ?></strong>
            <span>Admin</span>
        </div>
        <a href="../includes/logout.php" class="logout-btn" title="Logout">&#x2192;</a>
    </div>
</div>

<div class="main-content">
    <h1 class="page-title">Dashboard Overview</h1>

    <div class="stats-row stats-row-3" style="margin-bottom:16px;">
        <div class="stat-card">
            <div class="stat-card-top">
                <div>
                    <div class="stat-card-label">Total Books</div>
                    <div class="stat-card-number"><?php echo $totalBooks; ?></div>
                    <div class="stat-card-sub"><?php echo $availBooks; ?> available</div>
                </div>
                <div class="stat-icon-wrap">📖</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-top">
                <div>
                    <div class="stat-card-label">Total Members</div>
                    <div class="stat-card-number"><?php echo $totalMembers; ?></div>
                    <div class="stat-card-sub">&nbsp;</div>
                </div>
                <div class="stat-icon-wrap">👥</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-top">
                <div>
                    <div class="stat-card-label">Total Staff</div>
                    <div class="stat-card-number"><?php echo $totalStaff; ?></div>
                    <div class="stat-card-sub">&nbsp;</div>
                </div>
                <div class="stat-icon-wrap">🪪</div>
            </div>
        </div>
    </div>

    <div class="stats-row stats-row-3" style="margin-bottom:28px;">
        <div class="stat-card">
            <div class="stat-card-top">
                <div>
                    <div class="stat-card-label">Active Borrows</div>
                    <div class="stat-card-number"><?php echo $activeBorrow; ?></div>
                    <div class="stat-card-sub"><?php echo $totalReturn; ?> returned all time</div>
                </div>
                <div class="stat-icon-wrap">📥</div>
            </div>
        </div>
        <div class="stat-card <?php echo $overdue > 0 ? 'overdue-card' : ''; ?>">
            <div class="stat-card-top">
                <div>
                    <div class="stat-card-label">Overdue Books</div>
                    <div class="stat-card-number"><?php echo $overdue; ?></div>
                    <div class="stat-card-sub">&nbsp;</div>
                </div>
                <div class="stat-icon-wrap">⚠️</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-top">
                <div>
                    <div class="stat-card-label">Messages</div>
                    <div class="stat-card-number"><?php echo $msgs; ?></div>
                    <div class="stat-card-sub">from visitors</div>
                </div>
                <div class="stat-icon-wrap">✉️</div>
            </div>
        </div>
    </div>

    <div class="two-col">
        <div class="card">
            <div class="card-title"><span>📥</span> Recent Borrows</div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Book</th><th>Member</th><th>Date</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                    <?php while ($r = mysqli_fetch_assoc($recentBorrows)):
                        $today  = date('Y-m-d');
                        $isOver = ($r['status'] == 'Borrowed' && $today > $r['return_date']);
                        if ($r['status'] == 'Returned') {
                            $badge = '<span class="badge badge-returned">Returned</span>';
                        } elseif ($isOver) {
                            $badge = '<span class="badge badge-overdue">Overdue</span>';
                        } else {
                            $badge = '<span class="badge badge-borrowed">Borrowed</span>';
                        }
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($r['title']); ?></strong></td>
                        <td><?php echo htmlspecialchars($r['member']); ?></td>
                        <td><span class="muted"><?php echo date('M j, Y', strtotime($r['borrow_date'])); ?></span></td>
                        <td><?php echo $badge; ?></td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-title overdue-title"><span>⚠️</span> Overdue Alerts</div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Book</th><th>Member</th><th>Due Date</th><th>Fine</th></tr>
                    </thead>
                    <tbody>
                    <?php
                    $hasOverdue = false;
                    while ($r = mysqli_fetch_assoc($overdueList)):
                        $hasOverdue = true;
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($r['title']); ?></strong></td>
                        <td><?php echo htmlspecialchars($r['member']); ?></td>
                        <td class="overdue-date"><?php echo date('M j, Y', strtotime($r['return_date'])); ?></td>
                        <td style="color:var(--red);font-weight:600;">UGX 1,000</td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if (!$hasOverdue): ?>
                    <tr>
                        <td colspan="4" style="text-align:center;padding:28px;color:var(--text-muted);">No overdue books</td>
                    </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>

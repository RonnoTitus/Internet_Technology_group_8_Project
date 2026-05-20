<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';
requireMember();

$email     = $_SESSION['user'];
$data      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT MemberID, CONCAT(FirstName,' ',LastName) AS full_name FROM MEMBER WHERE Email='$email'"));
$name      = $data['full_name'];
$member_id = $data['MemberID'];
if (!isset($_SESSION['user_id'])) { $_SESSION['user_id'] = $member_id; }

$availBooks = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS n FROM BOOK b
    WHERE NOT EXISTS (
        SELECT 1 FROM BORROWED_BOOKS bb WHERE bb.BookId = b.BookID AND bb.Status = 'Borrowed'
    )
"))['n'];

$activeBorrow = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS n FROM BORROWING_TRANSACTION bt
    JOIN BORROWED_BOOKS bb ON bb.BorrowId = bt.BorrowId
    WHERE bt.MemberId='$member_id' AND bb.Status='Borrowed'
"))['n'];

$overdueCount = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS n FROM BORROWING_TRANSACTION bt
    JOIN BORROWED_BOOKS bb ON bb.BorrowId = bt.BorrowId
    WHERE bt.MemberId='$member_id' AND bb.Status='Borrowed' AND bb.ReturnDate < CURDATE()
"))['n'];

$currentBorrows = mysqli_query($conn, "
    SELECT bk.Title AS title,
           IFNULL(GROUP_CONCAT(DISTINCT a.AuthorName SEPARATOR ', '),'—') AS authors,
           bt.Borrowdate AS borrow_date,
           bb.ReturnDate AS return_date,
           bb.Status AS status,
           bt.BorrowId
    FROM BORROWING_TRANSACTION bt
    JOIN BORROWED_BOOKS bb ON bb.BorrowId = bt.BorrowId
    JOIN BOOK bk ON bk.BookID = bb.BookId
    LEFT JOIN BOOK_AUTHOR ba ON ba.BookID = bk.BookID
    LEFT JOIN AUTHOR a ON a.AuthorID = ba.AuthorID
    WHERE bt.MemberId = '$member_id' AND bb.Status = 'Borrowed'
    GROUP BY bt.BorrowId, bk.Title, bt.Borrowdate, bb.ReturnDate, bb.Status
    ORDER BY bt.BorrowId DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Dashboard — ODM LMS</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
<link rel="stylesheet" href="../styles/main.css">
</head>
<body>
<div class="sidebar">
    <div class="sidebar-brand"><span class="sidebar-brand-icon">📖</span><h3>ODM Library</h3></div>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="active"><span class="nav-icon">⊞</span> My Dashboard</a>
        <a href="books.php"><span class="nav-icon">📖</span> Browse Books</a>
        <a href="mybooks.php"><span class="nav-icon">📥</span> My Borrows</a>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-user"><strong><?php echo htmlspecialchars($name); ?></strong><span>Member</span></div>
        <a href="../includes/logout.php" class="logout-btn" title="Logout">&#x2192;</a>
    </div>
</div>
<div class="main-content">
    <h1 class="page-title">Welcome, <?php echo htmlspecialchars($name); ?></h1>

    <div class="stats-row stats-row-3" style="margin-bottom:28px;">
        <div class="stat-card">
            <div class="stat-card-top">
                <div>
                    <div class="stat-card-label">Available Books</div>
                    <div class="stat-card-number"><?php echo $availBooks; ?></div>
                    <div class="stat-card-sub">Ready to borrow</div>
                </div>
                <div class="stat-icon-wrap">📖</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-top">
                <div>
                    <div class="stat-card-label">Active Borrows</div>
                    <div class="stat-card-number"><?php echo $activeBorrow; ?></div>
                    <div class="stat-card-sub">&nbsp;</div>
                </div>
                <div class="stat-icon-wrap">📥</div>
            </div>
        </div>
        <div class="stat-card <?php echo $overdueCount > 0 ? 'overdue-card' : ''; ?>">
            <div class="stat-card-top">
                <div>
                    <div class="stat-card-label">Overdue</div>
                    <div class="stat-card-number"><?php echo $overdueCount; ?></div>
                    <div class="stat-card-sub">&nbsp;</div>
                </div>
                <div class="stat-icon-wrap">⚠️</div>
            </div>
        </div>
    </div>

    <?php if ($overdueCount > 0): ?>
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px 18px;display:flex;align-items:center;gap:10px;font-size:14px;color:#dc2626;font-weight:500;margin-bottom:20px;">
        ⚠️ You have <strong style="margin:0 4px;"><?php echo $overdueCount; ?> overdue book<?php echo $overdueCount > 1 ? 's' : ''; ?></strong>. Fine: UGX 1,000 per book. Please return them immediately.
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-title"><span>🕐</span> Current Borrows</div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Book</th><th>Author(s)</th><th>Borrow Date</th><th>Due Date</th><th>Status</th></tr></thead>
                <tbody>
                <?php
                $count = 0;
                while ($r = mysqli_fetch_assoc($currentBorrows)):
                    $count++;
                    $today  = date('Y-m-d');
                    $isOver = ($today > $r['return_date']);
                    $badge  = $isOver
                        ? '<span class="badge badge-overdue">Overdue</span>'
                        : '<span class="badge badge-active">Active</span>';
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($r['title']); ?></strong></td>
                    <td><span class="muted"><?php echo htmlspecialchars($r['authors']); ?></span></td>
                    <td><?php echo date('M j, Y', strtotime($r['borrow_date'])); ?></td>
                    <td class="<?php echo $isOver ? 'overdue-date' : ''; ?>">
                        <?php echo date('M j, Y', strtotime($r['return_date'])); ?>
                    </td>
                    <td><?php echo $badge; ?></td>
                </tr>
                <?php endwhile; ?>
                <?php if ($count === 0): ?>
                <tr>
                    <td colspan="5" style="text-align:center;padding:32px;color:var(--text-muted);">
                        No active borrows. <a href="books.php" style="color:var(--navy);font-weight:600;">Browse books →</a>
                    </td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>

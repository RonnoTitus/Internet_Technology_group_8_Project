<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';
requireMember();

$email     = $_SESSION['user'];
$data      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT MemberID, CONCAT(FirstName,' ',LastName) AS full_name FROM MEMBER WHERE Email='$email'"));
$name      = $data['full_name'];
$member_id = $data['MemberID'];

$result = mysqli_query($conn, "
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
    WHERE bt.MemberId = '$member_id'
    GROUP BY bt.BorrowId, bk.Title, bt.Borrowdate, bb.ReturnDate, bb.Status
    ORDER BY bt.BorrowId DESC
");

$total    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM BORROWING_TRANSACTION WHERE MemberId='$member_id'"))['n'];
$active   = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS n FROM BORROWING_TRANSACTION bt
    JOIN BORROWED_BOOKS bb ON bb.BorrowId = bt.BorrowId
    WHERE bt.MemberId='$member_id' AND bb.Status='Borrowed'
"))['n'];
$returned = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS n FROM BORROWING_TRANSACTION bt
    JOIN BORROWED_BOOKS bb ON bb.BorrowId = bt.BorrowId
    WHERE bt.MemberId='$member_id' AND bb.Status='Returned'
"))['n'];
$overdue  = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS n FROM BORROWING_TRANSACTION bt
    JOIN BORROWED_BOOKS bb ON bb.BorrowId = bt.BorrowId
    WHERE bt.MemberId='$member_id' AND bb.Status='Borrowed' AND bb.ReturnDate < CURDATE()
"))['n'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Borrows — ODM LMS</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
<link rel="stylesheet" href="../styles/main.css">
</head>
<body>
<div class="sidebar">
    <div class="sidebar-brand"><span class="sidebar-brand-icon">📖</span><h3>ODM Library</h3></div>
    <nav class="sidebar-nav">
        <a href="dashboard.php"><span class="nav-icon">⊞</span> My Dashboard</a>
        <a href="books.php"><span class="nav-icon">📖</span> Browse Books</a>
        <a href="mybooks.php" class="active"><span class="nav-icon">📥</span> My Borrows</a>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-user"><strong><?php echo htmlspecialchars($name); ?></strong><span>Member</span></div>
        <a href="../includes/logout.php" class="logout-btn">&#x2192;</a>
    </div>
</div>
<div class="main-content">
    <div class="page-header">
        <div class="page-header-left"><h2>My Borrows</h2><p>Your complete borrowing history</p></div>
        <a href="books.php" class="btn btn-primary">Browse Books</a>
    </div>

    <div class="stats-row stats-row-4" style="margin-bottom:24px;">
        <div class="stat-card">
            <div class="stat-card-top">
                <div><div class="stat-card-label">Total Borrows</div><div class="stat-card-number"><?php echo $total; ?></div></div>
                <div class="stat-icon-wrap">📋</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-top">
                <div><div class="stat-card-label">Active</div><div class="stat-card-number"><?php echo $active; ?></div></div>
                <div class="stat-icon-wrap">📤</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-top">
                <div><div class="stat-card-label">Returned</div><div class="stat-card-number"><?php echo $returned; ?></div></div>
                <div class="stat-icon-wrap">✅</div>
            </div>
        </div>
        <div class="stat-card <?php echo $overdue > 0 ? 'overdue-card' : ''; ?>">
            <div class="stat-card-top">
                <div><div class="stat-card-label">Overdue</div><div class="stat-card-number"><?php echo $overdue; ?></div></div>
                <div class="stat-icon-wrap">⚠️</div>
            </div>
        </div>
    </div>

    <?php if ($overdue > 0): ?>
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px 18px;display:flex;align-items:center;gap:10px;font-size:14px;color:#dc2626;font-weight:500;margin-bottom:20px;">
        ⚠️ You have <strong style="margin:0 4px;"><?php echo $overdue; ?> overdue book<?php echo $overdue > 1 ? 's' : ''; ?></strong>. Fine: UGX 1,000 per book. Please return them immediately.
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-title"><span>📋</span> Borrow History</div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Book</th><th>Author(s)</th><th>Borrow Date</th><th>Due Date</th><th>Status</th><th>Fine</th></tr></thead>
                <tbody>
                <?php
                $count = 0;
                while ($r = mysqli_fetch_assoc($result)):
                    $count++;
                    $today  = date('Y-m-d');
                    $isOver = ($r['status'] == 'Borrowed' && $today > $r['return_date']);
                    $fine   = $isOver ? 1000 : 0;
                    if ($r['status'] == 'Returned')  $badge = '<span class="badge badge-returned">Returned</span>';
                    elseif ($isOver)                  $badge = '<span class="badge badge-overdue">Overdue</span>';
                    else                              $badge = '<span class="badge badge-active">Active</span>';
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($r['title']); ?></strong></td>
                    <td><span class="muted"><?php echo htmlspecialchars($r['authors']); ?></span></td>
                    <td><span class="muted"><?php echo date('M j, Y', strtotime($r['borrow_date'])); ?></span></td>
                    <td class="<?php echo $isOver ? 'overdue-date' : ''; ?>">
                        <?php echo date('M j, Y', strtotime($r['return_date'])); ?>
                    </td>
                    <td><?php echo $badge; ?></td>
                    <td><?php echo $fine > 0 ? '<span style="color:var(--red);font-weight:600;">UGX 1,000</span>' : '<span class="muted">—</span>'; ?></td>
                </tr>
                <?php endwhile; ?>
                <?php if ($count === 0): ?>
                <tr>
                    <td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted);">
                        No borrow history yet. <a href="books.php" style="color:var(--navy);font-weight:600;">Browse books →</a>
                    </td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body></html>

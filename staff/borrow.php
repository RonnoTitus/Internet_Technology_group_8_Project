<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';
requireStaff();

$email    = $_SESSION['staff'];
$sRow     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT StaffId, CONCAT(FirstName,' ',LastName) AS full_name FROM STAFF_INFO WHERE Email='$email'"));
$staffName = $sRow['full_name'];
$staffId   = $sRow['StaffId'];
if (!isset($_SESSION['staff_id'])) { $_SESSION['staff_id'] = $staffId; }

$issueMsg = '';
if (isset($_POST['issue'])) {
    $book_id   = intval($_POST['book_id']);
    $member_id = intval($_POST['member_id']);
    $days      = intval($_POST['days']) ?: 7;
    $today     = date('Y-m-d');
    $returnDate = date('Y-m-d', strtotime("+$days days"));

    // Check book is not already borrowed
    $avail = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT COUNT(*) AS n FROM BORROWED_BOOKS WHERE BookId='$book_id' AND Status='Borrowed'
    "))['n'];

    if ($avail == 0) {
        mysqli_query($conn, "INSERT INTO BORROWING_TRANSACTION(MemberId,StaffId,Borrowdate)
                             VALUES('$member_id','$staffId','$today')");
        $borrowId = mysqli_insert_id($conn);
        mysqli_query($conn, "INSERT INTO BORROWED_BOOKS(BookId,ReturnDate,Status,BorrowId)
                             VALUES('$book_id','$returnDate','Borrowed','$borrowId')");
        $issueMsg = 'success';
    } else {
        $issueMsg = 'unavailable';
    }
}

$stats = [
    'total'    => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM BORROWED_BOOKS"))['n'],
    'borrowed' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM BORROWED_BOOKS WHERE Status='Borrowed'"))['n'],
    'returned' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM BORROWED_BOOKS WHERE Status='Returned'"))['n'],
    'overdue'  => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM BORROWED_BOOKS WHERE Status='Borrowed' AND ReturnDate < CURDATE()"))['n'],
];

$search = ''; $extra = '';
if (isset($_GET['search']) && $_GET['search'] !== '') {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $extra  = "AND (bk.Title LIKE '%$search%' OR CONCAT(m.FirstName,' ',m.LastName) LIKE '%$search%')";
}

$records = mysqli_query($conn, "
    SELECT bt.BorrowId,
           bk.Title AS title,
           CONCAT(m.FirstName,' ',m.LastName) AS member,
           bt.Borrowdate AS borrow_date,
           bb.ReturnDate AS return_date,
           bb.Status AS status
    FROM BORROWING_TRANSACTION bt
    JOIN BORROWED_BOOKS bb ON bb.BorrowId = bt.BorrowId
    JOIN BOOK bk ON bk.BookID = bb.BookId
    JOIN MEMBER m ON m.MemberID = bt.MemberId
    WHERE 1=1 $extra
    ORDER BY bt.BorrowId DESC
");

// Available books
$books = mysqli_query($conn, "
    SELECT b.BookID, b.Title,
           IFNULL(GROUP_CONCAT(DISTINCT a.AuthorName SEPARATOR ', '),'') AS authors
    FROM BOOK b
    LEFT JOIN BOOK_AUTHOR ba ON ba.BookID = b.BookID
    LEFT JOIN AUTHOR a ON a.AuthorID = ba.AuthorID
    WHERE NOT EXISTS (
        SELECT 1 FROM BORROWED_BOOKS bb WHERE bb.BookId = b.BookID AND bb.Status = 'Borrowed'
    )
    GROUP BY b.BookID, b.Title
    ORDER BY b.Title ASC
");

$members = mysqli_query($conn, "
    SELECT MemberID, CONCAT(FirstName,' ',LastName) AS full_name
    FROM MEMBER ORDER BY FirstName ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Borrow Records — ODM LMS</title>
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
        <a href="borrow.php" class="active"><span class="nav-icon">📥</span> Borrow Records</a>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-user"><strong><?php echo htmlspecialchars($staffName); ?></strong><span>Staff</span></div>
        <a href="../includes/logout.php" class="logout-btn">&#x2192;</a>
    </div>
</div>
<div class="main-content">
    <div class="page-header">
        <div class="page-header-left"><h2>Borrow Records</h2><p>Issue books and manage returns</p></div>
    </div>

    <div class="stats-row stats-row-4" style="margin-bottom:24px;">
        <div class="stat-card">
            <div class="stat-card-top">
                <div><div class="stat-card-label">Total Records</div><div class="stat-card-number"><?php echo $stats['total']; ?></div></div>
                <div class="stat-icon-wrap">📋</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-top">
                <div><div class="stat-card-label">Active Borrows</div><div class="stat-card-number"><?php echo $stats['borrowed']; ?></div></div>
                <div class="stat-icon-wrap">📤</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-top">
                <div><div class="stat-card-label">Returned</div><div class="stat-card-number"><?php echo $stats['returned']; ?></div></div>
                <div class="stat-icon-wrap">✅</div>
            </div>
        </div>
        <div class="stat-card <?php echo $stats['overdue'] > 0 ? 'overdue-card' : ''; ?>">
            <div class="stat-card-top">
                <div><div class="stat-card-label">Overdue</div><div class="stat-card-number"><?php echo $stats['overdue']; ?></div></div>
                <div class="stat-icon-wrap">⚠️</div>
            </div>
        </div>
    </div>

    <div class="split-layout" style="align-items:start;">

        <div class="form-section">
            <div class="form-section-title">📤 Issue a Book</div>
            <?php if ($issueMsg === 'success'): ?>
            <div class="alert alert-success">Book issued successfully!</div>
            <?php elseif ($issueMsg === 'unavailable'): ?>
            <div class="alert alert-error">This book is currently borrowed.</div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Select Book</label>
                    <select name="book_id" class="form-control" required>
                        <option value="">— Choose a book —</option>
                        <?php
                        $bArr = [];
                        while ($b = mysqli_fetch_assoc($books)) { $bArr[] = $b; }
                        foreach ($bArr as $b):
                        ?>
                        <option value="<?php echo $b['BookID']; ?>">
                            <?php echo htmlspecialchars($b['Title']); ?>
                            <?php if ($b['authors']): ?> — <?php echo htmlspecialchars($b['authors']); ?><?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Select Member</label>
                    <select name="member_id" class="form-control" required>
                        <option value="">— Choose a member —</option>
                        <?php while ($m = mysqli_fetch_assoc($members)): ?>
                        <option value="<?php echo $m['MemberID']; ?>"><?php echo htmlspecialchars($m['full_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Loan Duration (Days)</label>
                    <input type="number" name="days" class="form-control" value="7" min="1" max="90">
                </div>
                <button name="issue" class="btn btn-primary" style="width:100%;">Issue Book</button>
            </form>
        </div>

        <div class="card">
            <div class="card-title"><span>📥</span> All Borrow Records</div>
            <div style="padding:16px 16px 0;">
                <form method="GET">
                    <div class="search-row">
                        <input type="text" name="search" class="form-control" placeholder="Search book or member..."
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <?php if ($search): ?><a href="borrow.php" class="btn btn-ghost">Clear</a><?php endif; ?>
                    </div>
                </form>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>#</th><th>Book</th><th>Member</th><th>Borrow Date</th><th>Due Date</th><th>Status</th><th>Fine</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                    <?php while ($r = mysqli_fetch_assoc($records)):
                        $today  = date('Y-m-d');
                        $isOver = ($r['status'] == 'Borrowed' && $today > $r['return_date']);
                        $fine   = $isOver ? 1000 : 0;
                        if ($r['status'] == 'Returned')  $badge = '<span class="badge badge-returned">Returned</span>';
                        elseif ($isOver)                  $badge = '<span class="badge badge-overdue">Overdue</span>';
                        else                              $badge = '<span class="badge badge-borrowed">Borrowed</span>';
                    ?>
                    <tr>
                        <td><span class="muted"><?php echo $r['BorrowId']; ?></span></td>
                        <td><strong><?php echo htmlspecialchars($r['title']); ?></strong></td>
                        <td><?php echo htmlspecialchars($r['member']); ?></td>
                        <td><span class="muted"><?php echo date('M j, Y', strtotime($r['borrow_date'])); ?></span></td>
                        <td class="<?php echo $isOver ? 'overdue-date' : 'muted'; ?>">
                            <?php echo date('M j, Y', strtotime($r['return_date'])); ?>
                        </td>
                        <td><?php echo $badge; ?></td>
                        <td><?php echo $fine > 0 ? '<span style="color:var(--red);font-weight:600;">UGX 1,000</span>' : '<span class="muted">—</span>'; ?></td>
                        <td>
                            <?php if ($r['status'] == 'Borrowed'): ?>
                            <a href="return.php?id=<?php echo $r['BorrowId']; ?>" class="btn btn-success btn-sm"
                               onclick="return confirm('Mark as returned?')">Return</a>
                            <?php else: ?>
                            <span class="muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
</body></html>

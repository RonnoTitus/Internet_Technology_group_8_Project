<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';
requireMember();

$email = $_SESSION['user'];
$data  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT MemberID, CONCAT(FirstName,' ',LastName) AS full_name FROM MEMBER WHERE Email='$email'"));
$name  = $data['full_name'];

$search = ''; $having = '';
if (isset($_GET['search']) && $_GET['search'] !== '') {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $having = "HAVING title LIKE '%$search%' OR authors LIKE '%$search%' OR publisher LIKE '%$search%'";
}

$result = mysqli_query($conn, "
    SELECT b.BookID,
           b.Title AS title,
           IFNULL(GROUP_CONCAT(DISTINCT a.AuthorName ORDER BY a.AuthorName SEPARATOR ', '),'—') AS authors,
           IFNULL(MAX(p.PublisherName),'—') AS publisher
    FROM BOOK b
    LEFT JOIN BOOK_AUTHOR ba ON ba.BookID = b.BookID
    LEFT JOIN AUTHOR a ON a.AuthorID = ba.AuthorID
    LEFT JOIN BOOK_PUBLISHING bp ON bp.BookID = b.BookID
    LEFT JOIN PUBLISHER p ON p.PublisherID = bp.PublisherID
    WHERE NOT EXISTS (
        SELECT 1 FROM BORROWED_BOOKS bb WHERE bb.BookId = b.BookID AND bb.Status = 'Borrowed'
    )
    GROUP BY b.BookID, b.Title
    $having
    ORDER BY b.Title ASC
");

$total = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS n FROM BOOK b
    WHERE NOT EXISTS (
        SELECT 1 FROM BORROWED_BOOKS bb WHERE bb.BookId = b.BookID AND bb.Status = 'Borrowed'
    )
"))['n'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Browse Books — ODM LMS</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
<link rel="stylesheet" href="../styles/main.css">
</head>
<body>
<div class="sidebar">
    <div class="sidebar-brand"><span class="sidebar-brand-icon">📖</span><h3>ODM Library</h3></div>
    <nav class="sidebar-nav">
        <a href="dashboard.php"><span class="nav-icon">⊞</span> My Dashboard</a>
        <a href="books.php" class="active"><span class="nav-icon">📖</span> Browse Books</a>
        <a href="mybooks.php"><span class="nav-icon">📥</span> My Borrows</a>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-user"><strong><?php echo htmlspecialchars($name); ?></strong><span>Member</span></div>
        <a href="../includes/logout.php" class="logout-btn">&#x2192;</a>
    </div>
</div>
<div class="main-content">
    <div class="page-header">
        <div class="page-header-left">
            <h2>Browse Books</h2>
            <p><?php echo $total; ?> books available to borrow</p>
        </div>
    </div>
    <div class="card">
        <div class="card-title"><span>📖</span> Available Books</div>
        <div style="padding:16px 16px 0;">
            <form method="GET">
                <div class="search-row">
                    <input type="text" name="search" class="form-control" placeholder="Search by title, author, or publisher..."
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <?php if ($search): ?><a href="books.php" class="btn btn-ghost">Clear</a><?php endif; ?>
                </div>
            </form>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Book Title</th><th>Author(s)</th><th>Publisher</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                <?php
                $count = 0;
                while ($row = mysqli_fetch_assoc($result)):
                    $count++;
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['authors']); ?></td>
                    <td><span class="muted"><?php echo htmlspecialchars($row['publisher']); ?></span></td>
                    <td><span class="badge badge-returned">Available</span></td>
                    <td>
                        <a href="borrow.php?id=<?php echo $row['BookID']; ?>"
                           class="btn btn-primary btn-sm"
                           onclick="return confirm('Borrow &quot;<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>&quot;? Due in 7 days.')">
                            Borrow
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if ($count === 0): ?>
                <tr>
                    <td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted);">
                        <?php echo $search ? 'No books match your search.' : 'No books are currently available.'; ?>
                    </td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body></html>

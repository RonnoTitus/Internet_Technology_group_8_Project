<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';
requireStaff();

$email    = $_SESSION['staff'];
$sRow     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT CONCAT(FirstName,' ',LastName) AS full_name FROM STAFF_INFO WHERE Email='$email'"));
$staffName = $sRow['full_name'];

// ----- EDIT LOAD -----
$edit = false; $editData = [];
if (isset($_GET['edit'])) {
    $edit = true;
    $id   = intval($_GET['edit']);
    $editData = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT b.BookID, b.Title,
               IFNULL(GROUP_CONCAT(DISTINCT a.AuthorName ORDER BY a.AuthorName SEPARATOR ', '),'') AS authors,
               IFNULL(MAX(p.PublisherName),'') AS publisher,
               IFNULL(MAX(bp.Year),'') AS pub_year
        FROM BOOK b
        LEFT JOIN BOOK_AUTHOR ba ON ba.BookID = b.BookID
        LEFT JOIN AUTHOR a ON a.AuthorID = ba.AuthorID
        LEFT JOIN BOOK_PUBLISHING bp ON bp.BookID = b.BookID
        LEFT JOIN PUBLISHER p ON p.PublisherID = bp.PublisherID
        WHERE b.BookID = '$id'
        GROUP BY b.BookID, b.Title
    "));
}

// ----- UPDATE -----
if (isset($_POST['update'])) {
    $id        = intval($_POST['id']);
    $title     = mysqli_real_escape_string($conn, $_POST['title']);
    $authorStr = mysqli_real_escape_string($conn, trim($_POST['author']));
    $pubName   = mysqli_real_escape_string($conn, trim($_POST['publisher']));
    $pubYear   = intval($_POST['pub_year']) ?: date('Y');

    mysqli_query($conn, "UPDATE BOOK SET Title='$title' WHERE BookID='$id'");

    mysqli_query($conn, "DELETE FROM BOOK_AUTHOR WHERE BookID='$id'");
    $aRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AuthorID FROM AUTHOR WHERE AuthorName='$authorStr'"));
    if (!$aRow) { mysqli_query($conn, "INSERT INTO AUTHOR(AuthorName) VALUES('$authorStr')"); $authorId = mysqli_insert_id($conn); }
    else { $authorId = $aRow['AuthorID']; }
    mysqli_query($conn, "INSERT IGNORE INTO BOOK_AUTHOR(BookID,AuthorID) VALUES('$id','$authorId')");

    mysqli_query($conn, "DELETE FROM BOOK_PUBLISHING WHERE BookID='$id'");
    $pRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT PublisherID FROM PUBLISHER WHERE PublisherName='$pubName'"));
    if (!$pRow) { mysqli_query($conn, "INSERT INTO PUBLISHER(PublisherName) VALUES('$pubName')"); $pubId = mysqli_insert_id($conn); }
    else { $pubId = $pRow['PublisherID']; }
    mysqli_query($conn, "INSERT IGNORE INTO BOOK_PUBLISHING(Year,PublisherID,BookID) VALUES('$pubYear','$pubId','$id')");

    header("Location: books.php"); exit();
}

// ----- SAVE (ADD) -----
if (isset($_POST['save'])) {
    $title     = mysqli_real_escape_string($conn, $_POST['title']);
    $authorStr = mysqli_real_escape_string($conn, trim($_POST['author']));
    $pubName   = mysqli_real_escape_string($conn, trim($_POST['publisher']));
    $pubYear   = intval($_POST['pub_year']) ?: date('Y');

    mysqli_query($conn, "INSERT INTO BOOK(Title) VALUES('$title')");
    $bookId = mysqli_insert_id($conn);

    $aRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AuthorID FROM AUTHOR WHERE AuthorName='$authorStr'"));
    if (!$aRow) { mysqli_query($conn, "INSERT INTO AUTHOR(AuthorName) VALUES('$authorStr')"); $authorId = mysqli_insert_id($conn); }
    else { $authorId = $aRow['AuthorID']; }
    mysqli_query($conn, "INSERT IGNORE INTO BOOK_AUTHOR(BookID,AuthorID) VALUES('$bookId','$authorId')");

    $pRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT PublisherID FROM PUBLISHER WHERE PublisherName='$pubName'"));
    if (!$pRow) { mysqli_query($conn, "INSERT INTO PUBLISHER(PublisherName) VALUES('$pubName')"); $pubId = mysqli_insert_id($conn); }
    else { $pubId = $pRow['PublisherID']; }
    mysqli_query($conn, "INSERT IGNORE INTO BOOK_PUBLISHING(Year,PublisherID,BookID) VALUES('$pubYear','$pubId','$bookId')");

    header("Location: books.php"); exit();
}

// ----- LIST -----
$search = ''; $having = '';
if (isset($_GET['search']) && $_GET['search'] !== '') {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $having = "HAVING title LIKE '%$search%' OR authors LIKE '%$search%' OR publisher LIKE '%$search%'";
}
$result = mysqli_query($conn, "
    SELECT b.BookID, b.Title AS title,
           IFNULL(GROUP_CONCAT(DISTINCT a.AuthorName ORDER BY a.AuthorName SEPARATOR ', '),'—') AS authors,
           IFNULL(MAX(p.PublisherName),'—') AS publisher,
           IFNULL(MAX(bp.Year),'—') AS pub_year,
           CASE WHEN EXISTS (
               SELECT 1 FROM BORROWED_BOOKS bb WHERE bb.BookId = b.BookID AND bb.Status = 'Borrowed'
           ) THEN 0 ELSE 1 END AS available
    FROM BOOK b
    LEFT JOIN BOOK_AUTHOR ba ON ba.BookID = b.BookID
    LEFT JOIN AUTHOR a ON a.AuthorID = ba.AuthorID
    LEFT JOIN BOOK_PUBLISHING bp ON bp.BookID = b.BookID
    LEFT JOIN PUBLISHER p ON p.PublisherID = bp.PublisherID
    GROUP BY b.BookID, b.Title
    $having
    ORDER BY b.BookID DESC
");
$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM BOOK"))['n'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Books — ODM LMS</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
<link rel="stylesheet" href="../styles/main.css">
</head>
<body>
<div class="sidebar">
    <div class="sidebar-brand"><span class="sidebar-brand-icon">📖</span><h3>ODM Library</h3></div>
    <nav class="sidebar-nav">
        <a href="dashboard.php"><span class="nav-icon">⊞</span> Dashboard</a>
        <a href="books.php" class="active"><span class="nav-icon">📖</span> Books</a>
        <a href="members.php"><span class="nav-icon">👥</span> Members</a>
        <a href="borrow.php"><span class="nav-icon">📥</span> Borrow Records</a>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-user"><strong><?php echo htmlspecialchars($staffName); ?></strong><span>Staff</span></div>
        <a href="../includes/logout.php" class="logout-btn">&#x2192;</a>
    </div>
</div>
<div class="main-content">
    <div class="page-header">
        <div class="page-header-left"><h2>Books</h2><p><?php echo $total; ?> total books in catalog</p></div>
    </div>
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 16px;font-size:13px;color:#1d4ed8;margin-bottom:20px;display:flex;align-items:center;gap:8px;">
        ℹ️ Staff can view, add and edit books. Book deletion is restricted to Admin only.
    </div>
    <div class="split-layout">
        <div class="form-section">
            <div class="form-section-title"><?php echo $edit ? '✏️ Edit Book' : '➕ Add New Book'; ?></div>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $edit ? $editData['BookID'] : ''; ?>">
                <div class="form-group">
                    <label>Book Title</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Introduction to Algorithms"
                           value="<?php echo $edit ? htmlspecialchars($editData['Title']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Author</label>
                    <input type="text" name="author" class="form-control" placeholder="e.g. Thomas Cormen"
                           value="<?php echo $edit ? htmlspecialchars($editData['authors']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Publisher</label>
                    <input type="text" name="publisher" class="form-control" placeholder="e.g. MIT Press"
                           value="<?php echo $edit ? htmlspecialchars($editData['publisher']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Publication Year</label>
                    <input type="number" name="pub_year" class="form-control"
                           value="<?php echo $edit ? htmlspecialchars($editData['pub_year']) : date('Y'); ?>"
                           min="1900" max="<?php echo date('Y'); ?>" required>
                </div>
                <div style="display:flex;gap:10px;">
                    <?php if ($edit): ?>
                        <button name="update" class="btn btn-primary" style="flex:1;">Update Book</button>
                        <a href="books.php" class="btn btn-ghost" style="flex:1;justify-content:center;">Cancel</a>
                    <?php else: ?>
                        <button name="save" class="btn btn-primary" style="flex:1;">Add Book</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <div class="card">
            <div class="card-title"><span>📖</span> All Books</div>
            <div style="padding:16px 16px 0;">
                <form method="GET">
                    <div class="search-row">
                        <input type="text" name="search" class="form-control" placeholder="Search title, author, publisher..."
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <?php if ($search): ?><a href="books.php" class="btn btn-ghost">Clear</a><?php endif; ?>
                    </div>
                </form>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>#</th><th>Title</th><th>Author(s)</th><th>Publisher</th><th>Year</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><span class="muted"><?php echo $row['BookID']; ?></span></td>
                        <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['authors']); ?></td>
                        <td><span class="muted"><?php echo htmlspecialchars($row['publisher']); ?></span></td>
                        <td><span class="muted"><?php echo htmlspecialchars($row['pub_year']); ?></span></td>
                        <td>
                            <span class="badge <?php echo $row['available'] ? 'badge-returned' : 'badge-overdue'; ?>">
                                <?php echo $row['available'] ? 'Available' : 'Borrowed'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="?edit=<?php echo $row['BookID']; ?>" class="btn btn-warning btn-sm">Edit</a>
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

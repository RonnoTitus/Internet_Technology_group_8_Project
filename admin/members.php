<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';
requireAdmin();

// ----- DELETE -----
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM MEMBER WHERE MemberID='$id'");
    header("Location: members.php"); exit();
}

// ----- SAVE -----
$saveError = '';
if (isset($_POST['save'])) {
    $first = mysqli_real_escape_string($conn, trim($_POST['first_name']));
    $last  = mysqli_real_escape_string($conn, trim($_POST['last_name']));
    $addr  = mysqli_real_escape_string($conn, trim($_POST['address']));
    $ph    = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $em    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $pw    = mysqli_real_escape_string($conn, $_POST['password']);

    // Duplicate email check
    $exists = mysqli_fetch_assoc(mysqli_query($conn, "SELECT MemberID FROM MEMBER WHERE Email='$em'"));
    if ($exists) {
        $saveError = "A member with email <strong>" . htmlspecialchars($em) . "</strong> already exists.";
    } else {
        mysqli_query($conn, "INSERT INTO MEMBER(FirstName,LastName,Address,Email,password)
                             VALUES('$first','$last','$addr','$em','$pw')");
        $memberId = mysqli_insert_id($conn);

        if ($ph !== '') {
            mysqli_query($conn, "INSERT IGNORE INTO MEMBER_PHONE(PhoneNo,MemberID) VALUES('$ph','$memberId')");
        }
        header("Location: members.php"); exit();
    }
}

// ----- LIST -----
$search = ''; $where = '';
if (isset($_GET['search']) && $_GET['search'] !== '') {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where  = "HAVING full_name LIKE '%$search%' OR email LIKE '%$search%'";
}
$result = mysqli_query($conn, "
    SELECT m.MemberID,
           CONCAT(m.FirstName, ' ', m.LastName) AS full_name,
           m.Email AS email,
           m.Address,
           GROUP_CONCAT(DISTINCT mp.PhoneNo SEPARATOR ', ') AS phones
    FROM MEMBER m
    LEFT JOIN MEMBER_PHONE mp ON mp.MemberID = m.MemberID
    GROUP BY m.MemberID, m.FirstName, m.LastName, m.Email, m.Address
    $where
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
        <a href="staff.php"><span class="nav-icon">🪪</span> Staff</a>
        <a href="borrow.php"><span class="nav-icon">📥</span> Borrow Records</a>
        <a href="messages.php"><span class="nav-icon">✉️</span> Messages</a>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-user"><strong><?php echo htmlspecialchars($_SESSION['admin']); ?></strong><span>Admin</span></div>
        <a href="../includes/logout.php" class="logout-btn">&#x2192;</a>
    </div>
</div>
<div class="main-content">
    <div class="page-header">
        <div class="page-header-left">
            <h2>Members</h2>
            <p><?php echo $total; ?> registered members</p>
        </div>
    </div>
    <div class="split-layout">
        <div class="form-section">
            <div class="form-section-title">➕ Register Member</div>
            <?php if ($saveError): ?>
            <div class="alert alert-error"><?php echo $saveError; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group"><label>First Name</label>
                    <input type="text" name="first_name" class="form-control" placeholder="e.g. John" required></div>
                <div class="form-group"><label>Last Name</label>
                    <input type="text" name="last_name" class="form-control" placeholder="e.g. Doe" required></div>
                <div class="form-group"><label>Address</label>
                    <input type="text" name="address" class="form-control" placeholder="Kampala, Uganda"></div>
                <div class="form-group"><label>Phone</label>
                    <input type="text" name="phone" class="form-control" placeholder="+256 7XX XXX XXX"></div>
                <div class="form-group"><label>Email</label>
                    <input type="email" name="email" class="form-control" placeholder="member@email.com" required></div>
                <div class="form-group"><label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Initial password" required></div>
                <button name="save" class="btn btn-primary" style="width:100%;">Register Member</button>
            </form>
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
                    <thead><tr><th>#</th><th>Full Name</th><th>Email</th><th>Phone(s)</th><th>Address</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><span class="muted"><?php echo $row['MemberID']; ?></span></td>
                        <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                        <td><span class="muted"><?php echo htmlspecialchars($row['email']); ?></span></td>
                        <td><span class="muted"><?php echo htmlspecialchars($row['phones'] ?? '—'); ?></span></td>
                        <td><span class="muted"><?php echo htmlspecialchars($row['Address']); ?></span></td>
                        <td>
                            <a href="?delete=<?php echo $row['MemberID']; ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Remove member?')">Remove</a>
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

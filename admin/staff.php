<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';
requireAdmin();

// ----- DELETE -----
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM STAFF_INFO WHERE StaffId='$id'");
    header("Location: staff.php"); exit();
}

// ----- SAVE -----
$saveError = '';
if (isset($_POST['save'])) {
    $first = mysqli_real_escape_string($conn, trim($_POST['first_name']));
    $last  = mysqli_real_escape_string($conn, trim($_POST['last_name']));
    $ph    = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $em    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $pw    = mysqli_real_escape_string($conn, $_POST['password']);

    // Duplicate email check
    $exists = mysqli_fetch_assoc(mysqli_query($conn, "SELECT StaffId FROM STAFF_INFO WHERE Email='$em'"));
    if ($exists) {
        $saveError = "A staff member with email <strong>" . htmlspecialchars($em) . "</strong> already exists.";
    } else {
        mysqli_query($conn, "INSERT INTO STAFF_INFO(FirstName,LastName,Email,password)
                             VALUES('$first','$last','$em','$pw')");
        $staffId = mysqli_insert_id($conn);

        if ($ph !== '') {
            mysqli_query($conn, "INSERT IGNORE INTO STAFF_PHONE(StaffId,PhoneNo) VALUES('$staffId','$ph')");
        }
        header("Location: staff.php"); exit();
    }
}

$result = mysqli_query($conn, "
    SELECT s.StaffId,
           CONCAT(s.FirstName, ' ', s.LastName) AS full_name,
           s.Email,
           GROUP_CONCAT(DISTINCT sp.PhoneNo SEPARATOR ', ') AS phones
    FROM STAFF_INFO s
    LEFT JOIN STAFF_PHONE sp ON sp.StaffId = s.StaffId
    GROUP BY s.StaffId, s.FirstName, s.LastName, s.Email
    ORDER BY s.StaffId DESC
");
$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM STAFF_INFO"))['n'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff — ODM LMS</title>
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
        <a href="staff.php" class="active"><span class="nav-icon">🪪</span> Staff</a>
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
            <h2>Staff</h2>
            <p><?php echo $total; ?> staff members — Admin only</p>
        </div>
    </div>
    <div class="split-layout">
        <div class="form-section">
            <div class="form-section-title">➕ Add Staff Member</div>
            <?php if ($saveError): ?>
            <div class="alert alert-error"><?php echo $saveError; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group"><label>First Name</label>
                    <input type="text" name="first_name" class="form-control" placeholder="Alice" required></div>
                <div class="form-group"><label>Last Name</label>
                    <input type="text" name="last_name" class="form-control" placeholder="Nakato" required></div>
                <div class="form-group"><label>Phone</label>
                    <input type="text" name="phone" class="form-control" placeholder="+256 7XX XXX XXX"></div>
                <div class="form-group"><label>Email</label>
                    <input type="email" name="email" class="form-control" placeholder="staff@kyu.ac.ug" required></div>
                <div class="form-group"><label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Login password" required></div>
                <button name="save" class="btn btn-primary" style="width:100%;">Add Staff Member</button>
            </form>
        </div>
        <div class="card">
            <div class="card-title"><span>🪪</span> All Staff</div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>#</th><th>Full Name</th><th>Email</th><th>Phone(s)</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><span class="muted"><?php echo $row['StaffId']; ?></span></td>
                        <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                        <td><span class="muted"><?php echo htmlspecialchars($row['Email']); ?></span></td>
                        <td><span class="muted"><?php echo htmlspecialchars($row['phones'] ?? '—'); ?></span></td>
                        <td>
                            <a href="?delete=<?php echo $row['StaffId']; ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Remove staff member?')">Remove</a>
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

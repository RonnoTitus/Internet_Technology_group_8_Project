<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';
requireStaff();

if (!isset($_GET['id'])) {
    header("Location: borrow.php");
    exit();
}

$borrowId = intval($_GET['id']);

// Update all BORROWED_BOOKS for this transaction that are still 'Borrowed'
mysqli_query($conn, "UPDATE BORROWED_BOOKS SET Status='Returned' WHERE BorrowId='$borrowId' AND Status='Borrowed'");

header("Location: borrow.php");
exit();

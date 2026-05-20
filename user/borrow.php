<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';
requireMember();

if (!isset($_GET['id'])) {
    header("Location: books.php");
    exit();
}

$book_id   = intval($_GET['id']);
$email     = $_SESSION['user'];
$userData  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT MemberID FROM MEMBER WHERE Email='$email'"));
$member_id = $userData['MemberID'];

// Check the book is not already borrowed
$check = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS n FROM BORROWED_BOOKS WHERE BookId='$book_id' AND Status='Borrowed'
"));

if ($check && $check['n'] == 0) {
    $today      = date('Y-m-d');
    $returnDate = date('Y-m-d', strtotime('+7 days'));

    // Self-service borrow: StaffId = NULL
    mysqli_query($conn, "INSERT INTO BORROWING_TRANSACTION(MemberId,StaffId,Borrowdate)
                         VALUES('$member_id',NULL,'$today')");
    $borrowId = mysqli_insert_id($conn);
    mysqli_query($conn, "INSERT INTO BORROWED_BOOKS(BookId,ReturnDate,Status,BorrowId)
                         VALUES('$book_id','$returnDate','Borrowed','$borrowId')");
}

header("Location: mybooks.php");
exit();

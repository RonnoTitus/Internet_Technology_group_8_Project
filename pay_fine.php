<?php
/**
 * Fine payment is not handled via a separate table in this schema.
 * Fines are computed dynamically (UGX 1,000 per overdue book) in the UI.
 * This stub redirects back to the borrow records page.
 */
session_start();
include 'includes/db.php';
include 'includes/auth.php';

requireAdmin();

header("Location: admin/borrow.php");
exit();

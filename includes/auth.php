<?php
/**
 * RBAC helper — include after session_start() and db.php
 *
 * Sessions:
 *   $_SESSION['admin']  → admin username
 *   $_SESSION['staff']  → staff email
 *   $_SESSION['user']   → member email
 */

function requireAdmin() {
    if (!isset($_SESSION['admin'])) {
        header("Location: ../includes/login.php");
        exit();
    }
}

function requireStaff() {
    if (!isset($_SESSION['staff'])) {
        header("Location: ../includes/login.php");
        exit();
    }
}

function requireMember() {
    if (!isset($_SESSION['user'])) {
        header("Location: ../includes/login.php");
        exit();
    }
}

// True if the current session is admin
function isAdmin()  { return isset($_SESSION['admin']); }

// True if the current session is staff
function isStaff()  { return isset($_SESSION['staff']); }

// True if the current session is a member
function isMember() { return isset($_SESSION['user']); }

// Redirect logged-in users away from login/signup pages
function redirectIfLoggedIn() {
    if (isset($_SESSION['admin']))  { header("Location: ../admin/dashboard.php");  exit(); }
    if (isset($_SESSION['staff']))  { header("Location: ../staff/dashboard.php");  exit(); }
    if (isset($_SESSION['user']))   { header("Location: ../user/dashboard.php");   exit(); }
}

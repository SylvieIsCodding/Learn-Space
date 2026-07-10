<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /lms/login.php');
        exit();
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        header('Location: /lms/unauthorized.php');
        exit();
    }
}

function currentUser() {
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['name'],
        'role' => $_SESSION['role'],
        'email' => $_SESSION['email']
    ];
}
?>

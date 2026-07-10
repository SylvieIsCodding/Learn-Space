<?php
require_once 'includes/auth.php';
requireLogin();

switch ($_SESSION['role']) {
    case 'teacher':
        header('Location: /lms/teacher/dashboard.php');
        break;
    case 'student':
        header('Location: /lms/student/dashboard.php');
        break;
    case 'promoter':
        header('Location: /lms/promoter/dashboard.php');
        break;
    default:
        header('Location: /lms/login.php');
}
exit();
?>

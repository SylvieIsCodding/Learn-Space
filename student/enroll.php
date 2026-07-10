<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('student');
$user = currentUser();

$course_id = intval($_GET['course_id'] ?? 0);
if (!$course_id) {
    header('Location: /lms/student/dashboard.php');
    exit();
}

$stmt = $conn->prepare("INSERT IGNORE INTO enrollments (student_id, course_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user['id'], $course_id);
$stmt->execute();

header("Location: /lms/student/learn.php?course_id=$course_id");
exit();
?>

<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('student');
$user = currentUser();

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$lesson_id = intval($data['lesson_id']);
$answers = $data['answers'] ?? [];

// Get quizzes for this lesson
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE lesson_id = ?");
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$quizzes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total = count($quizzes);
$correct = 0;

foreach ($quizzes as $quiz) {
    $selected = $answers[$quiz['id']] ?? null;
    $is_correct = $selected === $quiz['correct_option'] ? 1 : 0;
    if ($is_correct) $correct++;

    if ($selected) {
        $stmt = $conn->prepare("INSERT INTO quiz_submissions (student_id, quiz_id, selected_option, is_correct) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE selected_option=?, is_correct=?");
        $stmt->bind_param("iisssi", $user['id'], $quiz['id'], $selected, $is_correct, $selected, $is_correct);
        $stmt->execute();
    }
}

$score = $total > 0 ? round(($correct / $total) * 100) : 100;

// Save progress if score >= 50 or no quizzes
if ($score >= 50 || $total === 0) {
    $completed = 1;
    $stmt = $conn->prepare("INSERT INTO lesson_progress (student_id, lesson_id, completed, score) VALUES (?, ?, 1, ?) ON DUPLICATE KEY UPDATE completed=1, score=?");
    $stmt->bind_param("iidd", $user['id'], $lesson_id, $score, $score);
    $stmt->execute();
}

echo json_encode(['score' => $score, 'correct' => $correct, 'total' => $total]);
?>

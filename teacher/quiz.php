<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('teacher');
$user = currentUser();

$lesson_id = intval($_GET['lesson_id'] ?? 0);
if (!$lesson_id) {
    header('Location: /lms/teacher/dashboard.php');
    exit();
}

// Get lesson and verify ownership
$stmt = $conn->prepare("SELECT l.*, c.title as course_title, c.id as course_id FROM lessons l JOIN courses c ON c.id = l.course_id WHERE l.id = ? AND c.teacher_id = ?");
$stmt->bind_param("ii", $lesson_id, $user['id']);
$stmt->execute();
$lesson = $stmt->get_result()->fetch_assoc();
if (!$lesson) {
    header('Location: /lms/teacher/dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['question']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $option_d = trim($_POST['option_d']);
    $correct = $_POST['correct_option'];

    if (empty($question) || empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d)) {
        $error = 'Tous les champs sont obligatoires.';
    } else {
        $stmt = $conn->prepare("INSERT INTO quizzes (lesson_id, question, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $lesson_id, $question, $option_a, $option_b, $option_c, $option_d, $correct);
        if ($stmt->execute()) {
            $success = 'Question ajoutée avec succès!';
        } else {
            $error = 'Erreur lors de l\'enregistrement.';
        }
    }
}

// Get existing quizzes
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE lesson_id = ?");
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$quizzes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnSpace — Quiz</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/lms/assets/css/style.css">
</head>
<body class="bg-gray-50">
<div class="flex min-h-screen">
    <aside class="w-64 bg-indigo-700 text-white flex flex-col sidebar">
        <div class="p-6 border-b border-indigo-600">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-500 rounded-xl flex items-center justify-center font-bold text-lg">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <div>
                    <p class="font-semibold text-sm"><?= htmlspecialchars($user['name']) ?></p>
                    <p class="text-indigo-300 text-xs">Enseignant</p>
                </div>
            </div>
        </div>
        <nav class="flex-1 p-4 space-y-1">
            <a href="/lms/teacher/dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-indigo-600 text-sm font-medium transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Tableau de bord
            </a>
            <a href="/lms/teacher/create_course.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-indigo-600 text-sm font-medium transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouveau cours
            </a>
        </nav>
        <div class="p-4 border-t border-indigo-600">
            <a href="/lms/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-indigo-600 text-sm font-medium transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Déconnexion
            </a>
        </div>
    </aside>

    <main class="flex-1 p-8">
        <div class="mb-8">
            <a href="/lms/teacher/lessons.php?course_id=<?= $lesson['course_id'] ?>" class="text-indigo-600 text-sm hover:underline">← Retour aux leçons</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Quiz — <?= htmlspecialchars($lesson['title']) ?></h1>
            <p class="text-gray-500 mt-1">Cours: <?= htmlspecialchars($lesson['course_title']) ?></p>
        </div>

        <?php if ($error): ?>
        <div class="bg-red-50 text-red-600 px-4 py-3 rounded-lg mb-4 text-sm"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="bg-green-50 text-green-600 px-4 py-3 rounded-lg mb-4 text-sm"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-5 gap-8">
            <!-- Add Question -->
            <div class="col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-8">
                    <h2 class="font-semibold text-gray-800 mb-4">Ajouter une question</h2>
                    <form method="POST">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Question *</label>
                            <textarea name="question" required rows="3"
                                class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm resize-none"></textarea>
                        </div>
                        <?php foreach (['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'] as $key => $label): ?>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Option <?= $label ?> *</label>
                            <input type="text" name="option_<?= $key ?>" required
                                class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                        </div>
                        <?php endforeach; ?>
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bonne réponse</label>
                            <select name="correct_option" required
                                class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm bg-white">
                                <option value="a">Option A</option>
                                <option value="b">Option B</option>
                                <option value="c">Option C</option>
                                <option value="d">Option D</option>
                            </select>
                        </div>
                        <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl transition text-sm">
                            Ajouter la question
                        </button>
                    </form>
                </div>
            </div>

            <!-- Questions List -->
            <div class="col-span-3">
                <h2 class="font-semibold text-gray-800 mb-4">Questions (<?= count($quizzes) ?>)</h2>
                <?php if (empty($quizzes)): ?>
                <div class="bg-white rounded-2xl p-10 text-center shadow-sm border border-gray-100">
                    <div class="text-4xl mb-3">❓</div>
                    <p class="text-gray-500">Aucune question pour l'instant.</p>
                </div>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($quizzes as $i => $quiz): ?>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <p class="font-medium text-gray-800 mb-3">Q<?= $i+1 ?>. <?= htmlspecialchars($quiz['question']) ?></p>
                        <div class="grid grid-cols-2 gap-2">
                            <?php foreach (['a', 'b', 'c', 'd'] as $opt): ?>
                            <div class="px-3 py-2 rounded-lg text-sm <?= $quiz['correct_option'] === $opt ? 'bg-green-50 text-green-700 font-medium border border-green-200' : 'bg-gray-50 text-gray-600' ?>">
                                <?= strtoupper($opt) ?>. <?= htmlspecialchars($quiz["option_$opt"]) ?>
                                <?= $quiz['correct_option'] === $opt ? ' ✓' : '' ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
</body>
</html>

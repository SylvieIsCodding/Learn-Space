<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('student');
$user = currentUser();

$course_id = intval($_GET['course_id'] ?? 0);
$lesson_id = intval($_GET['lesson_id'] ?? 0);

// Verify enrollment
$stmt = $conn->prepare("SELECT * FROM enrollments WHERE student_id = ? AND course_id = ?");
$stmt->bind_param("ii", $user['id'], $course_id);
$stmt->execute();
if (!$stmt->get_result()->fetch_assoc()) {
    header('Location: /lms/student/dashboard.php');
    exit();
}

// Get course
$stmt = $conn->prepare("SELECT c.*, u.name as teacher_name FROM courses c JOIN users u ON u.id = c.teacher_id WHERE c.id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

// Get all lessons
$stmt = $conn->prepare("SELECT l.*, lp.completed, lp.score FROM lessons l LEFT JOIN lesson_progress lp ON lp.lesson_id = l.id AND lp.student_id = ? WHERE l.course_id = ? ORDER BY l.order_num ASC");
$stmt->bind_param("ii", $user['id'], $course_id);
$stmt->execute();
$lessons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Current lesson
if (!$lesson_id && !empty($lessons)) {
    $lesson_id = $lessons[0]['id'];
}

$current_lesson = null;
foreach ($lessons as $l) {
    if ($l['id'] == $lesson_id) {
        $current_lesson = $l;
        break;
    }
}

// Get quizzes for current lesson
$quizzes = [];
if ($current_lesson) {
    $stmt = $conn->prepare("SELECT * FROM quizzes WHERE lesson_id = ?");
    $stmt->bind_param("i", $lesson_id);
    $stmt->execute();
    $quizzes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Calculate overall progress
$total = count($lessons);
$completed = count(array_filter($lessons, fn($l) => $l['completed']));
$progress = $total > 0 ? round(($completed / $total) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnSpace — <?= htmlspecialchars($course['title']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/lms/assets/css/style.css">
</head>
<body class="bg-gray-50">
<div class="flex min-h-screen">
    <!-- Course Sidebar -->
    <aside class="w-72 bg-white border-r border-gray-100 flex flex-col">
        <div class="p-6 border-b border-gray-100">
            <a href="/lms/student/dashboard.php" class="text-indigo-600 text-sm hover:underline">← Retour</a>
            <h2 class="font-bold text-gray-900 mt-2 text-sm"><?= htmlspecialchars($course['title']) ?></h2>
            <p class="text-xs text-gray-400">Par <?= htmlspecialchars($course['teacher_name']) ?></p>
            <div class="mt-3">
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>Progression</span>
                    <span class="font-semibold text-indigo-600"><?= $progress ?>%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5">
                    <div class="bg-indigo-600 h-1.5 rounded-full" style="width: <?= $progress ?>%"></div>
                </div>
            </div>
        </div>
        <nav class="flex-1 overflow-y-auto p-4 space-y-2">
            <?php foreach ($lessons as $l): ?>
            <a href="/lms/student/learn.php?course_id=<?= $course_id ?>&lesson_id=<?= $l['id'] ?>"
               class="flex items-center gap-3 p-3 rounded-xl text-sm transition <?= $l['id'] == $lesson_id ? 'bg-indigo-50 text-indigo-700 font-medium' : 'hover:bg-gray-50 text-gray-600' ?>">
                <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 <?= $l['completed'] ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-500' ?> text-xs font-bold">
                    <?= $l['completed'] ? '✓' : $l['order_num'] ?>
                </div>
                <span class="line-clamp-2"><?= htmlspecialchars($l['title']) ?></span>
            </a>
            <?php endforeach; ?>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
        <?php if ($current_lesson): ?>
        <div class="p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($current_lesson['title']) ?></h1>
            <p class="text-gray-400 text-sm mb-6">
                <?= $current_lesson['content_type'] === 'pdf' ? '📄 Document PDF' : '🎬 Vidéo' ?>
            </p>

            <!-- Content Viewer -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <?php if ($current_lesson['content_type'] === 'pdf'): ?>
                <iframe src="<?= htmlspecialchars($current_lesson['content_path']) ?>"
                    class="w-full" style="height: 600px;" frameborder="0"></iframe>
                <?php else: ?>
                <video controls class="w-full" style="max-height: 500px; background: #000;">
                    <source src="<?= htmlspecialchars($current_lesson['content_path']) ?>" type="video/mp4">
                    Votre navigateur ne supporte pas la vidéo.
                </video>
                <?php endif; ?>
            </div>

            <!-- Quiz Section -->
            <?php if (!empty($quizzes) && !$current_lesson['completed']): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h2 class="text-xl font-bold text-gray-900 mb-2">Évaluation</h2>
                <p class="text-gray-500 text-sm mb-6">Répondez aux questions pour valider cette leçon.</p>

                <form id="quiz-form">
                    <input type="hidden" name="lesson_id" value="<?= $lesson_id ?>">
                    <?php foreach ($quizzes as $i => $quiz): ?>
                    <div class="mb-6">
                        <p class="font-medium text-gray-800 mb-3">Q<?= $i+1 ?>. <?= htmlspecialchars($quiz['question']) ?></p>
                        <div class="space-y-2" id="options-<?= $quiz['id'] ?>">
                            <?php foreach (['a', 'b', 'c', 'd'] as $opt): ?>
                            <label class="quiz-option flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer transition">
                                <input type="radio" name="quiz_<?= $quiz['id'] ?>" value="<?= $opt ?>" class="text-indigo-600" required>
                                <span class="text-sm"><?= strtoupper($opt) ?>. <?= htmlspecialchars($quiz["option_$opt"]) ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <button type="button" onclick="submitQuiz()"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl transition">
                        Soumettre l'évaluation
                    </button>
                </form>

                <div id="quiz-result" class="hidden mt-6 p-6 rounded-2xl text-center"></div>
            </div>
            <?php elseif ($current_lesson['completed']): ?>
            <div class="bg-green-50 border border-green-200 rounded-2xl p-6 text-center">
                <div class="text-4xl mb-2">✅</div>
                <p class="font-semibold text-green-700">Leçon complétée!</p>
                <p class="text-green-600 text-sm mt-1">Score obtenu: <?= $current_lesson['score'] ?>%</p>
                <?php
                $next = null;
                $found = false;
                foreach ($lessons as $l) {
                    if ($found) { $next = $l; break; }
                    if ($l['id'] == $lesson_id) $found = true;
                }
                ?>
                <?php if ($next): ?>
                <a href="/lms/student/learn.php?course_id=<?= $course_id ?>&lesson_id=<?= $next['id'] ?>"
                   class="inline-block mt-4 bg-green-600 text-white px-6 py-2 rounded-xl text-sm font-medium hover:bg-green-700 transition">
                    Leçon suivante →
                </a>
                <?php endif; ?>
            </div>
            <?php elseif (empty($quizzes)): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
                <p class="text-gray-500">Aucune évaluation pour cette leçon.</p>
                <button onclick="markComplete()" class="mt-4 bg-indigo-600 text-white px-6 py-2 rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                    Marquer comme complété
                </button>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </main>
</div>

<script>
const quizData = <?= json_encode($quizzes) ?>;

function submitQuiz() {
    const form = document.getElementById('quiz-form');
    const lessonId = <?= $lesson_id ?>;
    let answers = {};
    let allAnswered = true;

    quizData.forEach(q => {
        const selected = form.querySelector(`input[name="quiz_${q.id}"]:checked`);
        if (!selected) { allAnswered = false; return; }
        answers[q.id] = selected.value;
    });

    if (!allAnswered) {
        alert('Veuillez répondre à toutes les questions.');
        return;
    }

    fetch('/lms/student/submit_quiz.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ lesson_id: lessonId, answers })
    })
    .then(r => r.json())
    .then(data => {
        const result = document.getElementById('quiz-result');
        result.classList.remove('hidden');

        if (data.score >= 50) {
            result.className = 'mt-6 p-6 rounded-2xl text-center bg-green-50 border border-green-200';
            result.innerHTML = `<div class="text-4xl mb-2">🎉</div>
                <p class="font-bold text-green-700 text-xl">${data.score}%</p>
                <p class="text-green-600 mt-1">Félicitations! Leçon validée.</p>
                <button onclick="location.reload()" class="mt-4 bg-green-600 text-white px-6 py-2 rounded-xl text-sm font-medium">
                    Continuer
                </button>`;
        } else {
            result.className = 'mt-6 p-6 rounded-2xl text-center bg-red-50 border border-red-200';
            result.innerHTML = `<div class="text-4xl mb-2">😔</div>
                <p class="font-bold text-red-700 text-xl">${data.score}%</p>
                <p class="text-red-600 mt-1">Score insuffisant. Révisez et réessayez.</p>
                <button onclick="location.reload()" class="mt-4 bg-red-600 text-white px-6 py-2 rounded-xl text-sm font-medium">
                    Réessayer
                </button>`;
        }

        // Show correct/wrong answers
        quizData.forEach(q => {
            const options = document.querySelectorAll(`#options-${q.id} label`);
            options.forEach(label => {
                const input = label.querySelector('input');
                if (input.value === q.correct_option) {
                    label.classList.add('correct');
                } else if (input.checked && input.value !== q.correct_option) {
                    label.classList.add('wrong');
                }
                input.disabled = true;
            });
        });

        document.querySelector('button[onclick="submitQuiz()"]').disabled = true;
    });
}

function markComplete() {
    fetch('/lms/student/submit_quiz.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ lesson_id: <?= $lesson_id ?>, answers: {} })
    })
    .then(r => r.json())
    .then(() => location.reload());
}
</script>
</body>
</html>

<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('student');
$user = currentUser();

// Get enrolled courses with progress
$stmt = $conn->prepare("
    SELECT c.*, u.name as teacher_name,
        COUNT(DISTINCT l.id) as total_lessons,
        COUNT(DISTINCT lp.lesson_id) as completed_lessons
    FROM enrollments e
    JOIN courses c ON c.id = e.course_id
    JOIN users u ON u.id = c.teacher_id
    LEFT JOIN lessons l ON l.course_id = c.id
    LEFT JOIN lesson_progress lp ON lp.lesson_id = l.id AND lp.student_id = ? AND lp.completed = 1
    WHERE e.student_id = ?
    GROUP BY c.id
");
$stmt->bind_param("ii", $user['id'], $user['id']);
$stmt->execute();
$enrolled = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get available courses (not enrolled)
$stmt = $conn->prepare("
    SELECT c.*, u.name as teacher_name, COUNT(DISTINCT l.id) as total_lessons
    FROM courses c
    JOIN users u ON u.id = c.teacher_id
    LEFT JOIN lessons l ON l.course_id = c.id
    WHERE c.id NOT IN (
        SELECT course_id FROM enrollments WHERE student_id = ?
    )
    GROUP BY c.id
");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$available = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnSpace — Étudiant</title>
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
                    <p class="text-indigo-300 text-xs">Étudiant</p>
                </div>
            </div>
        </div>
        <nav class="flex-1 p-4 space-y-1">
            <a href="/lms/student/dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl nav-link active text-sm font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Tableau de bord
            </a>
            <a href="/lms/student/courses.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-indigo-600 text-sm font-medium transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Explorer les cours
            </a>
            <a href="/lms/student/certificates.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-indigo-600 text-sm font-medium transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                Mes certificats
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
            <h1 class="text-2xl font-bold text-gray-900">Bonjour, <?= htmlspecialchars($user['name']) ?> 👋</h1>
            <p class="text-gray-500 mt-1">Continuez votre apprentissage</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500 mb-1">Cours suivis</p>
                <p class="text-3xl font-bold text-indigo-600"><?= count($enrolled) ?></p>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500 mb-1">Leçons complétées</p>
                <p class="text-3xl font-bold text-purple-600"><?= array_sum(array_column($enrolled, 'completed_lessons')) ?></p>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500 mb-1">Cours disponibles</p>
                <p class="text-3xl font-bold text-green-600"><?= count($available) ?></p>
            </div>
        </div>

        <!-- Enrolled Courses -->
        <?php if (!empty($enrolled)): ?>
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Mes cours en cours</h2>
        <div class="grid grid-cols-2 gap-6 mb-8">
            <?php foreach ($enrolled as $course): ?>
            <?php
                $progress = $course['total_lessons'] > 0
                    ? round(($course['completed_lessons'] / $course['total_lessons']) * 100)
                    : 0;
            ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden lesson-card">
                <div class="h-3 bg-gradient-to-r from-indigo-500 to-purple-500" style="width: 100%"></div>
                <div class="p-6">
                    <h3 class="font-semibold text-gray-800 mb-1"><?= htmlspecialchars($course['title']) ?></h3>
                    <p class="text-xs text-gray-400 mb-4">Par <?= htmlspecialchars($course['teacher_name']) ?></p>
                    <div class="mb-2 flex justify-between text-sm">
                        <span class="text-gray-500">Progression</span>
                        <span class="font-semibold text-indigo-600"><?= $progress ?>%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2 mb-4">
                        <div class="bg-indigo-600 h-2 rounded-full progress-bar" style="width: <?= $progress ?>%"></div>
                    </div>
                    <a href="/lms/student/learn.php?course_id=<?= $course['id'] ?>"
                       class="block text-center bg-indigo-600 text-white py-2 rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                        Continuer →
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Available Courses -->
        <?php if (!empty($available)): ?>
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Cours disponibles</h2>
        <div class="grid grid-cols-2 gap-6">
            <?php foreach ($available as $course): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden lesson-card">
                <div class="h-3 bg-gradient-to-r from-gray-200 to-gray-300"></div>
                <div class="p-6">
                    <h3 class="font-semibold text-gray-800 mb-1"><?= htmlspecialchars($course['title']) ?></h3>
                    <p class="text-xs text-gray-400 mb-2">Par <?= htmlspecialchars($course['teacher_name']) ?></p>
                    <p class="text-gray-500 text-sm mb-4 line-clamp-2"><?= htmlspecialchars($course['description']) ?></p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400">📖 <?= $course['total_lessons'] ?> leçons</span>
                        <a href="/lms/student/enroll.php?course_id=<?= $course['id'] ?>"
                           class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl text-sm font-medium hover:bg-indigo-100 transition">
                            S'inscrire
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (empty($enrolled) && empty($available)): ?>
        <div class="bg-white rounded-2xl p-12 text-center shadow-sm border border-gray-100">
            <div class="text-5xl mb-4">🎓</div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Aucun cours disponible</h3>
            <p class="text-gray-400">Les enseignants n'ont pas encore publié de cours.</p>
        </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>

<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('teacher');
$user = currentUser();

// Get teacher's courses
$stmt = $conn->prepare("SELECT c.*, COUNT(DISTINCT l.id) as lesson_count, COUNT(DISTINCT e.id) as student_count 
    FROM courses c 
    LEFT JOIN lessons l ON l.course_id = c.id 
    LEFT JOIN enrollments e ON e.course_id = c.id 
    WHERE c.teacher_id = ? 
    GROUP BY c.id 
    ORDER BY c.created_at DESC");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnSpace — Enseignant</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/lms/assets/css/style.css">
</head>
<body class="bg-gray-50">

<div class="flex min-h-screen">
    <!-- Sidebar -->
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
            <a href="/lms/teacher/dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl nav-link active text-sm font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Tableau de bord
            </a>
            <a href="/lms/teacher/create_course.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-indigo-600 text-sm font-medium transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouveau cours
            </a>
            <a href="/lms/teacher/lessons.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-indigo-600 text-sm font-medium transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Mes leçons
            </a>
        </nav>
        <div class="p-4 border-t border-indigo-600">
            <a href="/lms/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-indigo-600 text-sm font-medium transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Déconnexion
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Bonjour, <?= htmlspecialchars($user['name']) ?> 👋</h1>
            <p class="text-gray-500 mt-1">Gérez vos cours et suivez vos étudiants</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500 mb-1">Total cours</p>
                <p class="text-3xl font-bold text-indigo-600"><?= count($courses) ?></p>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500 mb-1">Total leçons</p>
                <p class="text-3xl font-bold text-purple-600"><?= array_sum(array_column($courses, 'lesson_count')) ?></p>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500 mb-1">Total étudiants</p>
                <p class="text-3xl font-bold text-green-600"><?= array_sum(array_column($courses, 'student_count')) ?></p>
            </div>
        </div>

        <!-- Courses -->
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Mes cours</h2>
            <a href="/lms/teacher/create_course.php" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                + Nouveau cours
            </a>
        </div>

        <?php if (empty($courses)): ?>
        <div class="bg-white rounded-2xl p-12 text-center shadow-sm border border-gray-100">
            <div class="text-5xl mb-4">📚</div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Aucun cours pour l'instant</h3>
            <p class="text-gray-400 mb-6">Créez votre premier cours pour commencer</p>
            <a href="/lms/teacher/create_course.php" class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-indigo-700 transition">
                Créer un cours
            </a>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-2 gap-6">
            <?php foreach ($courses as $course): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden lesson-card">
                <div class="h-3 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
                <div class="p-6">
                    <h3 class="font-semibold text-gray-800 mb-1"><?= htmlspecialchars($course['title']) ?></h3>
                    <p class="text-gray-400 text-sm mb-4 line-clamp-2"><?= htmlspecialchars($course['description']) ?></p>
                    <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
                        <span>📖 <?= $course['lesson_count'] ?> leçons</span>
                        <span>👥 <?= $course['student_count'] ?> étudiants</span>
                    </div>
                    <div class="flex gap-2">
                        <a href="/lms/teacher/lessons.php?course_id=<?= $course['id'] ?>" 
                           class="flex-1 text-center bg-indigo-50 text-indigo-600 py-2 rounded-xl text-sm font-medium hover:bg-indigo-100 transition">
                            Gérer les leçons
                        </a>
                        <a href="/lms/teacher/edit_course.php?id=<?= $course['id'] ?>" 
                           class="px-4 bg-gray-50 text-gray-600 py-2 rounded-xl text-sm font-medium hover:bg-gray-100 transition">
                            ✏️
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>
</div>

</body>
</html>

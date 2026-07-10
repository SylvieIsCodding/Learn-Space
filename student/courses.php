<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('student');
$user = currentUser();

// Get all courses with enrollment status
$stmt = $conn->prepare("
    SELECT c.*, u.name as teacher_name,
        COUNT(DISTINCT l.id) as total_lessons,
        CASE WHEN e.id IS NOT NULL THEN 1 ELSE 0 END as enrolled
    FROM courses c
    JOIN users u ON u.id = c.teacher_id
    LEFT JOIN lessons l ON l.course_id = c.id
    LEFT JOIN enrollments e ON e.course_id = c.id AND e.student_id = ?
    GROUP BY c.id
    ORDER BY c.created_at DESC
");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnSpace — Explorer les cours</title>
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
            <a href="/lms/student/dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-indigo-600 text-sm font-medium transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Tableau de bord
            </a>
            <a href="/lms/student/courses.php" class="flex items-center gap-3 px-4 py-3 rounded-xl nav-link active text-sm font-medium">
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
            <h1 class="text-2xl font-bold text-gray-900">Explorer les cours 📚</h1>
            <p class="text-gray-500 mt-1"><?= count($courses) ?> cours disponibles sur la plateforme</p>
        </div>

        <!-- Search -->
        <div class="mb-6">
            <input type="text" id="search" placeholder="Rechercher un cours..."
                class="w-full max-w-md px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                onkeyup="filterCourses()">
        </div>

        <?php if (empty($courses)): ?>
        <div class="bg-white rounded-2xl p-12 text-center shadow-sm border border-gray-100">
            <div class="text-5xl mb-4">📭</div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Aucun cours disponible</h3>
            <p class="text-gray-400">Les enseignants n'ont pas encore publié de cours.</p>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-2 gap-6" id="courses-grid">
            <?php foreach ($courses as $course): ?>
            <div class="course-card bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden lesson-card"
                 data-title="<?= strtolower(htmlspecialchars($course['title'])) ?>">
                <div class="h-3 bg-gradient-to-r <?= $course['enrolled'] ? 'from-green-400 to-emerald-500' : 'from-indigo-500 to-purple-500' ?>"></div>
                <div class="p-6">
                    <div class="flex items-start justify-between mb-3">
                        <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($course['title']) ?></h3>
                        <?php if ($course['enrolled']): ?>
                        <span class="text-xs bg-green-50 text-green-600 px-2 py-1 rounded-lg font-medium flex-shrink-0 ml-2">✓ Inscrit</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-gray-400 text-sm mb-2">Par <?= htmlspecialchars($course['teacher_name']) ?></p>
                    <p class="text-gray-500 text-sm mb-4 line-clamp-2"><?= htmlspecialchars($course['description']) ?></p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400">📖 <?= $course['total_lessons'] ?> leçon(s)</span>
                        <?php if ($course['enrolled']): ?>
                        <a href="/lms/student/learn.php?course_id=<?= $course['id'] ?>"
                           class="bg-green-50 text-green-600 px-4 py-2 rounded-xl text-sm font-medium hover:bg-green-100 transition">
                            Continuer →
                        </a>
                        <?php else: ?>
                        <a href="/lms/student/enroll.php?course_id=<?= $course['id'] ?>"
                           class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl text-sm font-medium hover:bg-indigo-100 transition">
                            S'inscrire
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>
</div>

<script>
function filterCourses() {
    const search = document.getElementById('search').value.toLowerCase();
    document.querySelectorAll('.course-card').forEach(card => {
        const title = card.dataset.title;
        card.style.display = title.includes(search) ? 'block' : 'none';
    });
}
</script>
</body>
</html>

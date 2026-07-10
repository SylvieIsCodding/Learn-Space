<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('teacher');
$user = currentUser();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $teacher_id = $user['id'];

    if (empty($title)) {
        $error = 'Le titre est obligatoire.';
    } else {
        $stmt = $conn->prepare("INSERT INTO courses (title, description, teacher_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $title, $description, $teacher_id);
        if ($stmt->execute()) {
            $course_id = $conn->insert_id;
            header("Location: /lms/teacher/lessons.php?course_id=$course_id&new=1");
            exit();
        } else {
            $error = 'Erreur lors de la création du cours.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnSpace — Créer un cours</title>
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
            <a href="/lms/teacher/dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-indigo-600 text-sm font-medium transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Tableau de bord
            </a>
            <a href="/lms/teacher/create_course.php" class="flex items-center gap-3 px-4 py-3 rounded-xl nav-link active text-sm font-medium">
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

    <!-- Main -->
    <main class="flex-1 p-8">
        <div class="max-w-2xl mx-auto">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Créer un nouveau cours</h1>
                <p class="text-gray-500 mt-1">Remplissez les informations de base de votre cours</p>
            </div>

            <?php if ($error): ?>
            <div class="bg-red-50 text-red-600 px-4 py-3 rounded-lg mb-4 text-sm"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <form method="POST">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Titre du cours *</label>
                        <input type="text" name="title" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                            placeholder="ex: Introduction à Python">
                    </div>
                    <div class="mb-8">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="4"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition resize-none"
                            placeholder="Décrivez votre cours en quelques phrases..."></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl transition">
                            Créer le cours et ajouter des leçons →
                        </button>
                        <a href="/lms/teacher/dashboard.php"
                            class="px-6 bg-gray-100 hover:bg-gray-200 text-gray-600 font-medium py-3 rounded-xl transition text-center">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>

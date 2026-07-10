<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('teacher');
$user = currentUser();

$course_id = intval($_GET['course_id'] ?? 0);
if (!$course_id) {
    header('Location: /lms/teacher/dashboard.php');
    exit();
}

// Verify course belongs to teacher
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ? AND teacher_id = ?");
$stmt->bind_param("ii", $course_id, $user['id']);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
if (!$course) {
    header('Location: /lms/teacher/dashboard.php');
    exit();
}

$error = '';
$success = '';

// Handle lesson upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_lesson') {
    $title = trim($_POST['title']);
    $content_type = $_POST['content_type'];
    $order_num = intval($_POST['order_num'] ?? 1);

    if (empty($title)) {
        $error = 'Le titre est obligatoire.';
    } elseif (!isset($_FILES['content_file']) || $_FILES['content_file']['error'] !== 0) {
        $error = 'Veuillez sélectionner un fichier.';
    } else {
        $file = $_FILES['content_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        $allowed_pdf = ['pdf'];
        $allowed_video = ['mp4', 'avi', 'mov', 'mkv'];

        $valid = ($content_type === 'pdf' && in_array($ext, $allowed_pdf)) ||
                 ($content_type === 'video' && in_array($ext, $allowed_video));

        if (!$valid) {
            $error = 'Type de fichier invalide.';
        } elseif ($file['size'] > 100 * 1024 * 1024) {
            $error = 'Fichier trop volumineux (max 100MB).';
        } else {
            $upload_dir = "/opt/lampp/htdocs/lms/assets/uploads/{$content_type}s/";
            $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
            $filepath = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $content_path = "/lms/assets/uploads/{$content_type}s/$filename";
                $stmt = $conn->prepare("INSERT INTO lessons (course_id, title, content_type, content_path, order_num) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("isssi", $course_id, $title, $content_type, $content_path, $order_num);
                if ($stmt->execute()) {
                    $success = 'Leçon ajoutée avec succès!';
                } else {
                    $error = 'Erreur lors de l\'enregistrement.';
                }
            } else {
                $error = 'Erreur lors de l\'upload du fichier.';
            }
        }
    }
}

// Get lessons
$stmt = $conn->prepare("SELECT l.*, COUNT(q.id) as quiz_count FROM lessons l LEFT JOIN quizzes q ON q.lesson_id = l.id WHERE l.course_id = ? GROUP BY l.id ORDER BY l.order_num ASC");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$lessons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnSpace — Leçons</title>
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
            <a href="/lms/teacher/create_course.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-indigo-600 text-sm font-medium transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouveau cours
            </a>
            <a href="/lms/teacher/lessons.php" class="flex items-center gap-3 px-4 py-3 rounded-xl nav-link active text-sm font-medium">
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
        <div class="mb-8 flex items-center justify-between">
            <div>
                <a href="/lms/teacher/dashboard.php" class="text-indigo-600 text-sm hover:underline">← Retour</a>
                <h1 class="text-2xl font-bold text-gray-900 mt-1"><?= htmlspecialchars($course['title']) ?></h1>
                <p class="text-gray-500 mt-1">Gérez les leçons de ce cours</p>
            </div>
        </div>

        <?php if ($error): ?>
        <div class="bg-red-50 text-red-600 px-4 py-3 rounded-lg mb-4 text-sm"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="bg-green-50 text-green-600 px-4 py-3 rounded-lg mb-4 text-sm"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-5 gap-8">
            <!-- Add Lesson Form -->
            <div class="col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-8">
                    <h2 class="font-semibold text-gray-800 mb-4">Ajouter une leçon</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_lesson">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
                            <input type="text" name="title" required
                                class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type de contenu</label>
                            <select name="content_type" id="content_type" required
                                class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm bg-white"
                                onchange="updateFileAccept(this.value)">
                                <option value="pdf">📄 Document PDF</option>
                                <option value="video">🎬 Vidéo</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ordre</label>
                            <input type="number" name="order_num" value="<?= count($lessons) + 1 ?>" min="1"
                                class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fichier *</label>
                            <div class="upload-zone rounded-xl p-4 text-center cursor-pointer" onclick="document.getElementById('file_input').click()">
                                <div class="text-2xl mb-1">📁</div>
                                <p class="text-sm text-indigo-600 font-medium">Cliquer pour choisir</p>
                                <p class="text-xs text-gray-400 mt-1" id="file_hint">PDF uniquement (max 100MB)</p>
                                <p class="text-xs text-gray-600 mt-2 font-medium" id="file_name">Aucun fichier</p>
                            </div>
                            <input type="file" id="file_input" name="content_file" class="hidden" accept=".pdf"
                                onchange="document.getElementById('file_name').textContent = this.files[0]?.name || 'Aucun fichier'">
                        </div>
                        <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl transition text-sm">
                            Ajouter la leçon
                        </button>
                    </form>
                </div>
            </div>

            <!-- Lessons List -->
            <div class="col-span-3">
                <h2 class="font-semibold text-gray-800 mb-4">Leçons (<?= count($lessons) ?>)</h2>
                <?php if (empty($lessons)): ?>
                <div class="bg-white rounded-2xl p-10 text-center shadow-sm border border-gray-100">
                    <div class="text-4xl mb-3">📝</div>
                    <p class="text-gray-500">Aucune leçon pour l'instant. Ajoutez votre première leçon!</p>
                </div>
                <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($lessons as $lesson): ?>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 lesson-card">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600 font-bold text-sm">
                                    <?= $lesson['order_num'] ?>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($lesson['title']) ?></h3>
                                    <span class="text-xs text-gray-400">
                                        <?= $lesson['content_type'] === 'pdf' ? '📄 PDF' : '🎬 Vidéo' ?>
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs bg-purple-50 text-purple-600 px-2 py-1 rounded-lg">
                                    <?= $lesson['quiz_count'] ?> quiz
                                </span>
                                <a href="/lms/teacher/quiz.php?lesson_id=<?= $lesson['id'] ?>"
                                   class="text-xs bg-indigo-50 text-indigo-600 px-3 py-1 rounded-lg hover:bg-indigo-100 transition">
                                    + Quiz
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
function updateFileAccept(type) {
    const input = document.getElementById('file_input');
    const hint = document.getElementById('file_hint');
    if (type === 'pdf') {
        input.accept = '.pdf';
        hint.textContent = 'PDF uniquement (max 100MB)';
    } else {
        input.accept = '.mp4,.avi,.mov,.mkv';
        hint.textContent = 'MP4, AVI, MOV, MKV (max 100MB)';
    }
}
</script>

</body>
</html>

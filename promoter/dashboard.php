<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('promoter');
$user = currentUser();

$error = '';
$success = '';

// Handle module creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_module') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        if ($title) {
            $stmt = $conn->prepare("INSERT INTO modules (title, description, promoter_id) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $title, $description, $user['id']);
            $stmt->execute() ? $success = 'Module créé!' : $error = 'Erreur.';
        }
    } elseif ($_POST['action'] === 'issue_certificate') {
        $student_id = intval($_POST['student_id']);
        $module_id = intval($_POST['module_id']);
        $stmt = $conn->prepare("INSERT IGNORE INTO certificates (student_id, module_id, issued_by) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $student_id, $module_id, $user['id']);
        $stmt->execute() ? $success = 'Certificat émis!' : $error = 'Erreur.';
    }
}

// Get modules
$stmt = $conn->prepare("SELECT m.*, COUNT(DISTINCT c.id) as course_count FROM modules m LEFT JOIN courses c ON c.module_id = m.id WHERE m.promoter_id = ? GROUP BY m.id");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$modules = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get all students
$stmt = $conn->prepare("SELECT * FROM users WHERE role = 'student' ORDER BY name");
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get all courses
$stmt = $conn->prepare("SELECT c.*, u.name as teacher_name FROM courses c JOIN users u ON u.id = c.teacher_id ORDER BY c.title");
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnSpace — Promoteur</title>
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
                    <p class="text-indigo-300 text-xs">Promoteur</p>
                </div>
            </div>
        </div>
        <nav class="flex-1 p-4 space-y-1">
            <a href="/lms/promoter/dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl nav-link active text-sm font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Tableau de bord
            </a>
	    <a href="/lms/promoter/assign_course.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-indigo-600 text-sm font-medium transition">
    		<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
    		Assigner des cours
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
            <p class="text-gray-500 mt-1">Gérez les modules et les certificats</p>
        </div>

        <?php if ($error): ?>
        <div class="bg-red-50 text-red-600 px-4 py-3 rounded-lg mb-4 text-sm"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="bg-green-50 text-green-600 px-4 py-3 rounded-lg mb-4 text-sm"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-2 gap-8">
            <!-- Create Module -->
            <div>
                <h2 class="font-semibold text-gray-800 mb-4">Créer un module</h2>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                    <form method="POST">
                        <input type="hidden" name="action" value="create_module">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Titre du module *</label>
                            <input type="text" name="title" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="3"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm resize-none"></textarea>
                        </div>
                        <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl transition text-sm">
                            Créer le module
                        </button>
                    </form>
                </div>

                <!-- Modules List -->
                <h2 class="font-semibold text-gray-800 mb-4">Mes modules (<?= count($modules) ?>)</h2>
                <div class="space-y-3">
                    <?php foreach ($modules as $module): ?>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($module['title']) ?></h3>
                        <p class="text-gray-400 text-sm mt-1"><?= htmlspecialchars($module['description']) ?></p>
                        <span class="text-xs text-indigo-600 mt-2 inline-block">📚 <?= $module['course_count'] ?> cours</span>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($modules)): ?>
                    <div class="bg-white rounded-2xl p-8 text-center shadow-sm border border-gray-100">
                        <p class="text-gray-400 text-sm">Aucun module créé.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Issue Certificate -->
            <div>
                <h2 class="font-semibold text-gray-800 mb-4">Émettre un certificat</h2>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                    <form method="POST">
                        <input type="hidden" name="action" value="issue_certificate">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Étudiant</label>
                            <select name="student_id" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm bg-white">
                                <option value="">Choisir un étudiant</option>
                                <?php foreach ($students as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Module</label>
                            <select name="module_id" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm bg-white">
                                <option value="">Choisir un module</option>
                                <?php foreach ($modules as $m): ?>
                                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit"
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 rounded-xl transition text-sm">
                            🏅 Émettre le certificat
                        </button>
                    </form>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 text-center">
                        <p class="text-3xl font-bold text-indigo-600"><?= count($students) ?></p>
                        <p class="text-sm text-gray-500 mt-1">Étudiants</p>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 text-center">
                        <p class="text-3xl font-bold text-purple-600"><?= count($courses) ?></p>
                        <p class="text-sm text-gray-500 mt-1">Cours total</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>

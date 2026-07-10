<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: /lms/dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error = 'Cet email est déjà utilisé.';
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashed, $role);
        if ($stmt->execute()) {
            $success = 'Compte créé avec succès! Vous pouvez vous connecter.';
        } else {
            $error = 'Erreur lors de la création du compte.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnSpace — Inscription</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/lms/assets/css/style.css">
</head>
<body class="bg-gradient-to-br from-indigo-50 to-purple-50 min-h-screen flex items-center justify-center py-10">

<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-600 rounded-2xl mb-4 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900">LearnSpace</h1>
        <p class="text-gray-500 mt-1">Créez votre compte</p>
    </div>

    <div class="bg-white rounded-2xl shadow-xl p-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Inscription</h2>

        <?php if ($error): ?>
        <div class="bg-red-50 text-red-600 px-4 py-3 rounded-lg mb-4 text-sm">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="bg-green-50 text-green-600 px-4 py-3 rounded-lg mb-4 text-sm">
            <?= htmlspecialchars($success) ?>
            <a href="/lms/login.php" class="font-semibold underline ml-1">Se connecter</a>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
                <input type="text" name="name" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                    placeholder="Marie Dupont">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                    placeholder="vous@exemple.com">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                    placeholder="••••••••">
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Je suis</label>
                <select name="role" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition bg-white">
                    <option value="">Choisir un rôle</option>
                    <option value="student">Étudiant</option>
                    <option value="teacher">Enseignant</option>
                    <option value="promoter">Promoteur</option>
                </select>
            </div>
            <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl transition duration-200 shadow-md">
                Créer mon compte
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Déjà un compte ?
            <a href="/lms/login.php" class="text-indigo-600 font-medium hover:underline">Se connecter</a>
        </p>
    </div>
</div>

</body>
</html>

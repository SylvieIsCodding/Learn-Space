<?php
require_once 'includes/auth.php';
if (isLoggedIn()) {
    header('Location: /lms/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnSpace — Plateforme d'apprentissage</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/lms/assets/css/style.css">
</head>
<body class="bg-white">

<!-- Navbar -->
<nav class="flex items-center justify-between px-12 py-5 border-b border-gray-100">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
        <span class="text-xl font-bold text-gray-900">LearnSpace</span>
    </div>
    <div class="flex items-center gap-4">
        <a href="/lms/login.php" class="text-gray-600 hover:text-gray-900 font-medium text-sm transition">Se connecter</a>
        <a href="/lms/register.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium text-sm transition">
            Commencer gratuitement
        </a>
    </div>
</nav>

<!-- Hero -->
<section class="px-12 py-24 max-w-6xl mx-auto">
    <div class="grid grid-cols-2 gap-16 items-center">
        <div>
            <span class="inline-block bg-indigo-50 text-indigo-600 text-xs font-semibold px-3 py-1 rounded-full mb-6">
                🚀 Plateforme LMS nouvelle génération
            </span>
            <h1 class="text-5xl font-bold text-gray-900 leading-tight mb-6">
                Apprenez à votre <span class="text-indigo-600">rythme</span>, progressez à votre <span class="text-purple-600">niveau</span>
            </h1>
            <p class="text-gray-500 text-lg mb-8 leading-relaxed">
                LearnSpace connecte enseignants et étudiants dans un environnement d'apprentissage interactif. 
                Cours en PDF et vidéo, évaluations automatiques, certificats de validation.
            </p>
            <div class="flex gap-4">
                <a href="/lms/register.php" 
                   class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-4 rounded-xl font-semibold transition shadow-lg shadow-indigo-200">
                    Créer un compte →
                </a>
                <a href="/lms/login.php"
                   class="border border-gray-200 hover:border-indigo-300 text-gray-700 px-8 py-4 rounded-xl font-semibold transition">
                    Se connecter
                </a>
            </div>
        </div>
        <div class="relative">
            <!-- Dashboard Preview Card -->
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
                <div class="bg-indigo-700 px-6 py-4 flex items-center gap-3">
                    <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center text-white font-bold text-sm">M</div>
                    <div>
                        <p class="text-white text-sm font-medium">Marie Dupont</p>
                        <p class="text-indigo-300 text-xs">Étudiante</p>
                    </div>
                </div>
                <div class="p-6">
                    <p class="text-xs text-gray-400 mb-3 font-medium uppercase tracking-wide">Mes cours en cours</p>
                    <div class="space-y-3">
                        <div class="border border-gray-100 rounded-xl p-4">
                            <p class="font-semibold text-gray-800 text-sm mb-2">Introduction au HTML/CSS</p>
                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                <span>Progression</span><span class="text-indigo-600 font-bold">75%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-1.5">
                                <div class="bg-indigo-600 h-1.5 rounded-full" style="width: 75%"></div>
                            </div>
                        </div>
                        <div class="border border-gray-100 rounded-xl p-4">
                            <p class="font-semibold text-gray-800 text-sm mb-2">Python pour débutants</p>
                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                <span>Progression</span><span class="text-green-600 font-bold">100%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-1.5">
                                <div class="bg-green-500 h-1.5 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Floating badge -->
            <div class="absolute -top-4 -right-4 bg-green-500 text-white px-4 py-2 rounded-xl text-sm font-semibold shadow-lg">
                🏅 Certificat obtenu!
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section class="bg-gray-50 px-12 py-20">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Tout ce dont vous avez besoin</h2>
            <p class="text-gray-500">Une plateforme complète pour enseignants, étudiants et promoteurs</p>
        </div>
        <div class="grid grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-4 text-2xl">👨‍🏫</div>
                <h3 class="font-bold text-gray-900 mb-2">Enseignants</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Créez des cours structurés en leçons PDF ou vidéo. Ajoutez des évaluations après chaque leçon pour mesurer la compréhension.</p>
            </div>
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-4 text-2xl">👨‍🎓</div>
                <h3 class="font-bold text-gray-900 mb-2">Étudiants</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Suivez vos cours à votre rythme. Visualisez votre progression en temps réel et obtenez des certificats de validation de modules.</p>
            </div>
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-4 text-2xl">🏢</div>
                <h3 class="font-bold text-gray-900 mb-2">Promoteurs</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Organisez les cours en modules cohérents. Émettez des certificats officiels aux étudiants qui valident leurs modules.</p>
            </div>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="px-12 py-16 max-w-6xl mx-auto">
    <div class="grid grid-cols-3 gap-8 text-center">
        <div>
            <p class="text-4xl font-bold text-indigo-600 mb-2">PDF & Vidéo</p>
            <p class="text-gray-500">Formats de cours supportés</p>
        </div>
        <div>
            <p class="text-4xl font-bold text-purple-600 mb-2">100%</p>
            <p class="text-gray-500">Progression en temps réel</p>
        </div>
        <div>
            <p class="text-4xl font-bold text-green-600 mb-2">🏅</p>
            <p class="text-gray-500">Certificats de validation</p>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="bg-gradient-to-br from-indigo-600 to-purple-600 px-12 py-20 text-center text-white">
    <h2 class="text-3xl font-bold mb-4">Prêt à commencer?</h2>
    <p class="text-indigo-200 mb-8 text-lg">Rejoignez LearnSpace et transformez votre façon d'apprendre.</p>
    <a href="/lms/register.php"
       class="inline-block bg-white text-indigo-600 hover:bg-indigo-50 px-10 py-4 rounded-xl font-bold transition shadow-lg">
        Créer mon compte gratuitement →
    </a>
</section>

<!-- Footer -->
<footer class="px-12 py-8 border-t border-gray-100 text-center text-gray-400 text-sm">
    <p>© 2026 LearnSpace — Plateforme d'apprentissage en ligne</p>
</footer>

</body>
</html>

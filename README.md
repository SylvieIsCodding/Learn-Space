# 📚 LearnSpace  Plateforme LMS

> Plateforme d'apprentissage en ligne (LMS) développée dans le cadre du cours INF222:Programmation Web.
>
> > ⚠️ **Projet en cours de développement** — Des fonctionnalités sont encore en cours d'ajout.

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-10.4-4479A1?style=flat&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-CDN-06B6D4?style=flat&logo=tailwindcss&logoColor=white)
![XAMPP](https://img.shields.io/badge/XAMPP-8.2-FB7A24?style=flat&logo=xampp&logoColor=white)

---

## 📋 Description

**LearnSpace** est une plateforme LMS (Learning Management System) permettant à trois types d'utilisateurs d'interagir :

- 👨‍🏫 **Enseignant** — Crée des cours structurés en leçons (PDF ou vidéo) et ajoute des évaluations après chaque leçon.
- 👨‍🎓 **Étudiant** — Suit les cours, passe les évaluations et suit sa progression en pourcentage.
- 🏢 **Promoteur** — Organise les cours en modules et délivre des certificats de validation aux étudiants.

---

## 🛠️ Technologies utilisées

| Technologie | Rôle |
|---|---|
| **PHP 8.2** | Backend — logique métier, sessions, gestion des fichiers |
| **MySQL / MariaDB** | Base de données relationnelle |
| **HTML5 / CSS3** | Structure et style des pages |
| **JavaScript / Ajax** | Interactions dynamiques (soumission de quiz sans rechargement) |
| **Tailwind CSS** | Framework CSS utilitaire (via CDN) |
| **XAMPP** | Serveur local Apache + MySQL |

---

## 🗂️ Structure du projet

```
lms/
├── index.php                  # Page d'accueil (landing page)
├── login.php                  # Connexion
├── register.php               # Inscription
├── dashboard.php              # Routeur de rôles
├── logout.php                 # Déconnexion
├── unauthorized.php           # Page d'accès refusé
│
├── includes/
│   ├── db.php                 # Connexion à la base de données
│   └── auth.php               # Fonctions d'authentification et sessions
│
├── teacher/
│   ├── dashboard.php          # Tableau de bord enseignant
│   ├── create_course.php      # Création de cours
│   ├── lessons.php            # Gestion des leçons (upload PDF/vidéo)
│   └── quiz.php               # Gestion des questions d'évaluation
│
├── student/
│   ├── dashboard.php          # Tableau de bord étudiant
│   ├── courses.php            # Explorer les cours disponibles
│   ├── learn.php              # Visualisation leçon + évaluation
│   ├── enroll.php             # Inscription à un cours
│   ├── submit_quiz.php        # Endpoint Ajax soumission quiz
│   └── certificates.php      # Mes certificats
│
├── promoter/
│   ├── dashboard.php          # Tableau de bord promoteur
│   └── assign_course.php      # Assignation cours → modules
│
└── assets/
    ├── css/
    │   └── style.css          # Styles personnalisés
    └── uploads/
        ├── pdfs/              # Fichiers PDF uploadés
        └── videos/            # Fichiers vidéo uploadés
```

---

## ⚙️ Installation et configuration

### Prérequis

- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP 8.2)
- Navigateur web moderne

### Étapes d'installation

**1. Cloner le projet dans le dossier htdocs**

```bash
cd /opt/lampp/htdocs
git clone https://github.com/votre-username/learnspace.git lms
```

**2. Démarrer XAMPP**

```bash
sudo /opt/lampp/lampp start
```

**3. Créer la base de données**

- Ouvrir [phpMyAdmin](http://localhost/phpmyadmin)
- Créer une base de données nommée `lms_db` avec collation `utf8mb4_unicode_ci`
- Importer le fichier `database/lms_db.sql`

**4. Configurer la connexion**

Vérifier les paramètres dans `includes/db.php` :

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');        // Mot de passe root XAMPP (vide par défaut)
define('DB_NAME', 'lms_db');
```

**5. Permissions des dossiers d'upload**

```bash
sudo chmod -R 777 /opt/lampp/htdocs/lms/assets/uploads/
```

**6. Accéder à l'application**

```
http://localhost/lms/
```

---

## 🚀 Utilisation

### Créer un compte

Rendez-vous sur `http://localhost/lms/register.php` et choisissez votre rôle :
- **Étudiant** — pour suivre des cours
- **Enseignant** — pour créer des cours
- **Promoteur** — pour gérer les modules et certificats

### Flux Enseignant

1. Se connecter → Tableau de bord enseignant
2. Cliquer **Nouveau cours** → Remplir titre et description
3. Ajouter des leçons (PDF ou vidéo, max 100MB)
4. Ajouter des questions de quiz après chaque leçon

### Flux Étudiant

1. Se connecter → Explorer les cours
2. S'inscrire à un cours
3. Suivre les leçons (PDF dans iframe, vidéo en lecture directe)
4. Passer l'évaluation → Score calculé automatiquement
5. Progression mise à jour en temps réel (%)

### Flux Promoteur

1. Se connecter → Créer des modules
2. Assigner des cours aux modules
3. Émettre des certificats aux étudiants ayant validé un module

---

## ✨ Fonctionnalités clés

- 🔐 **Authentification sécurisée** — Sessions PHP + mots de passe hachés (bcrypt)
- 📄 **Support PDF et vidéo** — Visualisation directe dans le navigateur
- ⚡ **Ajax** — Soumission du quiz et affichage du score sans rechargement de page
- 📊 **Progression en temps réel** — Pourcentage calculé selon les scores obtenus
- 🏅 **Certificats** — Délivrés par le promoteur aux étudiants qualifiés
- 🔒 **Contrôle d'accès par rôle** — Chaque rôle n'accède qu'à ses propres pages
- 🛡️ **Requêtes préparées** — Protection contre les injections SQL

---
## 🤝 Contributions

Ce projet est en cours de développement actif. Les contributions sont les bienvenues !

Si vous souhaitez contribuer :

1. Forkez le projet
2. Créez une branche pour votre fonctionnalité (`git checkout -b feature/ma-fonctionnalite`)
3. Committez vos changements (`git commit -m 'Ajout de ma fonctionnalité'`)
4. Poussez vers la branche (`git push origin feature/ma-fonctionnalite`)
5. Ouvrez une Pull Request

N'hésitez pas à ouvrir une **Issue** pour signaler un bug ou proposer une amélioration.


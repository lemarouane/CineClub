<?php
// Configuration de la connexion à la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'cineclub');
define('DB_USER', 'root');
define('DB_PASS', ''); // Mot de passe vide pour XAMPP/WAMP par défaut

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Démarrer la session
session_start();

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fonction pour vérifier si l'utilisateur est admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Fonction pour rediriger
function redirect($url) {
    header("Location: $url");
    exit();
}

// Fonction pour nettoyer les données
function cleanInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>
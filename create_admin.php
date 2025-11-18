<?php
require_once 'config/database.php';

// Ce script crée un compte administrateur avec un mot de passe hashé correctement

$email = 'admin@cineclub.com';
$password = 'admin123';
$nom = 'Admin';
$prenom = 'CineClub';
$role = 'admin';

// Générer le hash du mot de passe
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Vérifier si l'admin existe déjà
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$existing = $stmt->fetch();

if ($existing) {
    // Mettre à jour le mot de passe
    $stmt = $pdo->prepare("UPDATE users SET password = ?, role = ? WHERE email = ?");
    $stmt->execute([$password_hash, $role, $email]);
    echo "<h2 style='color: green;'>✅ Compte admin mis à jour avec succès !</h2>";
} else {
    // Créer un nouveau compte
    $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nom, $prenom, $email, $password_hash, $role]);
    echo "<h2 style='color: green;'>✅ Compte admin créé avec succès !</h2>";
}

echo "<div style='font-family: Arial; padding: 20px;'>";
echo "<h3>Informations de connexion :</h3>";
echo "<p><strong>Email:</strong> $email</p>";
echo "<p><strong>Mot de passe:</strong> $password</p>";
echo "<p><a href='login.php' style='background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>→ Se connecter maintenant</a></p>";
echo "<hr>";
echo "<p style='color: #e74c3c;'><strong>⚠️ IMPORTANT:</strong> Supprimez ce fichier après utilisation pour des raisons de sécurité !</p>";
echo "</div>";
?>
<?php
require_once 'config/database.php';

echo "<div style='font-family: Arial; padding: 20px; background: #f5f5f5;'>";
echo "<h2>🔍 Diagnostic de Session</h2>";

if (isLoggedIn()) {
    echo "<div style='background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;'>";
    echo "<h3 style='color: green;'>✅ Vous êtes connecté !</h3>";
    echo "<table style='width: 100%; border-collapse: collapse;'>";
    echo "<tr><td style='padding: 10px; border-bottom: 1px solid #ddd;'><strong>User ID:</strong></td><td style='padding: 10px; border-bottom: 1px solid #ddd;'>" . $_SESSION['user_id'] . "</td></tr>";
    echo "<tr><td style='padding: 10px; border-bottom: 1px solid #ddd;'><strong>Nom:</strong></td><td style='padding: 10px; border-bottom: 1px solid #ddd;'>" . $_SESSION['nom'] . "</td></tr>";
    echo "<tr><td style='padding: 10px; border-bottom: 1px solid #ddd;'><strong>Prénom:</strong></td><td style='padding: 10px; border-bottom: 1px solid #ddd;'>" . $_SESSION['prenom'] . "</td></tr>";
    echo "<tr><td style='padding: 10px; border-bottom: 1px solid #ddd;'><strong>Email:</strong></td><td style='padding: 10px; border-bottom: 1px solid #ddd;'>" . $_SESSION['email'] . "</td></tr>";
    echo "<tr><td style='padding: 10px; border-bottom: 1px solid #ddd;'><strong>Rôle:</strong></td><td style='padding: 10px; border-bottom: 1px solid #ddd;'><span style='background: " . ($_SESSION['role'] === 'admin' ? '#27ae60' : '#e74c3c') . "; color: white; padding: 5px 10px; border-radius: 4px;'>" . $_SESSION['role'] . "</span></td></tr>";
    echo "</table>";
    
    if (isAdmin()) {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
        echo "<strong>🎉 Vous êtes administrateur !</strong>";
        echo "<p><a href='admin/index.php' style='background: #27ae60; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 10px;'>→ Accéder au tableau de bord admin</a></p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
        echo "<strong>❌ Vous n'êtes PAS administrateur</strong>";
        echo "<p>Vous devez vous connecter avec un compte admin pour accéder à l'administration.</p>";
        echo "</div>";
    }
    
    echo "<p style='margin-top: 20px;'><a href='logout.php' style='background: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Se déconnecter</a></p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 8px;'>";
    echo "<h3>❌ Vous n'êtes pas connecté</h3>";
    echo "<p>Vous devez vous connecter pour accéder à cette page.</p>";
    echo "<p><a href='login.php' style='background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>→ Se connecter</a></p>";
    echo "</div>";
}

// Afficher les utilisateurs admin dans la base de données
echo "<div style='background: white; padding: 20px; border-radius: 8px; margin-top: 20px;'>";
echo "<h3>👥 Comptes administrateurs dans la base de données :</h3>";
$stmt = $pdo->query("SELECT id, nom, prenom, email, role, date_inscription FROM users WHERE role = 'admin'");
$admins = $stmt->fetchAll();

if (empty($admins)) {
    echo "<p style='color: #e74c3c;'>Aucun compte administrateur trouvé dans la base de données !</p>";
} else {
    echo "<table style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #2c3e50; color: white;'><th style='padding: 10px; text-align: left;'>ID</th><th style='padding: 10px; text-align: left;'>Nom</th><th style='padding: 10px; text-align: left;'>Email</th><th style='padding: 10px; text-align: left;'>Date d'inscription</th></tr>";
    foreach ($admins as $admin) {
        echo "<tr style='border-bottom: 1px solid #ddd;'>";
        echo "<td style='padding: 10px;'>" . $admin['id'] . "</td>";
        echo "<td style='padding: 10px;'>" . htmlspecialchars($admin['prenom'] . ' ' . $admin['nom']) . "</td>";
        echo "<td style='padding: 10px;'>" . htmlspecialchars($admin['email']) . "</td>";
        echo "<td style='padding: 10px;'>" . date('d/m/Y H:i', strtotime($admin['date_inscription'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
echo "</div>";

echo "<hr style='margin: 30px 0;'>";
echo "<p style='text-align: center;'><a href='index.php'>← Retour à l'accueil</a></p>";
echo "</div>";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>CineClub</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">🎬 CineClub</a>
            <ul class="nav-links">
                <?php if (isLoggedIn()): ?>
                    <li><a href="dashboard.php">Tableau de bord</a></li>
                    <li><a href="films.php">Films</a></li>
                    <li><a href="seances.php">Séances</a></li>
                    <?php if (isAdmin()): ?>
                        <li><a href="admin/index.php">Administration</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Déconnexion (<?php echo $_SESSION['prenom']; ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php">Connexion</a></li>
                    <li><a href="register.php">Inscription</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <div class="container main-content">
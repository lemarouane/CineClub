<?php
require_once 'config/database.php';

// Si l'utilisateur est connecté, rediriger vers le dashboard
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$page_title = 'Accueil';
include 'includes/header.php';
?>

<div class="card" style="text-align: center; padding: 3rem;">
    <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">🎬 Bienvenue sur CineClub</h1>
    <p style="font-size: 1.2rem; margin-bottom: 2rem; color: #7f8c8d;">
        La plateforme de gestion de votre club de cinéma
    </p>
    
    <div style="margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1rem;">Fonctionnalités</h2>
        <div class="stats-grid" style="text-align: left;">
            <div class="stat-card">
                <h3>📽️</h3>
                <p><strong>Proposez des films</strong></p>
                <p style="font-size: 0.9rem;">Suggérez vos films préférés au club</p>
            </div>
            <div class="stat-card">
                <h3>🗳️</h3>
                <p><strong>Votez</strong></p>
                <p style="font-size: 0.9rem;">Choisissez les prochains films à projeter</p>
            </div>
            <div class="stat-card">
                <h3>🎟️</h3>
                <p><strong>Participez</strong></p>
                <p style="font-size: 0.9rem;">Inscrivez-vous aux séances</p>
            </div>
            <div class="stat-card">
                <h3>⭐</h3>
                <p><strong>Critiquez</strong></p>
                <p style="font-size: 0.9rem;">Partagez vos avis sur les films</p>
            </div>
        </div>
    </div>
    
    <div style="display: flex; gap: 1rem; justify-content: center;">
        <a href="register.php" class="btn btn-success" style="font-size: 1.1rem; padding: 1rem 2rem;">
            Rejoindre le club
        </a>
        <a href="login.php" class="btn" style="font-size: 1.1rem; padding: 1rem 2rem;">
            Se connecter
        </a>
    </div>
</div>

<div class="card">
    <h2>À propos de CineClub</h2>
    <p style="margin-bottom: 1rem;">
        CineClub est une plateforme collaborative qui permet aux passionnés de cinéma de:
    </p>
    <ul style="list-style-position: inside; margin-left: 1rem; line-height: 2;">
        <li>Proposer leurs films favoris à la communauté</li>
        <li>Voter démocratiquement pour les prochaines projections</li>
        <li>S'inscrire facilement aux séances organisées</li>
        <li>Noter et critiquer les films visionnés ensemble</li>
        <li>Découvrir les statistiques et tendances du club</li>
    </ul>
</div>

<?php include 'includes/footer.php'; ?>
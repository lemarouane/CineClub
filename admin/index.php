<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

// Statistiques globales
$stats = [];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$stats['membres'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM films");
$stats['films'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM seances WHERE date_seance >= NOW()");
$stats['seances_avenir'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM reviews");
$stats['reviews'] = $stmt->fetch()['total'];

// Films par statut
$stmt = $pdo->query("
    SELECT statut, COUNT(*) as count
    FROM films
    GROUP BY statut
");
$films_par_statut = $stmt->fetchAll();

// Genres les plus populaires
$stmt = $pdo->query("
    SELECT genre, COUNT(*) as count
    FROM films
    GROUP BY genre
    ORDER BY count DESC
    LIMIT 5
");
$genres_populaires = $stmt->fetchAll();

// Films les mieux notés
$stmt = $pdo->query("
    SELECT f.titre, f.realisateur, AVG(r.note) as avg_note, COUNT(r.id) as nb_reviews
    FROM films f
    JOIN reviews r ON f.id = r.film_id
    GROUP BY f.id
    HAVING COUNT(r.id) >= 2
    ORDER BY avg_note DESC
    LIMIT 5
");
$top_films = $stmt->fetchAll();

// Derniers membres
$stmt = $pdo->query("
    SELECT prenom, nom, email, date_inscription
    FROM users
    ORDER BY date_inscription DESC
    LIMIT 5
");
$derniers_membres = $stmt->fetchAll();

$page_title = 'Administration';
include '../includes/header.php';
?>
<link rel="stylesheet" href="../css/style.css">

<h1 style="margin-bottom: 2rem;">⚙️ Administration du CineClub</h1>

<!-- Navigation admin -->
<div class="card">
    <h2>Menu d'administration</h2>
    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
        <a href="manage_films.php" class="btn">📽️ Gérer les films</a>
        <a href="manage_seances.php" class="btn">🎟️ Gérer les séances</a>
    </div>
</div>

<!-- Statistiques générales -->
<div class="stats-grid">
    <div class="stat-card">
        <h3><?php echo $stats['membres']; ?></h3>
        <p>Membres</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $stats['films']; ?></h3>
        <p>Films au catalogue</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $stats['seances_avenir']; ?></h3>
        <p>Séances à venir</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $stats['reviews']; ?></h3>
        <p>Critiques publiées</p>
    </div>
</div>

<!-- Charts container -->
<div class="card" style="display: flex; gap: 2rem; flex-wrap: wrap; justify-content: center; height: 25em;">
    
    <!-- Graph: Films par statut -->
    <div style="flex: 1 1 400px; max-width: 400px; height: 300px;">
        <h2>Répartition des films par statut</h2>
        <canvas id="filmsStatusChart" style="width: 100%; height: 100%;"></canvas>
    </div>

    <!-- Graph: Top 5 des genres -->
    <div style="flex: 1 1 400px; max-width: 400px; height: 300px;">
        <h2>Top 5 des genres</h2>
        <canvas id="genresChart" style="width: 100%; height: 100%;"></canvas>
    </div>

</div>


<!-- Films les mieux notés -->
<?php if (!empty($top_films)): ?>
<div class="card">
    <h2>Films les mieux notés</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Film</th>
                <th>Note moyenne</th>
                <th>Critiques</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($top_films as $film): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($film['titre']); ?></strong><br>
                        <small><?php echo htmlspecialchars($film['realisateur']); ?></small>
                    </td>
                    <td>
                        <span class="rating">
                            <?php
                            $avg = round($film['avg_note']);
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $avg ? '★' : '☆';
                            }
                            ?>
                        </span>
                        <br>
                        <small><?php echo number_format($film['avg_note'], 1); ?> / 5</small>
                    </td>
                    <td><?php echo $film['nb_reviews']; ?> avis</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Derniers membres -->
<div class="card">
    <h2>Derniers membres inscrits</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Date d'inscription</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($derniers_membres as $membre): ?>
                <tr>
                    <td><?php echo htmlspecialchars($membre['prenom'] . ' ' . $membre['nom']); ?></td>
                    <td><?php echo htmlspecialchars($membre['email']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($membre['date_inscription'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Films par statut (doughnut)
const filmsStatusCtx = document.getElementById('filmsStatusChart').getContext('2d');
new Chart(filmsStatusCtx, {
    type: 'doughnut',
    data: {
        labels: [<?php foreach ($films_par_statut as $stat) { echo '"' . ucfirst(str_replace('_',' ',$stat['statut'])) . '",'; } ?>],
        datasets: [{
            data: [<?php foreach ($films_par_statut as $stat) { echo $stat['count'] . ','; } ?>],
            backgroundColor: ['#3498db','#16a085','#f39c12','#e74c3c']
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

// Top genres (bar)
const genresCtx = document.getElementById('genresChart').getContext('2d');
new Chart(genresCtx, {
    type: 'bar',
    data: {
        labels: [<?php foreach ($genres_populaires as $genre) { echo '"' . htmlspecialchars($genre['genre']) . '",'; } ?>],
        datasets: [{
            label: 'Nombre de films',
            data: [<?php foreach ($genres_populaires as $genre) { echo $genre['count'] . ','; } ?>],
            backgroundColor: '#3498db'
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>

<?php include '../includes/footer.php'; ?>

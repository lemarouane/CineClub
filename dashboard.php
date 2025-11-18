<?php
require_once 'config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Statistiques
$stmt = $pdo->query("SELECT COUNT(*) as total FROM films");
$total_films = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM films WHERE statut = 'en_vote'");
$films_en_vote = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM seances WHERE date_seance >= NOW()");
$prochaines_seances = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM votes WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$mes_votes = $stmt->fetch()['total'];

// Prochaines séances
$stmt = $pdo->query("
    SELECT s.*, f.titre, f.realisateur, f.annee, 
           (SELECT COUNT(*) FROM participations WHERE seance_id = s.id) as inscrits
    FROM seances s
    JOIN films f ON s.film_id = f.id
    WHERE s.date_seance >= NOW()
    ORDER BY s.date_seance ASC
    LIMIT 3
");
$prochaines_seances_list = $stmt->fetchAll();

// Films en vote
$stmt = $pdo->query("
    SELECT f.*, u.prenom, u.nom,
           (SELECT COUNT(*) FROM votes WHERE film_id = f.id) as total_votes,
           (SELECT COUNT(*) FROM votes WHERE film_id = f.id AND user_id = {$_SESSION['user_id']}) as user_voted
    FROM films f
    JOIN users u ON f.user_id = u.id
    WHERE f.statut = 'en_vote'
    ORDER BY total_votes DESC
    LIMIT 4
");
$films_vote = $stmt->fetchAll();

$page_title = 'Tableau de bord';
include 'includes/header.php';
?>

<h1 style="margin-bottom: 2rem;">Tableau de bord</h1>

<!-- Statistiques -->
<div class="stats-grid">
    <div class="stat-card">
        <h3><?php echo $total_films; ?></h3>
        <p>Films au catalogue</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $films_en_vote; ?></h3>
        <p>Films en vote</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $prochaines_seances; ?></h3>
        <p>Séances à venir</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $mes_votes; ?></h3>
        <p>Mes votes</p>
    </div>
</div>

<!-- Prochaines séances -->
<div class="card">
    <h2>🎟️ Prochaines séances</h2>
    <?php if (empty($prochaines_seances_list)): ?>
        <p>Aucune séance programmée pour le moment.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Film</th>
                    <th>Date</th>
                    <th>Lieu</th>
                    <th>Places</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($prochaines_seances_list as $seance): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($seance['titre']); ?></strong><br>
                            <small><?php echo htmlspecialchars($seance['realisateur']); ?> (<?php echo $seance['annee']; ?>)</small>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($seance['date_seance'])); ?></td>
                        <td><?php echo htmlspecialchars($seance['lieu']); ?></td>
                        <td><?php echo $seance['places_restantes']; ?> / <?php echo $seance['capacite_max']; ?></td>
                        <td>
                            <a href="seances.php?action=register&id=<?php echo $seance['id']; ?>" class="btn btn-success">S'inscrire</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="seances.php" class="btn">Voir toutes les séances</a>
    <?php endif; ?>
</div>

<!-- Films en vote -->
<div class="card">
    <h2>🗳️ Films en cours de vote</h2>
    <?php if (empty($films_vote)): ?>
        <p>Aucun film en vote actuellement.</p>
    <?php else: ?>
        <div class="films-grid">
            <?php foreach ($films_vote as $film): ?>
                <div class="film-card">
                    <h3><?php echo htmlspecialchars($film['titre']); ?></h3>
                    <p class="film-meta">
                        <?php echo htmlspecialchars($film['realisateur']); ?> (<?php echo $film['annee']; ?>)<br>
                        <span class="badge badge-vote"><?php echo $film['genre']; ?></span>
                    </p>
                    <p class="film-synopsis">
                        <?php echo substr(htmlspecialchars($film['synopsis']), 0, 100); ?>...
                    </p>
                    <p><strong>Votes: <?php echo $film['total_votes']; ?></strong></p>
                    <?php if ($film['user_voted'] > 0): ?>
                        <span class="badge badge-visionne">✓ Vous avez voté</span>
                    <?php else: ?>
                        <a href="vote.php?film_id=<?php echo $film['id']; ?>" class="btn btn-success">Voter</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <a href="films.php" class="btn" style="margin-top: 1rem;">Voir tous les films</a>
    <?php endif; ?>
</div>

<!-- Actions rapides -->
<div class="card">
    <h2>🚀 Actions rapides</h2>
    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
        <a href="propose_film.php" class="btn btn-success">Proposer un film</a>
        <a href="films.php" class="btn">Parcourir le catalogue</a>
        <a href="seances.php" class="btn">Voir les séances</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
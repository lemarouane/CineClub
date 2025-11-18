<?php
require_once 'config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Filtres
$statut_filter = isset($_GET['statut']) ? $_GET['statut'] : 'all';
$genre_filter = isset($_GET['genre']) ? $_GET['genre'] : 'all';

// Récupérer les genres disponibles
$stmt = $pdo->query("SELECT DISTINCT genre FROM films ORDER BY genre");
$genres = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Construction de la requête avec filtres
$query = "SELECT f.*, u.prenom, u.nom,
          (SELECT COUNT(*) FROM votes WHERE film_id = f.id) as total_votes,
          (SELECT COUNT(*) FROM votes WHERE film_id = f.id AND user_id = ?) as user_voted
          FROM films f
          JOIN users u ON f.user_id = u.id
          WHERE 1=1";

$params = [$_SESSION['user_id']];

if ($statut_filter !== 'all') {
    $query .= " AND f.statut = ?";
    $params[] = $statut_filter;
}

if ($genre_filter !== 'all') {
    $query .= " AND f.genre = ?";
    $params[] = $genre_filter;
}

$query .= " ORDER BY f.date_proposition DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$films = $stmt->fetchAll();

$page_title = 'Catalogue de films';
include 'includes/header.php';
?>

<h1 style="margin-bottom: 2rem;">🎬 Catalogue de films</h1>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="margin-bottom: 0;">Tous les films (<?php echo count($films); ?>)</h2>
        </div>
        <div>
            <a href="propose_film.php" class="btn btn-success">+ Proposer un film</a>
        </div>
    </div>
    
    <!-- Filtres -->
    <form method="GET" style="margin-top: 1.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
        <div class="form-group" style="margin-bottom: 0; min-width: 200px;">
            <label for="statut">Statut</label>
            <select name="statut" id="statut" onchange="this.form.submit()">
                <option value="all" <?php echo $statut_filter === 'all' ? 'selected' : ''; ?>>Tous</option>
                <option value="propose" <?php echo $statut_filter === 'propose' ? 'selected' : ''; ?>>Proposés</option>
                <option value="en_vote" <?php echo $statut_filter === 'en_vote' ? 'selected' : ''; ?>>En vote</option>
                <option value="programme" <?php echo $statut_filter === 'programme' ? 'selected' : ''; ?>>Programmés</option>
                <option value="visionne" <?php echo $statut_filter === 'visionne' ? 'selected' : ''; ?>>Visionnés</option>
            </select>
        </div>
        
        <div class="form-group" style="margin-bottom: 0; min-width: 200px;">
            <label for="genre">Genre</label>
            <select name="genre" id="genre" onchange="this.form.submit()">
                <option value="all" <?php echo $genre_filter === 'all' ? 'selected' : ''; ?>>Tous les genres</option>
                <?php foreach ($genres as $genre): ?>
                    <option value="<?php echo $genre; ?>" <?php echo $genre_filter === $genre ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($genre); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <?php if ($statut_filter !== 'all' || $genre_filter !== 'all'): ?>
            <a href="films.php" class="btn btn-secondary" style="align-self: flex-end;">Réinitialiser</a>
        <?php endif; ?>
    </form>
</div>

<?php if (empty($films)): ?>
    <div class="card">
        <p style="text-align: center; color: #7f8c8d;">Aucun film trouvé avec ces critères.</p>
    </div>
<?php else: ?>
    <div class="films-grid">
        <?php foreach ($films as $film): ?>
            <div class="film-card">
                <h3><?php echo htmlspecialchars($film['titre']); ?></h3>
                <p class="film-meta">
                    <strong><?php echo htmlspecialchars($film['realisateur']); ?></strong> (<?php echo $film['annee']; ?>)<br>
                    <span class="badge badge-<?php 
                        echo $film['statut'] === 'propose' ? 'propose' : 
                            ($film['statut'] === 'en_vote' ? 'vote' : 
                            ($film['statut'] === 'programme' ? 'programme' : 'visionne')); 
                    ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $film['statut'])); ?>
                    </span>
                    <span class="badge" style="background-color: #34495e; color: white;">
                        <?php echo htmlspecialchars($film['genre']); ?>
                    </span>
                </p>
                
                <?php if ($film['duree']): ?>
                    <p><small>⏱️ <?php echo $film['duree']; ?> min</small></p>
                <?php endif; ?>
                
                <p class="film-synopsis">
                    <?php echo htmlspecialchars($film['synopsis']); ?>
                </p>
                
                <p style="font-size: 0.9rem; color: #7f8c8d;">
                    Proposé par <?php echo htmlspecialchars($film['prenom'] . ' ' . $film['nom']); ?>
                </p>
                
                <?php if ($film['statut'] === 'en_vote'): ?>
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee;">
                        <p><strong>Votes: <?php echo $film['total_votes']; ?></strong></p>
                        <?php if ($film['user_voted'] > 0): ?>
                            <span class="badge badge-visionne">✓ Vous avez voté</span>
                        <?php else: ?>
                            <a href="vote.php?film_id=<?php echo $film['id']; ?>" class="btn btn-success">Voter pour ce film</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($film['statut'] === 'visionne'): ?>
                    <div style="margin-top: 1rem;">
                        <?php
                        $stmt_avg = $pdo->prepare("SELECT AVG(note) as avg_note, COUNT(*) as nb_reviews FROM reviews WHERE film_id = ?");
                        $stmt_avg->execute([$film['id']]);
                        $rating = $stmt_avg->fetch();
                        
                        if ($rating['nb_reviews'] > 0):
                        ?>
                            <p class="rating">
                                <?php
                                $avg_note = round($rating['avg_note']);
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $avg_note ? '★' : '☆';
                                }
                                ?>
                                <small>(<?php echo $rating['nb_reviews']; ?> avis)</small>
                            </p>
                        <?php endif; ?>
                        <a href="reviews.php?film_id=<?php echo $film['id']; ?>" class="btn">Voir les critiques</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
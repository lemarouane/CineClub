<?php
require_once 'config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$message = '';
$error = '';

// Traitement du vote
if (isset($_GET['film_id'])) {
    $film_id = intval($_GET['film_id']);
    
    // Vérifier que le film existe et est en vote
    $stmt = $pdo->prepare("SELECT * FROM films WHERE id = ? AND statut = 'en_vote'");
    $stmt->execute([$film_id]);
    $film = $stmt->fetch();
    
    if (!$film) {
        $error = "Ce film n'est pas disponible pour le vote.";
    } else {
        // Vérifier si l'utilisateur a déjà voté
        $stmt = $pdo->prepare("SELECT * FROM votes WHERE user_id = ? AND film_id = ?");
        $stmt->execute([$_SESSION['user_id'], $film_id]);
        
        if ($stmt->fetch()) {
            $error = "Vous avez déjà voté pour ce film.";
        } else {
            // Ajouter le vote
            $stmt = $pdo->prepare("INSERT INTO votes (user_id, film_id) VALUES (?, ?)");
            if ($stmt->execute([$_SESSION['user_id'], $film_id])) {
                $message = "Votre vote a été enregistré avec succès !";
            } else {
                $error = "Une erreur est survenue lors de l'enregistrement de votre vote.";
            }
        }
    }
}

// Retirer un vote
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['film_id'])) {
    $film_id = intval($_GET['film_id']);
    
    $stmt = $pdo->prepare("DELETE FROM votes WHERE user_id = ? AND film_id = ?");
    if ($stmt->execute([$_SESSION['user_id'], $film_id])) {
        $message = "Votre vote a été retiré.";
    }
}

$page_title = 'Mes votes';
include 'includes/header.php';
?>

<h1 style="margin-bottom: 2rem;">🗳️ Mes votes</h1>

<?php if ($message): ?>
    <div class="alert alert-success">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<!-- Films pour lesquels l'utilisateur a voté -->
<div class="card">
    <h2>Films que vous avez votés</h2>
    <?php
    $stmt = $pdo->prepare("
        SELECT f.*, v.date_vote,
               (SELECT COUNT(*) FROM votes WHERE film_id = f.id) as total_votes
        FROM votes v
        JOIN films f ON v.film_id = f.id
        WHERE v.user_id = ?
        ORDER BY v.date_vote DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $mes_votes = $stmt->fetchAll();
    
    if (empty($mes_votes)): ?>
        <p>Vous n'avez voté pour aucun film pour le moment.</p>
        <a href="films.php" class="btn">Parcourir les films en vote</a>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Film</th>
                    <th>Genre</th>
                    <th>Total des votes</th>
                    <th>Date de vote</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mes_votes as $vote): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($vote['titre']); ?></strong><br>
                            <small><?php echo htmlspecialchars($vote['realisateur']); ?> (<?php echo $vote['annee']; ?>)</small>
                        </td>
                        <td><span class="badge" style="background-color: #34495e; color: white;"><?php echo $vote['genre']; ?></span></td>
                        <td><strong><?php echo $vote['total_votes']; ?></strong> votes</td>
                        <td><?php echo date('d/m/Y H:i', strtotime($vote['date_vote'])); ?></td>
                        <td>
                            <?php if ($vote['statut'] === 'en_vote'): ?>
                                <a href="vote.php?action=remove&film_id=<?php echo $vote['id']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir retirer votre vote ?')">Retirer</a>
                            <?php else: ?>
                                <span class="badge badge-<?php echo $vote['statut']; ?>"><?php echo ucfirst($vote['statut']); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Films disponibles pour voter -->
<div class="card">
    <h2>Films disponibles pour le vote</h2>
    <?php
    $stmt = $pdo->query("
        SELECT f.*, u.prenom, u.nom,
               (SELECT COUNT(*) FROM votes WHERE film_id = f.id) as total_votes,
               (SELECT COUNT(*) FROM votes WHERE film_id = f.id AND user_id = {$_SESSION['user_id']}) as user_voted
        FROM films f
        JOIN users u ON f.user_id = u.id
        WHERE f.statut = 'en_vote'
        ORDER BY total_votes DESC
    ");
    $films_disponibles = $stmt->fetchAll();
    
    if (empty($films_disponibles)): ?>
        <p>Aucun film n'est actuellement disponible pour le vote.</p>
    <?php else: ?>
        <div class="films-grid">
            <?php foreach ($films_disponibles as $film): ?>
                <div class="film-card">
                    <h3><?php echo htmlspecialchars($film['titre']); ?></h3>
                    <p class="film-meta">
                        <?php echo htmlspecialchars($film['realisateur']); ?> (<?php echo $film['annee']; ?>)<br>
                        <span class="badge badge-vote"><?php echo $film['genre']; ?></span>
                    </p>
                    <p class="film-synopsis">
                        <?php echo htmlspecialchars($film['synopsis']); ?>
                    </p>
                    <p style="font-size: 0.9rem; color: #7f8c8d;">
                        Proposé par <?php echo htmlspecialchars($film['prenom'] . ' ' . $film['nom']); ?>
                    </p>
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee;">
                        <p><strong>Votes actuels: <?php echo $film['total_votes']; ?></strong></p>
                        <?php if ($film['user_voted'] > 0): ?>
                            <span class="badge badge-visionne">✓ Vous avez voté</span>
                            <a href="vote.php?action=remove&film_id=<?php echo $film['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Retirer votre vote ?')">Retirer</a>
                        <?php else: ?>
                            <a href="vote.php?film_id=<?php echo $film['id']; ?>" class="btn btn-success">Voter pour ce film</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
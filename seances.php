<?php
require_once 'config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$message = '';
$error = '';

// Inscription à une séance
if (isset($_GET['action']) && $_GET['action'] === 'register' && isset($_GET['id'])) {
    $seance_id = intval($_GET['id']);
    
    // Vérifier la disponibilité
    $stmt = $pdo->prepare("SELECT * FROM seances WHERE id = ? AND places_restantes > 0");
    $stmt->execute([$seance_id]);
    $seance = $stmt->fetch();
    
    if (!$seance) {
        $error = "Cette séance est complète ou n'existe pas.";
    } else {
        // Vérifier si déjà inscrit
        $stmt = $pdo->prepare("SELECT * FROM participations WHERE user_id = ? AND seance_id = ?");
        $stmt->execute([$_SESSION['user_id'], $seance_id]);
        
        if ($stmt->fetch()) {
            $error = "Vous êtes déjà inscrit à cette séance.";
        } else {
            // Inscrire et décrémenter les places
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("INSERT INTO participations (user_id, seance_id) VALUES (?, ?)");
                $stmt->execute([$_SESSION['user_id'], $seance_id]);
                
                $stmt = $pdo->prepare("UPDATE seances SET places_restantes = places_restantes - 1 WHERE id = ?");
                $stmt->execute([$seance_id]);
                
                $pdo->commit();
                $message = "Inscription réussie !";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Une erreur est survenue lors de l'inscription.";
            }
        }
    }
}

// Désinscription
if (isset($_GET['action']) && $_GET['action'] === 'unregister' && isset($_GET['id'])) {
    $seance_id = intval($_GET['id']);
    
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("DELETE FROM participations WHERE user_id = ? AND seance_id = ?");
        $stmt->execute([$_SESSION['user_id'], $seance_id]);
        
        $stmt = $pdo->prepare("UPDATE seances SET places_restantes = places_restantes + 1 WHERE id = ?");
        $stmt->execute([$seance_id]);
        
        $pdo->commit();
        $message = "Désinscription réussie.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Une erreur est survenue lors de la désinscription.";
    }
}

$page_title = 'Séances';
include 'includes/header.php';
?>

<h1 style="margin-bottom: 2rem;">🎟️ Séances de cinéma</h1>

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

<!-- Mes inscriptions -->
<div class="card">
    <h2>Mes inscriptions</h2>
    <?php
    $stmt = $pdo->prepare("
        SELECT s.*, f.titre, f.realisateur, f.annee, p.date_inscription
        FROM participations p
        JOIN seances s ON p.seance_id = s.id
        JOIN films f ON s.film_id = f.id
        WHERE p.user_id = ? AND s.date_seance >= NOW()
        ORDER BY s.date_seance ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $mes_inscriptions = $stmt->fetchAll();
    
    if (empty($mes_inscriptions)): ?>
        <p>Vous n'êtes inscrit à aucune séance.</p>
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
                <?php foreach ($mes_inscriptions as $inscription): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($inscription['titre']); ?></strong><br>
                            <small><?php echo htmlspecialchars($inscription['realisateur']); ?> (<?php echo $inscription['annee']; ?>)</small>
                        </td>
                        <td><?php echo date('d/m/Y à H:i', strtotime($inscription['date_seance'])); ?></td>
                        <td><?php echo htmlspecialchars($inscription['lieu']); ?></td>
                        <td><?php echo $inscription['places_restantes']; ?> / <?php echo $inscription['capacite_max']; ?></td>
                        <td>
                            <a href="seances.php?action=unregister&id=<?php echo $inscription['id']; ?>" class="btn btn-danger" onclick="return confirm('Se désinscrire de cette séance ?')">Se désinscrire</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Séances à venir -->
<div class="card">
    <h2>Toutes les séances à venir</h2>
    <?php
    $stmt = $pdo->prepare("
        SELECT s.*, f.titre, f.realisateur, f.annee, f.genre, f.duree,
               (SELECT COUNT(*) FROM participations WHERE seance_id = s.id) as inscrits,
               (SELECT COUNT(*) FROM participations WHERE seance_id = s.id AND user_id = ?) as user_inscrit
        FROM seances s
        JOIN films f ON s.film_id = f.id
        WHERE s.date_seance >= NOW()
        ORDER BY s.date_seance ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $toutes_seances = $stmt->fetchAll();
    
    if (empty($toutes_seances)): ?>
        <p>Aucune séance programmée pour le moment.</p>
        <?php if (isAdmin()): ?>
            <a href="admin/manage_seances.php" class="btn">Programmer une séance</a>
        <?php endif; ?>
    <?php else: ?>
        <div class="films-grid">
            <?php foreach ($toutes_seances as $seance): ?>
                <div class="film-card">
                    <h3><?php echo htmlspecialchars($seance['titre']); ?></h3>
                    <p class="film-meta">
                        <?php echo htmlspecialchars($seance['realisateur']); ?> (<?php echo $seance['annee']; ?>)<br>
                        <span class="badge" style="background-color: #34495e; color: white;"><?php echo $seance['genre']; ?></span>
                        <span class="badge" style="background-color: #16a085; color: white;">⏱️ <?php echo $seance['duree']; ?> min</span>
                    </p>
                    
                    <div style="background-color: #ecf0f1; padding: 1rem; border-radius: 4px; margin: 1rem 0;">
                        <p><strong>📅 <?php echo date('d/m/Y à H:i', strtotime($seance['date_seance'])); ?></strong></p>
                        <p>📍 <?php echo htmlspecialchars($seance['lieu']); ?></p>
                        <p>
                            <strong>Places:</strong> 
                            <?php 
                            $places_prises = $seance['capacite_max'] - $seance['places_restantes'];
                            echo $places_prises . ' / ' . $seance['capacite_max'];
                            ?>
                            <?php if ($seance['places_restantes'] == 0): ?>
                                <span class="badge badge-danger" style="background-color: #e74c3c;">Complet</span>
                            <?php elseif ($seance['places_restantes'] <= 5): ?>
                                <span class="badge" style="background-color: #f39c12; color: white;">Places limitées</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <?php if ($seance['user_inscrit'] > 0): ?>
                        <span class="badge badge-visionne">✓ Vous êtes inscrit</span>
                        <a href="seances.php?action=unregister&id=<?php echo $seance['id']; ?>" class="btn btn-danger" onclick="return confirm('Se désinscrire ?')">Se désinscrire</a>
                    <?php elseif ($seance['places_restantes'] > 0): ?>
                        <a href="seances.php?action=register&id=<?php echo $seance['id']; ?>" class="btn btn-success">S'inscrire</a>
                    <?php else: ?>
                        <button class="btn btn-secondary" disabled>Complet</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Séances passées -->
<div class="card">
    <h2>Séances passées</h2>
    <?php
    $stmt = $pdo->prepare("
        SELECT s.*, f.titre, f.realisateur, f.annee,
               (SELECT COUNT(*) FROM participations WHERE seance_id = s.id) as participants
        FROM seances s
        JOIN films f ON s.film_id = f.id
        WHERE s.date_seance < NOW()
        ORDER BY s.date_seance DESC
        LIMIT 10
    ");
    $stmt->execute();
    $seances_passees = $stmt->fetchAll();
    
    if (empty($seances_passees)): ?>
        <p>Aucune séance passée.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Film</th>
                    <th>Date</th>
                    <th>Participants</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($seances_passees as $seance): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($seance['titre']); ?></strong><br>
                            <small><?php echo htmlspecialchars($seance['realisateur']); ?> (<?php echo $seance['annee']; ?>)</small>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($seance['date_seance'])); ?></td>
                        <td><?php echo $seance['participants']; ?> personnes</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
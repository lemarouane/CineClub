<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$message = '';
$error = '';

// Changer le statut d'un film
if (isset($_GET['action']) && $_GET['action'] === 'change_status' && isset($_GET['id']) && isset($_GET['status'])) {
    $film_id = intval($_GET['id']);
    $new_status = $_GET['status'];
    
    $allowed_statuses = ['propose', 'en_vote', 'programme', 'visionne'];
    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $pdo->prepare("UPDATE films SET statut = ? WHERE id = ?");
        if ($stmt->execute([$new_status, $film_id])) {
            $message = "Statut modifié avec succès.";
        } else {
            $error = "Erreur lors de la modification du statut.";
        }
    }
}

// Supprimer un film
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $film_id = intval($_GET['id']);
    
    $stmt = $pdo->prepare("DELETE FROM films WHERE id = ?");
    if ($stmt->execute([$film_id])) {
        $message = "Film supprimé avec succès.";
    } else {
        $error = "Erreur lors de la suppression du film.";
    }
}

// Récupérer tous les films
$stmt = $pdo->query("
    SELECT f.*, u.prenom, u.nom,
           (SELECT COUNT(*) FROM votes WHERE film_id = f.id) as total_votes
    FROM films f
    JOIN users u ON f.user_id = u.id
    ORDER BY f.date_proposition DESC
");
$films = $stmt->fetchAll();

$page_title = 'Gestion des films';
include '../includes/header.php';

?>
<link rel="stylesheet" href="../css/style.css">

<div style="margin-bottom: 1rem;">
    <a href="index.php" style="color: #3498db; text-decoration: none;">← Retour au tableau de bord admin</a>
</div>

<h1 style="margin-bottom: 2rem;">📽️ Gestion des films</h1>

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

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="margin: 0;">Tous les films (<?php echo count($films); ?>)</h2>
    </div>
    
    <?php if (empty($films)): ?>
        <p>Aucun film dans la base de données.</p>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Réalisateur</th>
                        <th>Genre</th>
                        <th>Statut</th>
                        <th>Votes</th>
                        <th>Proposé par</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($films as $film): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($film['titre']); ?></strong><br>
                                <small><?php echo $film['annee']; ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($film['realisateur']); ?></td>
                            <td><span class="badge" style="background-color: #34495e; color: white;"><?php echo $film['genre']; ?></span></td>
                            <td>
                                <span class="badge badge-<?php 
                                    echo $film['statut'] === 'propose' ? 'propose' : 
                                        ($film['statut'] === 'en_vote' ? 'vote' : 
                                        ($film['statut'] === 'programme' ? 'programme' : 'visionne')); 
                                ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $film['statut'])); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($film['statut'] === 'en_vote'): ?>
                                    <strong><?php echo $film['total_votes']; ?></strong> votes
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($film['prenom'] . ' ' . $film['nom']); ?></td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    <!-- Menu déroulant pour changer le statut -->
                                    <select onchange="if(this.value) window.location.href=this.value;" style="padding: 0.5rem; border-radius: 4px; border: 1px solid #ddd;">
                                        <option value="">Changer statut</option>
                                        <?php if ($film['statut'] !== 'propose'): ?>
                                            <option value="?action=change_status&id=<?php echo $film['id']; ?>&status=propose">→ Proposé</option>
                                        <?php endif; ?>
                                        <?php if ($film['statut'] !== 'en_vote'): ?>
                                            <option value="?action=change_status&id=<?php echo $film['id']; ?>&status=en_vote">→ En vote</option>
                                        <?php endif; ?>
                                        <?php if ($film['statut'] !== 'programme'): ?>
                                            <option value="?action=change_status&id=<?php echo $film['id']; ?>&status=programme">→ Programmé</option>
                                        <?php endif; ?>
                                        <?php if ($film['statut'] !== 'visionne'): ?>
                                            <option value="?action=change_status&id=<?php echo $film['id']; ?>&status=visionne">→ Visionné</option>
                                        <?php endif; ?>
                                    </select>
                                    
                                    <a href="?action=delete&id=<?php echo $film['id']; ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce film ?')"
                                       style="padding: 0.5rem 1rem;">
                                        🗑️
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Statistiques par statut -->
<div class="card">
    <h2>Statistiques</h2>
    <?php
    $stats_statuts = [];
    foreach ($films as $film) {
        $statut = $film['statut'];
        if (!isset($stats_statuts[$statut])) {
            $stats_statuts[$statut] = 0;
        }
        $stats_statuts[$statut]++;
    }
    ?>
    <div class="stats-grid">
        <div class="stat-card">
            <h3><?php echo isset($stats_statuts['propose']) ? $stats_statuts['propose'] : 0; ?></h3>
            <p>Films proposés</p>
        </div>
        <div class="stat-card">
            <h3><?php echo isset($stats_statuts['en_vote']) ? $stats_statuts['en_vote'] : 0; ?></h3>
            <p>Films en vote</p>
        </div>
        <div class="stat-card">
            <h3><?php echo isset($stats_statuts['programme']) ? $stats_statuts['programme'] : 0; ?></h3>
            <p>Films programmés</p>
        </div>
        <div class="stat-card">
            <h3><?php echo isset($stats_statuts['visionne']) ? $stats_statuts['visionne'] : 0; ?></h3>
            <p>Films visionnés</p>
        </div>
    </div>
</div>

<!-- Guide d'utilisation -->
<div class="card" style="background-color: #e8f4f8; border-left: 4px solid #3498db;">
    <h3>💡 Guide de gestion</h3>
    <ul style="list-style-position: inside; line-height: 2;">
        <li><strong>Proposé:</strong> Film nouvellement ajouté, en attente de validation</li>
        <li><strong>En vote:</strong> Film validé, ouvert aux votes des membres</li>
        <li><strong>Programmé:</strong> Film sélectionné pour une prochaine séance</li>
        <li><strong>Visionné:</strong> Film déjà projeté, ouvert aux critiques</li>
    </ul>
    <p style="margin-top: 1rem;"><strong>Conseil:</strong> Passez les films les plus votés en statut "Programmé" pour les préparer aux séances.</p>
</div>

<?php include '../includes/footer.php'; ?>
<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$message = '';
$error = '';

// Créer une nouvelle séance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_seance'])) {
    $film_id = intval($_POST['film_id']);
    $date_seance = cleanInput($_POST['date_seance']);
    $lieu = cleanInput($_POST['lieu']);
    $capacite_max = intval($_POST['capacite_max']);
    
    if (empty($film_id) || empty($date_seance) || empty($lieu) || $capacite_max <= 0) {
        $error = "Tous les champs sont obligatoires.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO seances (film_id, date_seance, lieu, capacite_max, places_restantes) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$film_id, $date_seance, $lieu, $capacite_max, $capacite_max])) {
            $message = "Séance créée avec succès !";
            
            // Mettre le film en statut "programmé" si ce n'est pas déjà le cas
            $stmt = $pdo->prepare("UPDATE films SET statut = 'programme' WHERE id = ? AND statut != 'visionne'");
            $stmt->execute([$film_id]);
        } else {
            $error = "Erreur lors de la création de la séance.";
        }
    }
}

// Supprimer une séance
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $seance_id = intval($_GET['id']);
    
    $stmt = $pdo->prepare("DELETE FROM seances WHERE id = ?");
    if ($stmt->execute([$seance_id])) {
        $message = "Séance supprimée avec succès.";
    } else {
        $error = "Erreur lors de la suppression de la séance.";
    }
}

// Récupérer toutes les séances
$stmt = $pdo->query("
    SELECT s.*, f.titre, f.realisateur, f.annee,
           (SELECT COUNT(*) FROM participations WHERE seance_id = s.id) as participants
    FROM seances s
    JOIN films f ON s.film_id = f.id
    ORDER BY s.date_seance DESC
");
$seances = $stmt->fetchAll();

// Récupérer les films disponibles pour programmer une séance
$stmt = $pdo->query("
    SELECT id, titre, realisateur, annee, statut
    FROM films
    WHERE statut IN ('en_vote', 'programme')
    ORDER BY titre ASC
");
$films_disponibles = $stmt->fetchAll();

$page_title = 'Gestion des séances';
include '../includes/header.php';
?>
<link rel="stylesheet" href="../css/style.css">
<div style="margin-bottom: 1rem;">
    <a href="index.php" style="color: #3498db; text-decoration: none;">← Retour au tableau de bord admin</a>
</div>

<h1 style="margin-bottom: 2rem;">🎟️ Gestion des séances</h1>

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

<!-- Formulaire de création -->
<div class="card">
    <h2>➕ Programmer une nouvelle séance</h2>
    
    <?php if (empty($films_disponibles)): ?>
        <div class="alert alert-info">
            Aucun film disponible pour programmer une séance. Les films doivent être en statut "En vote" ou "Programmé".
        </div>
    <?php else: ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="film_id">Film *</label>
                <select id="film_id" name="film_id" required>
                    <option value="">-- Sélectionner un film --</option>
                    <?php foreach ($films_disponibles as $film): ?>
                        <option value="<?php echo $film['id']; ?>">
                            <?php echo htmlspecialchars($film['titre']); ?> 
                            (<?php echo $film['annee']; ?>) - 
                            <?php echo ucfirst($film['statut']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="date_seance">Date et heure *</label>
                    <input type="datetime-local" id="date_seance" name="date_seance" required 
                           min="<?php echo date('Y-m-d\TH:i'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="capacite_max">Capacité maximale *</label>
                    <input type="number" id="capacite_max" name="capacite_max" min="1" value="50" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="lieu">Lieu *</label>
                <input type="text" id="lieu" name="lieu" required 
                       placeholder="Ex: Salle Lumière, Cinéma Central">
            </div>
            
            <button type="submit" name="create_seance" class="btn btn-success">Créer la séance</button>
        </form>
    <?php endif; ?>
</div>

<!-- Liste des séances -->
<div class="card">
    <h2>Toutes les séances (<?php echo count($seances); ?>)</h2>
    
    <?php if (empty($seances)): ?>
        <p>Aucune séance programmée.</p>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Film</th>
                        <th>Date</th>
                        <th>Lieu</th>
                        <th>Participants</th>
                        <th>Places</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $seances_futures = [];
                    $seances_passees = [];
                    
                    foreach ($seances as $seance) {
                        if (strtotime($seance['date_seance']) >= time()) {
                            $seances_futures[] = $seance;
                        } else {
                            $seances_passees[] = $seance;
                        }
                    }
                    
                    // Afficher d'abord les séances futures
                    foreach ($seances_futures as $seance): 
                    ?>
                        <tr style="background-color: #f8f9fa;">
                            <td>
                                <strong><?php echo htmlspecialchars($seance['titre']); ?></strong><br>
                                <small><?php echo htmlspecialchars($seance['realisateur']); ?> (<?php echo $seance['annee']; ?>)</small>
                            </td>
                            <td>
                                <strong><?php echo date('d/m/Y', strtotime($seance['date_seance'])); ?></strong><br>
                                <small><?php echo date('H:i', strtotime($seance['date_seance'])); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($seance['lieu']); ?></td>
                            <td><strong><?php echo $seance['participants']; ?></strong> inscrits</td>
                            <td>
                                <?php 
                                $places_prises = $seance['capacite_max'] - $seance['places_restantes'];
                                echo $places_prises . ' / ' . $seance['capacite_max'];
                                ?>
                            </td>
                            <td>
                                <?php if ($seance['places_restantes'] == 0): ?>
                                    <span class="badge" style="background-color: #e74c3c; color: white;">Complet</span>
                                <?php else: ?>
                                    <span class="badge badge-programme">À venir</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?action=delete&id=<?php echo $seance['id']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette séance ? Les <?php echo $seance['participants']; ?> participants inscrits seront désincrits.')">
                                    Supprimer
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <!-- Séances passées -->
                    <?php foreach ($seances_passees as $seance): ?>
                        <tr style="opacity: 0.6;">
                            <td>
                                <strong><?php echo htmlspecialchars($seance['titre']); ?></strong><br>
                                <small><?php echo htmlspecialchars($seance['realisateur']); ?> (<?php echo $seance['annee']; ?>)</small>
                            </td>
                            <td>
                                <strong><?php echo date('d/m/Y', strtotime($seance['date_seance'])); ?></strong><br>
                                <small><?php echo date('H:i', strtotime($seance['date_seance'])); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($seance['lieu']); ?></td>
                            <td><strong><?php echo $seance['participants']; ?></strong> participants</td>
                            <td>-</td>
                            <td><span class="badge badge-visionne">Terminée</span></td>
                            <td>
                                <a href="?action=delete&id=<?php echo $seance['id']; ?>" 
                                   class="btn btn-secondary" 
                                   onclick="return confirm('Supprimer cette séance passée ?')">
                                    Supprimer
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Statistiques -->
<div class="stats-grid">
    <div class="stat-card">
        <h3><?php echo count($seances_futures); ?></h3>
        <p>Séances à venir</p>
    </div>
    <div class="stat-card">
        <h3><?php echo count($seances_passees); ?></h3>
        <p>Séances terminées</p>
    </div>
    <div class="stat-card">
        <h3>
            <?php 
            $total_participants = 0;
            foreach ($seances_futures as $s) {
                $total_participants += $s['participants'];
            }
            echo $total_participants;
            ?>
        </h3>
        <p>Inscriptions totales</p>
    </div>
    <div class="stat-card">
        <h3>
            <?php 
            $places_disponibles = 0;
            foreach ($seances_futures as $s) {
                $places_disponibles += $s['places_restantes'];
            }
            echo $places_disponibles;
            ?>
        </h3>
        <p>Places disponibles</p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
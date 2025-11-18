<?php
require_once 'config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$film_id = isset($_GET['film_id']) ? intval($_GET['film_id']) : 0;
$message = '';
$error = '';

// Vérifier que le film existe et est visionné
$stmt = $pdo->prepare("SELECT * FROM films WHERE id = ?");
$stmt->execute([$film_id]);
$film = $stmt->fetch();

if (!$film) {
    redirect('films.php');
}

// Traitement de l'ajout/modification de review
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $film['statut'] === 'visionne') {
    $note = intval($_POST['note']);
    $commentaire = cleanInput($_POST['commentaire']);
    
    if ($note < 1 || $note > 5) {
        $error = "La note doit être entre 1 et 5.";
    } else {
        // Vérifier si l'utilisateur a déjà une review
        $stmt = $pdo->prepare("SELECT * FROM reviews WHERE user_id = ? AND film_id = ?");
        $stmt->execute([$_SESSION['user_id'], $film_id]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Mise à jour
            $stmt = $pdo->prepare("UPDATE reviews SET note = ?, commentaire = ?, date_review = NOW() WHERE id = ?");
            if ($stmt->execute([$note, $commentaire, $existing['id']])) {
                $message = "Votre critique a été mise à jour !";
            }
        } else {
            // Insertion
            $stmt = $pdo->prepare("INSERT INTO reviews (user_id, film_id, note, commentaire) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$_SESSION['user_id'], $film_id, $note, $commentaire])) {
                $message = "Votre critique a été ajoutée !";
            }
        }
    }
}

// Récupérer la review de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM reviews WHERE user_id = ? AND film_id = ?");
$stmt->execute([$_SESSION['user_id'], $film_id]);
$user_review = $stmt->fetch();

// Récupérer toutes les reviews
$stmt = $pdo->prepare("
    SELECT r.*, u.prenom, u.nom
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.film_id = ?
    ORDER BY r.date_review DESC
");
$stmt->execute([$film_id]);
$reviews = $stmt->fetchAll();

// Calcul de la note moyenne
$stmt = $pdo->prepare("SELECT AVG(note) as avg_note, COUNT(*) as nb_reviews FROM reviews WHERE film_id = ?");
$stmt->execute([$film_id]);
$rating_data = $stmt->fetch();

$page_title = 'Critiques - ' . $film['titre'];
include 'includes/header.php';
?>

<div style="margin-bottom: 1rem;">
    <a href="films.php" style="color: #3498db; text-decoration: none;">← Retour au catalogue</a>
</div>

<div class="card">
    <h1 style="margin-bottom: 1rem;"><?php echo htmlspecialchars($film['titre']); ?></h1>
    <p class="film-meta">
        <strong><?php echo htmlspecialchars($film['realisateur']); ?></strong> (<?php echo $film['annee']; ?>)<br>
        <span class="badge" style="background-color: #34495e; color: white;"><?php echo $film['genre']; ?></span>
        <span class="badge badge-<?php echo $film['statut']; ?>"><?php echo ucfirst($film['statut']); ?></span>
    </p>
    
    <?php if ($rating_data['nb_reviews'] > 0): ?>
        <div style="background-color: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin: 1.5rem 0;">
            <h3 style="margin-bottom: 0.5rem;">Note moyenne</h3>
            <p class="rating" style="font-size: 2rem; margin-bottom: 0.5rem;">
                <?php
                $avg_note = round($rating_data['avg_note'], 1);
                $full_stars = floor($avg_note);
                for ($i = 1; $i <= 5; $i++) {
                    echo $i <= $full_stars ? '★' : '☆';
                }
                ?>
            </p>
            <p style="color: #7f8c8d;">
                <?php echo number_format($avg_note, 1); ?> / 5 
                (<?php echo $rating_data['nb_reviews']; ?> <?php echo $rating_data['nb_reviews'] > 1 ? 'avis' : 'avis'; ?>)
            </p>
        </div>
    <?php endif; ?>
    
    <h3 style="margin-top: 2rem;">Synopsis</h3>
    <p><?php echo nl2br(htmlspecialchars($film['synopsis'])); ?></p>
</div>

<!-- Formulaire de review (seulement si le film est visionné) -->
<?php if ($film['statut'] === 'visionne'): ?>
    <div class="card">
        <h2><?php echo $user_review ? 'Modifier ma critique' : 'Écrire une critique'; ?></h2>
        
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
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Note *</label>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <label style="cursor: pointer; font-size: 2rem; margin: 0;">
                            <input type="radio" name="note" value="<?php echo $i; ?>" 
                                   <?php echo ($user_review && $user_review['note'] == $i) ? 'checked' : ($i == 3 && !$user_review ? 'checked' : ''); ?>
                                   required
                                   style="display: none;">
                            <span class="star-rating" style="color: #f39c12;">★</span>
                        </label>
                    <?php endfor; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="commentaire">Votre critique</label>
                <textarea id="commentaire" name="commentaire" rows="5" placeholder="Partagez votre avis sur ce film..."><?php echo $user_review ? htmlspecialchars($user_review['commentaire']) : ''; ?></textarea>
            </div>
            
            <button type="submit" class="btn btn-success"><?php echo $user_review ? 'Mettre à jour' : 'Publier'; ?></button>
        </form>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        Les critiques seront disponibles après la projection de ce film.
    </div>
<?php endif; ?>

<!-- Liste des reviews -->
<div class="card">
    <h2>Toutes les critiques (<?php echo count($reviews); ?>)</h2>
    
    <?php if (empty($reviews)): ?>
        <p style="text-align: center; color: #7f8c8d; padding: 2rem;">
            Aucune critique pour le moment. Soyez le premier à donner votre avis !
        </p>
    <?php else: ?>
        <?php foreach ($reviews as $review): ?>
            <div style="border-bottom: 1px solid #eee; padding: 1.5rem 0;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                    <div>
                        <strong><?php echo htmlspecialchars($review['prenom'] . ' ' . $review['nom']); ?></strong>
                        <?php if ($review['user_id'] == $_SESSION['user_id']): ?>
                            <span class="badge" style="background-color: #3498db; color: white; font-size: 0.75rem;">Vous</span>
                        <?php endif; ?>
                    </div>
                    <small style="color: #7f8c8d;"><?php echo date('d/m/Y', strtotime($review['date_review'])); ?></small>
                </div>
                
                <p class="rating" style="margin-bottom: 0.5rem;">
                    <?php
                    for ($i = 1; $i <= 5; $i++) {
                        echo $i <= $review['note'] ? '★' : '☆';
                    }
                    ?>
                </p>
                
                <?php if ($review['commentaire']): ?>
                    <p style="margin-top: 0.5rem;"><?php echo nl2br(htmlspecialchars($review['commentaire'])); ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
// Gestion interactive des étoiles
document.querySelectorAll('label input[name="note"]').forEach((input, index) => {
    const label = input.parentElement;
    
    label.addEventListener('mouseover', function() {
        updateStars(index + 1, true);
    });
    
    label.addEventListener('mouseout', function() {
        const checked = document.querySelector('input[name="note"]:checked');
        if (checked) {
            const checkedIndex = Array.from(document.querySelectorAll('input[name="note"]')).indexOf(checked);
            updateStars(checkedIndex + 1, false);
        } else {
            updateStars(0, false);
        }
    });
    
    input.addEventListener('change', function() {
        updateStars(index + 1, false);
    });
});

function updateStars(count, hover) {
    document.querySelectorAll('.star-rating').forEach((star, index) => {
        if (index < count) {
            star.style.color = '#f39c12';
            star.style.transform = hover ? 'scale(1.2)' : 'scale(1)';
        } else {
            star.style.color = '#ddd';
            star.style.transform = 'scale(1)';
        }
    });
}

// Initialiser l'affichage des étoiles
const checked = document.querySelector('input[name="note"]:checked');
if (checked) {
    const checkedIndex = Array.from(document.querySelectorAll('input[name="note"]')).indexOf(checked);
    updateStars(checkedIndex + 1, false);
}
</script>

<?php include 'includes/footer.php'; ?>
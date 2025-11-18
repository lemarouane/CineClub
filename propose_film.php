<?php
require_once 'config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = cleanInput($_POST['titre']);
    $realisateur = cleanInput($_POST['realisateur']);
    $annee = intval($_POST['annee']);
    $genre = cleanInput($_POST['genre']);
    $duree = intval($_POST['duree']);
    $synopsis = cleanInput($_POST['synopsis']);
    
    // Validation
    if (empty($titre) || empty($realisateur) || empty($annee) || empty($genre) || empty($synopsis)) {
        $errors[] = "Tous les champs obligatoires doivent être remplis.";
    }
    
    if ($annee < 1888 || $annee > date('Y')) {
        $errors[] = "Année invalide.";
    }
    
    if ($duree <= 0) {
        $errors[] = "Durée invalide.";
    }
    
    // Insertion
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO films (titre, realisateur, annee, genre, duree, synopsis, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$titre, $realisateur, $annee, $genre, $duree, $synopsis, $_SESSION['user_id']])) {
            $success = "Film proposé avec succès !";
            // Réinitialiser le formulaire
            $titre = $realisateur = $genre = $synopsis = '';
            $annee = $duree = 0;
        } else {
            $errors[] = "Une erreur est survenue lors de l'ajout du film.";
        }
    }
}

$page_title = 'Proposer un film';
include 'includes/header.php';
?>

<div class="card" style="max-width: 700px; margin: 2rem auto;">
    <h2>🎬 Proposer un film au club</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul style="margin-left: 1.5rem;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <?php echo $success; ?>
            <p><a href="films.php">Voir le catalogue</a></p>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="titre">Titre du film *</label>
            <input type="text" id="titre" name="titre" required value="<?php echo isset($titre) ? $titre : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="realisateur">Réalisateur *</label>
            <input type="text" id="realisateur" name="realisateur" required value="<?php echo isset($realisateur) ? $realisateur : ''; ?>">
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="annee">Année *</label>
                <input type="number" id="annee" name="annee" min="1888" max="<?php echo date('Y'); ?>" required value="<?php echo isset($annee) ? $annee : date('Y'); ?>">
            </div>
            
            <div class="form-group">
                <label for="duree">Durée (minutes) *</label>
                <input type="number" id="duree" name="duree" min="1" required value="<?php echo isset($duree) ? $duree : 90; ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label for="genre">Genre *</label>
            <select id="genre" name="genre" required>
                <option value="">-- Sélectionner un genre --</option>
                <option value="Action">Action</option>
                <option value="Animation">Animation</option>
                <option value="Aventure">Aventure</option>
                <option value="Comédie">Comédie</option>
                <option value="Crime">Crime</option>
                <option value="Documentaire">Documentaire</option>
                <option value="Drame">Drame</option>
                <option value="Fantastique">Fantastique</option>
                <option value="Horreur">Horreur</option>
                <option value="Musical">Musical</option>
                <option value="Romance">Romance</option>
                <option value="Science-Fiction">Science-Fiction</option>
                <option value="Thriller">Thriller</option>
                <option value="Western">Western</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="synopsis">Synopsis *</label>
            <textarea id="synopsis" name="synopsis" rows="5" required><?php echo isset($synopsis) ? $synopsis : ''; ?></textarea>
        </div>
        
        <button type="submit" class="btn btn-success" style="width: 100%;">Proposer ce film</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
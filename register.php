<?php
require_once 'config/database.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = cleanInput($_POST['nom']);
    $prenom = cleanInput($_POST['prenom']);
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        $errors[] = "Tous les champs sont obligatoires.";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide.";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }
    
    // Vérifier si l'email existe déjà
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Cet email est déjà utilisé.";
        }
    }
    
    // Insertion dans la base de données
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, password) VALUES (?, ?, ?, ?)");
        
        if ($stmt->execute([$nom, $prenom, $email, $hashed_password])) {
            $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
        } else {
            $errors[] = "Une erreur est survenue lors de l'inscription.";
        }
    }
}

$page_title = 'Inscription';
include 'includes/header.php';
?>

<div class="card" style="max-width: 500px; margin: 2rem auto;">
    <h2>Inscription au CineClub</h2>
    
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
            <p><a href="login.php">Se connecter maintenant</a></p>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="nom">Nom *</label>
            <input type="text" id="nom" name="nom" required value="<?php echo isset($nom) ? $nom : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="prenom">Prénom *</label>
            <input type="text" id="prenom" name="prenom" required value="<?php echo isset($prenom) ? $prenom : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" required value="<?php echo isset($email) ? $email : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="password">Mot de passe * (min. 6 caractères)</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirmer le mot de passe *</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        
        <button type="submit" class="btn btn-success" style="width: 100%;">S'inscrire</button>
    </form>
    
    <p style="text-align: center; margin-top: 1.5rem;">
        Déjà membre ? <a href="login.php">Se connecter</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>
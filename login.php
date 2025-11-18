<?php
require_once 'config/database.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            redirect('dashboard.php');
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    }
}

$page_title = 'Connexion';
include 'includes/header.php';
?>

<div class="card" style="max-width: 450px; margin: 2rem auto;">
    <h2>Connexion</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn" style="width: 100%;">Se connecter</button>
    </form>
    
    <p style="text-align: center; margin-top: 1.5rem;">
        Pas encore membre ? <a href="register.php">S'inscrire</a>
    </p>
    
 
</div>

<?php include 'includes/footer.php'; ?>
<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect("views/{$_SESSION['role']}/dashboard.php");
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        $error = "Session invalide. Veuillez réessayer.";
    } else {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        if (empty($username) || empty($password)) {
            $error = "Veuillez remplir tous les champs.";
        } else {
            $db = new Database();
            $conn = $db->getConnection();

            $stmt = $conn->prepare("SELECT id, username, password_hash, role, is_active FROM users WHERE username = :username OR email = :email");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $username);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $row = $stmt->fetch();
                if ($row['is_active'] == 0) {
                    $error = "Votre compte a été désactivé.";
                } else if (password_verify($password, $row['password_hash'])) {
                    // Password correct, start session
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['role'] = $row['role'];

                    // Update last login
                    $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
                    $updateStmt->bindParam(':id', $row['id']);
                    $updateStmt->execute();

                    // Log action
                    logAction($conn, $row['id'], 'Connexion réussie', 'Connexion au système');

                    redirect("views/{$row['role']}/dashboard.php");
                } else {
                    $error = "Identifiants incorrects.";
                }
            } else {
                $error = "Identifiants incorrects.";
            }
        }
    }
}

$csrf_token = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion Université</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h1>Gestion Université</h1>
            <p class="subtitle">Connectez-vous à votre espace professionnel</p>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo escape($error); ?></div>
            <?php endif; ?>
            
            <?php displayFlashMessage(); ?>

            <form action="<?php echo escape($_SERVER["PHP_SELF"]); ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo escape($csrf_token); ?>">
                
                <div class="form-group">
                    <label for="username">Identifiant ou Email</label>
                    <input type="text" id="username" name="username" class="form-control" required autofocus placeholder="Entrez votre identifiant">
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="Entrez votre mot de passe">
                </div>
                
                <button type="submit" class="btn btn-primary">Se connecter</button>
            </form>
        </div>
    </div>
</body>
</html>

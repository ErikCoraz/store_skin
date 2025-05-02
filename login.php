<?php
session_start();
require_once 'includes/db.php';

$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM utenti WHERE username = ?");
        $stmt->execute([$username]);
        $utente = $stmt->fetch();

        if ($utente) {
            $password_hash = $utente['password'];

            if (password_verify($password, $password_hash)) {            //Login riuscito
                $_SESSION['user_id'] = $utente['id'];
                $_SESSION['username'] = $utente['username'];
                $_SESSION['ruolo'] = $utente['ruolo'];

                if ($utente['ruolo'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            } else {
                $errore = "Credenziali non valide";
            }
        } else {
            $errore = "Credenziali non valide";
        }
    } else {
        $errore = "Inserisci username e password";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login - LoL Skin Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Accedi</h2>
        <?php if (!empty($errore)): ?>
            <div class="errore"><?php echo htmlspecialchars($errore); ?></div>
        <?php endif; ?>
        <form method="POST">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Login</button>
        </form>
        <p>Non hai un account? <a href="register.php">Registrati</a></p>
    </div>
</body>
</html>

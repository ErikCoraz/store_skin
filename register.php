<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {                  // Controllo se il form è stato inviato
    $username = trim($_POST["username"]);                    // Rimuove spazi bianchi all'inizio e alla fine dell'username
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if (empty($username)  empty($password)  empty($confirm_password)) {              // Verifica che tutti i campi siano stati compilati (non siano vuoti)
        $errore = "Tutti i campi sono obbligatori.";
    } elseif ($password !== $confirm_password) {                                   // Verifica che le password coincidano
        $errore = "Le password non coincidono.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);                              // Hash della password

        try {
            $stmt = $pdo->prepare("INSERT INTO utenti (username, password, ruolo) VALUES (?, ?, 'utente')");       // Inserimento utente nel database
            $stmt->execute([$username, $hashed_password]);
            $_SESSION["successo"] = "Registrazione completata. Ora puoi accedere.";                   // Messaggio di successo
            header("Location: login.php");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errore = "Username già esistente.";                 // Errore di chiave duplicata (username già esistente)
            } else {
                $errore = "Errore nel database: " . $e->getMessage();                 // Altri errori del database
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Registrazione</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><button id="toggle-dark">🌓 Dark Mode</button></li>
        </ul>
    </nav>

    <div class="login-container">
        <h2>Registrati</h2>

        <?php if (!empty($errore)) echo "<div class='errore'>$errore</div>"; ?>

        <form method="post" action="">
            <label>Username:</label>
            <input type="text" name="username" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <label>Conferma Password:</label>
            <input type="password" name="confirm_password" required>

            <button type="submit">Registrati</button>
        </form>

        <p>Hai già un account? <a href="login.php">Accedi qui</a>.</p>
    </div>
<script src="assets/js/dark-mode.js"></script>
</body>
</html>
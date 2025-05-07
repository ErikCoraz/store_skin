<?php
session_start();
require_once 'includes/db.php';

$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {               // Controllo se il form è stato inviato
    $username = $_POST['username'] ?? '';                 // Assegna il valore di 'username' dal modulo se presente, altrimenti imposta una stringa vuota
    $password = $_POST['password'] ?? '';                // Stessa cosa per 'password'
 
    if (!empty($username) && !empty($password)) {                             // Verifica che i campi non siano vuoti
        $stmt = $pdo->prepare("SELECT * FROM utenti WHERE username = ?");       // Prepara la query per selezionare l'utente
        $stmt->execute([$username]);              
        $utente = $stmt->fetch();              // Esegue la query e recupera l'utente

        if ($utente) {
            $password_hash = $utente['password'];            // Recupera l'hash della password dal database

            if (password_verify($password, $password_hash)) {            //Login riuscito
                $_SESSION['user_id'] = $utente['id'];                 
                $_SESSION['username'] = $utente['username'];               
                $_SESSION['ruolo'] = $utente['ruolo'];                 // Salva le informazioni dell'utente nella sessione

                // 🔄 Ripristina il carrello dal database
                $stmt = $pdo->prepare("SELECT id_skin FROM carrello WHERE id_utente = ?");
                $stmt->execute([$utente['id']]);
                $items = $stmt->fetchAll(PDO::FETCH_COLUMN);  // Ottieni solo la colonna id_skin
                $_SESSION['carrello'] = $items ?: [];         // Salva nella sessione

                if ($utente['ruolo'] === 'admin') {               // Se l'utente è admin, reindirizza alla dashboard admin
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: index.php");       // Altrimenti, reindirizza alla home page
                }
                exit;
            } else {
                $errore = "Credenziali non valide";     // Se la password non corrisponde, mostra un errore
            }
        } else {
            $errore = "Credenziali non valide";      // Se l'username non esiste, mostra un errore
        }
    } else {
        $errore = "Inserisci username e password";      // Se i campi sono vuoti, mostra un errore
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
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
        </ul>
    </nav>
    <div class="login-container">
        <h2>Accedi</h2>
        <?php if (!empty($errore)): ?>
            <div class="errore"><?php echo htmlspecialchars($errore); ?></div>              <!-- Mostra messaggio di errore se presente -->
        <?php endif; ?>                                                                  
        <form method="POST">                                                     <!-- Invia i dati al server -->
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

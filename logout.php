<?php
session_start();             // Avvia la sessione (necessario per poterla distruggere)

if (isset($_COOKIE['remember_token'])) {                   // Controlla se il cookie esiste
    require_once 'includes/db.php';                   // Includi il file di connessione al database, necessario per rimuovere il token dal DB

    $token = $_COOKIE['remember_token'];                  // Recupera il token dal cookie
    $hashedToken = hash('sha256', $token);             // Hash del token                    

    $stmt = $pdo->prepare("DELETE FROM login_tokens WHERE token = ?");
    $stmt->execute([$hashedToken]);                     // Rimuovi il token dal database

    setcookie('remember_token', '', time() - 3600, '/');  // Cancella il cookie dal browser impostandolo con una scadenza passata
}

session_unset();             // Rimuove tutte le variabili di sessione
session_destroy();           // Distrugge la sessione

header("Location: login.php");   // Reindirizza alla pagina di login
exit();
?>

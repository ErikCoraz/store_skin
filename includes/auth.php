<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {                                                  // Avvia la sessione se non è già stata avviata
    session_start();
}

function isLoggedIn() {
    if (isset($_SESSION['user_id'])) {                // Verifica se l'utente è loggato
        if (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin') {     // Se l'utente è admin, verifica anche il flag di autenticazione admin
            return isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] === true;   // Controlla se il flag è impostato
        }
        return true;
    }


    if (isset($_COOKIE['remember_token'])) {                // Verifica se esiste un cookie di login automatico
        global $pdo;
        $token = $_COOKIE['remember_token'];
        $hashedToken = hash('sha256', $token);           // Hash del token

        $stmt = $pdo->prepare("SELECT lt.user_id, u.username, u.ruolo 
                               FROM login_tokens lt 
                               JOIN utenti u ON lt.user_id = u.id 
                               WHERE lt.token = ? AND lt.expires_at > NOW()");   // Cerca il token nel DB e verifica se non è scaduto
        $stmt->execute([$hashedToken]);
        $row = $stmt->fetch();

        if ($row) {         // Se esiste una corrispondenza nel database, si effettua il login automatico impostando la sessione
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['ruolo'] = $row['ruolo'];

            return true;
        } else {
            setcookie('remember_token', '', time() - 3600, '/');          // Se il token non è valido o è scaduto, il cookie viene eliminato
        }
    }

    return false;
}

function isAdmin() {
    return isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin';          // Verifica se l'utente è admin
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');                      //Reindirizza se non loggato
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ../login.php');             // Reindirizza se non admin
        exit();
    }
}
?>

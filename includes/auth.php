<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {                                                  // Avvia la sessione se non è già stata avviata
    session_start();
}

function isLoggedIn() {
    if (isset($_SESSION['user_id'])) {                // Verifica se l'utente è loggato
        return true;
    }

    if (isset($_COOKIE['remember_token'])) {                // Verifica se esiste un cookie di login automatico
        global $pdo;
        $token = $_COOKIE['remember_token'];
        $hashedToken = hash('sha256', $token);

        $stmt = $pdo->prepare("SELECT lt.user_id, u.username, u.ruolo 
                               FROM login_tokens lt 
                               JOIN utenti u ON lt.user_id = u.id 
                               WHERE lt.token = ? AND lt.expires_at > NOW()");
        $stmt->execute([$hashedToken]);
        $row = $stmt->fetch();

        if ($row) {
            // 🔐 Login automatico via cookie valido
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['ruolo'] = $row['ruolo'];

            return true;
        } else {
            // ❌ Token non valido o scaduto → elimina cookie
            setcookie('remember_token', '', time() - 3600, '/');
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

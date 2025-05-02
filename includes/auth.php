<?php
require_once 'db.php';


if (session_status() === PHP_SESSION_NONE) {                                                  // Avvia la sessione se non è già stata avviata
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);                // Verifica se l'utente è loggato
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

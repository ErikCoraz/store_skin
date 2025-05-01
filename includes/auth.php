<?php
require_once 'db.php';      // Include il file di connessione al database

if (session_status() === PHP_SESSION_NONE) {  // Controlla se la sessione è già avviata
    session_start();
}

function isLoggedIn() {                        
    return isset($_SESSION['user_id']);    // Controlla se l'utente è loggato
}


function isAdmin() { 
    return isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin';     // Controlla se l'utente è admin
}


function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');      // Reindirizza alla pagina di login se non loggato
        exit();
    }
}


function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ../login.php');                   // Reindirizza alla pagina di login se non admin
        exit();
    }
}
?>
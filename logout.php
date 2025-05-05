<?php
session_start();             // Avvia la sessione (necessario per poterla distruggere)
session_unset();             // Rimuove tutte le variabili di sessione
session_destroy();           // Distrugge la sessione

header("Location: login.php");   // Reindirizza alla pagina di login
exit();
?>

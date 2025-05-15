<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();                                        // Solo admin può accedere

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {                 // Controlla se l'ID è valido
    header('Location: dashboard.php');                                 // Reindirizza alla dashboard
    exit();
}

$id = $_GET['id'];                                                       // ID della skin da eliminare                

$stmt = $pdo->prepare("SELECT * FROM skin WHERE id = ?");               // Recupera i dati della skin da eliminare 
$stmt->execute([$id]);                                                  
$skin = $stmt->fetch();

if (!$skin) {                                            
    header('Location: dashboard.php');                  // Se la skin non esiste, reindirizza alla dashboard
    exit();
}

$immagine_path = '../assets/img/' . $skin['immagine'];            // Percorso dell'immagine da eliminare
if (file_exists($immagine_path)) {                                // Controlla se il file esiste
    unlink($immagine_path);                               // Elimina il file immagine
}

$stmt = $pdo->prepare("DELETE FROM skin WHERE id = ?");          // Prepara la query per eliminare la skin
$stmt->execute([$id]);

header('Location: dashboard.php?success=1');                // Reindirizza alla dashboard con messaggio di successo
exit();
?>
<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();                    // Verifica accesso admin

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {         // Controlla se l'ID è valido
    header('Location: dashboard.php'); 
    exit();
}

$id = $_GET['id'];           

$stmt = $pdo->prepare("SELECT * FROM skin WHERE id = ?");     // Recupera la skin da modificare
$stmt->execute([$id]);
$skin = $stmt->fetch();

if (!$skin) {                                 
    header('Location: dashboard.php');             // Se la skin non esiste, reindirizza alla dashboard
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {             // Se il form è stato inviato
    $nome = $_POST['nome'] ?? '';                      
    $campione = $_POST['campione'] ?? '';
    $prezzo = $_POST['prezzo'] ?? '';
    $quantita = $_POST['quantita'] ?? '';           // Recupera i dati dal form

    $immagine = $skin['immagine'];                 // Di default l'immagine attuale

    if (!empty($nome) && !empty($campione) && is_numeric($prezzo) && is_numeric($quantita)) {        // Controlla se i campi sono validi
        if (!empty($_FILES['immagine']['name'])) {                                                  // Se è stata caricata una nuova immagine
            $target_dir = "../assets/img/";                                                     // Directory di destinazione
            $target_file = $target_dir . basename($_FILES["immagine"]["name"]);                // Percorso di destinazione

            if (move_uploaded_file($_FILES["immagine"]["tmp_name"], $target_file)) {          // Sposta il file dalla cartella temporanea PHP a quella di destinazione
                $immagine = $_FILES['immagine']['name'];                                      // Nome del file immagine
            } else {
                $errore = "Errore durante il caricamento dell'immagine.";                   // Errore nel caricamento
            }
        }

        if (!isset($errore)) {                                                      // Se non ci sono errori
            $stmt = $pdo->prepare("UPDATE skin SET nome = ?, campione = ?, prezzo = ?, quantita = ?, immagine = ? WHERE id = ?");
            $stmt->execute([$nome, $campione, $prezzo, $quantita, $immagine, $id]);            // Aggiorna i dati nel database

            $successo = "Skin aggiornata con successo!";                                 // Messaggio di successo
            $stmt = $pdo->prepare("SELECT * FROM skin WHERE id = ?");                   // Recupera la skin aggiornata
            $stmt->execute([$id]);
            $skin = $stmt->fetch(); 
        }
    } else {
        $errore = "Tutti i campi (eccetto immagine) sono obbligatori.";               // Controlla se i campi sono validi
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Modifica Skin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h1>Modifica Skin</h1>

    <?php if (isset($errore)): ?>
        <p style="color: red;"><?= $errore ?></p>
    <?php endif; ?>

    <?php if (isset($successo)): ?>
        <p style="color: green;"><?= $successo ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Nome Skin:</label><br>
        <input type="text" name="nome" value="<?= htmlspecialchars($skin['nome']) ?>"><br><br>

        <label>Campione:</label><br>
        <input type="text" name="campione" value="<?= htmlspecialchars($skin['campione']) ?>"><br><br>

        <label>Prezzo:</label><br>
        <input type="text" name="prezzo" value="<?= htmlspecialchars($skin['prezzo']) ?>"><br><br>

        <label>Quantità:</label><br>
        <input type="number" name="quantita" value="<?= htmlspecialchars($skin['quantita']) ?>"><br><br>

        <label>Immagine (lascia vuoto per non cambiare):</label><br>
        <input type="file" name="immagine"><br>
        <img src="../assets/img/<?= $skin['immagine'] ?>" alt="Skin attuale" width="120"><br><br>

        <button type="submit">Salva modifiche</button>
    </form>

    <p><a href="dashboard.php">⬅ Torna alla Dashboard</a></p>
</body>
</html>
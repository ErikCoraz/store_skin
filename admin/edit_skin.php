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

            $_SESSION['success'] = "Skin aggiornata con successo!";               // Messaggio di successo
            header("Location: edit_skin.php?id=" . $id);
            exit();                               

        }
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
<nav>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><button id="toggle-dark">🌓 Dark Mode</button></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</nav>
<div class="container">
    <h1>Modifica Skin</h1>

    <?php if (isset($errore)): ?>
        <div class="errore"><?= $errore ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="form">
        <div class="form-group">
            <label for="nome">Nome Skin:</label>
            <input type="text" name="nome" id="nome" value="<?= htmlspecialchars($skin['nome']) ?>" required>
        </div>

        <div class="form-group">
            <label for="campione">Campione:</label>
            <input type="text" name="campione" id="campione" value="<?= htmlspecialchars($skin['campione']) ?>" required>
        </div>

        <div class="form-group">
            <label for="prezzo">Prezzo:</label>
            <input type="number" step="0.01" name="prezzo" min=0 id="prezzo" value="<?= htmlspecialchars($skin['prezzo']) ?>" required>
        </div>

        <div class="form-group">
            <label for="quantita">Quantità:</label>
            <input type="number" name="quantita" id="quantita" min=0 value="<?= htmlspecialchars($skin['quantita']) ?>" required>
        </div>

        <div class="form-group">
            <label for="immagine">Immagine (lascia vuoto per non cambiare):</label>
            <input type="file" name="immagine" id="immagine">
            <br>
            <img src="../assets/img/<?= htmlspecialchars($skin['immagine']) ?>" alt="Skin attuale" style="max-width: 150px; margin-top: 10px;">
        </div>

        <div class="form-actions">
            <a href="dashboard.php" class="btn btn-secondary">⬅ Torna alla Dashboard</a>
            <button type="submit" class="btn btn-primary">Salva modifiche</button>
        </div>
    </form>
</div>
<script src="../assets/js/dark-mode.js"></script>
</body>
</html>
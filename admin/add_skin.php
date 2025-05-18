<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();     // Accesso riservato solo agli admin

$errore = '';
$successo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {                       // Controlla se il form è stato inviato
    $nome = $_POST['nome'] ?? '';
    $campione = $_POST['campione'] ?? '';
    $prezzo = $_POST['prezzo'] ?? '';
    $quantita = $_POST['quantita'] ?? '';
    $immagine = $_FILES['immagine']['name'] ?? '';          // Raccolti dati dal form

    if (!empty($nome) && !empty($campione) && is_numeric($prezzo) && is_numeric($quantita) && !empty($immagine)) {      // Verifica che i campi non siano vuoti e che prezzo e quantità siano numerici
        $target_dir = "../assets/img/";                             // Directory di destinazione per l'immagine
        $target_file = $target_dir . basename($immagine);          // Percorso completo del file

  
        if (move_uploaded_file($_FILES["immagine"]["tmp_name"], $target_file)) {        // Sposta il file caricato nella directory di destinazione
 
            $stmt = $pdo->prepare("INSERT INTO skin (nome, campione, prezzo, quantita, immagine) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $campione, $prezzo, $quantita, $immagine]);        // Esegue la query per inserire i dati nel database

            $successo = "Skin aggiunta con successo!";                      // Messaggio di successo
        } else {
            $errore = "Errore durante il caricamento dell'immagine.";     // Messaggio di errore se il caricamento dell'immagine fallisce
        }
    } else {
        $errore = "Tutti i campi sono obbligatori, compresa l'immagine.";     // Messaggio di errore se i campi non sono compilati correttamente
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Aggiungi Skin - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav>
        <ul>
            <img src="../assets/img/logo.png" alt="Logo" style="height: 40px;">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><button id="toggle-dark">🌓 Dark Mode</button></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>Aggiungi Nuova Skin</h1>

        <?php if ($errore): ?>
            <div class="errore"><?= htmlspecialchars($errore) ?></div>
        <?php endif; ?>

        <?php if ($successo): ?>
            <div class="success"><?= htmlspecialchars($successo) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label for="nome">Nome:</label>
            <input type="text" name="nome" id="nome" required>

            <label for="campione">Campione:</label>
            <input type="text" name="campione" id="campione" required>

            <label for="prezzo">Prezzo (€):</label>
            <input type="number" step="0.01" min="0" name="prezzo" id="prezzo" required>

            <label for="quantita">Quantità:</label>
            <input type="number" min="0" name="quantita" id="quantita" required>

            <label for="immagine">Immagine:</label>
            <input type="file" name="immagine" id="immagine" accept="image/*" required>

            <button type="submit">Aggiungi Skin</button>
        </form>
    </div>
<script src="../assets/js/dark-mode.js"></script>
</body>
</html>

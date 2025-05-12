<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();    

$errore = '';
$successo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $campione = $_POST['campione'] ?? '';
    $prezzo = $_POST['prezzo'] ?? '';
    $quantita = $_POST['quantita'] ?? '';
    $immagine = $_FILES['immagine']['name'] ?? '';

    if (!empty($nome) && !empty($campione) && is_numeric($prezzo) && is_numeric($quantita)) {

        if ($immagine) {
            $target_dir = "../assets/img/";
            $target_file = $target_dir . basename($immagine);
            move_uploaded_file($_FILES["immagine"]["tmp_name"], $target_file);
        }

        $stmt = $pdo->prepare("INSERT INTO skin (nome, campione, prezzo, quantita, immagine) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $campione, $prezzo, $quantita, $immagine]);

        $successo = "Skin aggiunta con successo!";
    } else {
        $errore = "Compila tutti i campi correttamente.";
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
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>Aggiungi Nuova Skin</h1>

        <?php if ($errore): ?>
            <div class="errore"><?= htmlspecialchars($errore) ?></div>
        <?php endif; ?>

        <?php if ($successo): ?>
            <div class="successo"><?= htmlspecialchars($successo) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label for="nome">Nome:</label>
            <input type="text" name="nome" id="nome" required>

            <label for="campione">Campione:</label>
            <input type="text" name="campione" id="campione" required>

            <label for="prezzo">Prezzo (€):</label>
            <input type="number" step="0.01" name="prezzo" id="prezzo" required>

            <label for="quantita">Quantità:</label>
            <input type="number" name="quantita" id="quantita" required>

            <label for="immagine">Immagine (opzionale):</label>
            <input type="file" name="immagine" id="immagine" accept="image/*">

            <button type="submit">Aggiungi Skin</button>
        </form>
    </div>
</body>
</html>

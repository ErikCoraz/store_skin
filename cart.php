<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();               // Richiedi che l'utente sia loggato per accedere alla pagina

if (session_status() === PHP_SESSION_NONE) {               // Avvia la sessione se non è attiva
    session_start();
}

$user_id = $_SESSION['user_id'];                // Recupera ID utente dalla sessione
$carrello = $_SESSION['carrello'] ?? [];        // Recupera il carrello dalla sessione (se presente)

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rimuovi_id'])) {            // Rimuovi skin dal carrello
    $idToRemove = (int) $_POST['rimuovi_id'];

    $_SESSION['carrello'] = array_values(array_filter($carrello, fn($id) => $id != $idToRemove));     // Rimuove la skin dalla sessione

    $stmt = $pdo->prepare("DELETE FROM carrello WHERE id_utente = ? AND id_skin = ? LIMIT 1");      // Rimuove la skin dal carrello nel database
    $stmt->execute([$user_id, $idToRemove]);

    header("Location: cart.php");           // Reindirizza per evitare reinvio dati POST
    exit;
}

$carrello = $_SESSION['carrello'] ?? [];          // Recupera carrello aggiornato dalla sessione

$skins = [];                                                                   // Inizializza array per le skin                   
if (!empty($carrello)) {                                                          // Recupera informazioni delle skin nel carrello, se presenti
    $placeholders = implode(',', array_fill(0, count($carrello), '?'));              // Crea un elenco di segnaposto '?' per la query SQL, uno per ogni ID skin nel carrello
    $stmt = $pdo->prepare("SELECT * FROM skin WHERE id IN ($placeholders)");        // Prepara la query SQL per recuperare le skin in base agli ID nel carrello
    $stmt->execute($carrello);                                                   // Esegue la query con gli ID delle skin nel carrello come parametri
    $skins = $stmt->fetchAll();                                               // Salva le skin recuperate nell'array $skins
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nickname'])) {               // Gestione acquisto (quando viene inviato il nickname)
    $nickname = trim($_POST['nickname']);                                         // Rimuove spazi all'inizio o alla fine del nickname
    $esaurite = [];                                                              // Array per eventuali skin esaurite

    $placeholders = implode(',', array_fill(0, count($carrello), '?'));                        // Crea nuovamente i segnaposto per verificare la disponibilità delle skin nel carrello                     
    $stmt = $pdo->prepare("SELECT id, nome, quantita FROM skin WHERE id IN ($placeholders)");      // Ottiene id, nome e quantità delle skin nel carrello
    $stmt->execute($carrello);
    $datiSkin = $stmt->fetchAll();

    foreach ($datiSkin as $skin) {                     // Controlla se ci sono skin esaurite tra quelle selezionate
        if ((int)$skin['quantita'] <= 0) {              // Se la quantità è zero o negativa, la skin è esaurita
            $esaurite[] = $skin['nome'];                // Aggiunge il nome della skin esaurita alla lista
        }
    }

    if (!empty($esaurite)) {
        echo json_encode(['success' => false, 'out_of_stock' => $esaurite]);     // Restituisce un JSON con le skin esaurite
    } else {
        $stmt = $pdo->prepare("UPDATE skin SET quantita = quantita - 1 WHERE id = ? AND quantita > 0");  // Query per diminuire la quantità delle skin acquistate (solo se ancora disponibili)
        foreach ($carrello as $id) {                                                                    // Esegue la query per ogni ID di skin nel carrello, aggiornando la quantità
            $stmt->execute([$id]);
        }

        $_SESSION['carrello'] = [];                                               // Svuota il carrello nella sessione dopo l'acquisto
        $stmt = $pdo->prepare("DELETE FROM carrello WHERE id_utente = ?");        // Svuota il carrello anche nel db
        $stmt->execute([$user_id]);

        echo json_encode(['success' => true]);                          // Restituisce un JSON per confermare l'acquisto
    }

    exit;
}

?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Carrello</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .popup {
            display: none;
            position: fixed;
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border: 2px solid black;
            z-index: 10;
        }
        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 5;
        }
    </style>
</head>
<body>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="cart.php">Carrello</a></li>
        </ul>
    </nav>

    <main class="container">
        <h2>Il tuo carrello</h2>

        <?php if (count($skins) > 0): ?>
    <ul class="cart-list">
        <?php 
        $totale = 0;
        foreach ($skins as $skin): 
            $totale += $skin['prezzo'];
        ?>
            <li style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                <img src="assets/img/<?= htmlspecialchars($skin['immagine']) ?>" alt="<?= htmlspecialchars($skin['nome']) ?>" style="width: 60px; height: auto;">
                <span><?= htmlspecialchars($skin['nome']) ?> - €<?= number_format($skin['prezzo'], 2) ?></span>
                
                <form method="POST" style="margin-left: auto;">
                    <input type="hidden" name="rimuovi_id" value="<?= $skin['id'] ?>">
                    <button type="submit">Rimuovi</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
    <p><strong>Totale: €<?= number_format($totale, 2) ?></strong></p>
    <button onclick="mostraPopup()">Acquista</button>
<?php else: ?>
    <p>Il tuo carrello è vuoto.</p>
<?php endif; ?>


    </main>

    <div class="overlay" id="overlay"></div>

    <div class="popup" id="popup">
    <button onclick="chiudiPopup()" style="position: absolute; top: 5px; right: 10px; background: none; border: none; font-size: 18px; cursor: pointer;">&times;</button>
    <h3>Inserisci il tuo nickname LoL</h3>
    <input type="text" id="nickname" placeholder="Nickname">
    <button onclick="inviaAcquisto()">Invia</button>
</div>


    <div class="popup" id="conferma">
        <h3>Acquisto riuscito!</h3>
        <a href="index.php"><button>Torna alla home</button></a>
    </div>

    <script>
        function mostraPopup() {
            document.getElementById('overlay').style.display = 'block';
            document.getElementById('popup').style.display = 'block';
        }

        function chiudiPopup() {
        document.getElementById('popup').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
}


        function inviaAcquisto() {
            const nickname = document.getElementById('nickname').value;
            if (!nickname) return alert("Inserisci il tuo nickname");

            fetch('cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ nickname })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('popup').style.display = 'none';
                    document.getElementById('conferma').style.display = 'block';
                } else if (data.out_of_stock) {
                    alert("Le seguenti skin sono esaurite:\n\n" + data.out_of_stock.join('\n'));
                }
});

}
    </script>
</body>
</html>

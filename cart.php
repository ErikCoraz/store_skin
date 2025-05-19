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

</head>
<body>
    <nav>
        <ul>
            <img src="assets/img/logo.png" alt="Logo" style="height: 40px;">
            <li><a href="index.php">Home</a></li>
            <li><a href="cart.php">Carrello</a></li>
            <li><button id="toggle-dark">🌓 Dark Mode</button></li>
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
            <li class="cart-item">
                <img src="assets/img/<?= htmlspecialchars($skin['immagine']) ?>" alt="<?= htmlspecialchars($skin['nome']) ?>">
                <span><?= htmlspecialchars($skin['nome']) ?> - €<?= number_format($skin['prezzo'], 2) ?></span>
                <form method="POST">
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
    <button class="close-btn" onclick="chiudiPopup()">&times;</button>
<h3>Dati per l'acquisto</h3>
<form id="acquistoForm" onsubmit="event.preventDefault(); inviaAcquisto();">
    <input type="text" id="nickname" placeholder="Nickname LoL" required>

    <select id="regione" required>
        <option value="">Seleziona Regione</option>
        <option value="EUW">EUW</option>
        <option value="EUNE">EUNE</option>
        <option value="NA">NA</option>
        <option value="KR">KR</option>
        <option value="BR">BR</option>
    </select>

    <input type="text" id="numeroCarta" placeholder="Numero Carta di Credito" required pattern="\d{16}" title="Inserisci 16 cifre">
    <input type="text" id="scadenza" placeholder="MM/AA" required pattern="\d{2}/\d{2}" title="Formato MM/AA">
    <input type="text" id="cvv" placeholder="CVV" required pattern="\d{3}" title="Inserisci un CVV di 3 cifre">
    
    <button type="submit">Invia</button>
</form>

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

        function chiudiPopup() {                                         // Funzione per chhiudere il popup
        document.getElementById('popup').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
}


function inviaAcquisto() {                                                   // Funzione per inviare i dati dell'acquisto
    const nickname = document.getElementById('nickname').value.trim();
    const regione = document.getElementById('regione').value;
    const numeroCarta = document.getElementById('numeroCarta').value.trim();
    const scadenza = document.getElementById('scadenza').value.trim();
    const cvv = document.getElementById('cvv').value.trim();

    if (!nickname || !regione || !numeroCarta || !scadenza || !cvv) {
        return alert("Compila tutti i campi obbligatori.");
    }

    if (!/^\d{16}$/.test(numeroCarta)) {
        return alert("Il numero della carta deve contenere 16 cifre.");
    }

    if (!/^\d{2}\/\d{2}$/.test(scadenza)) {
        return alert("La scadenza deve essere nel formato MM/AA.");
    }
    const [meseStr, annoStr] = scadenza.split('/');                          // Estrae mese e anno dalla stringa di scadenza
    const mese = parseInt(meseStr, 10);                                  
    const anno = parseInt(annoStr, 10) + 2000;                            // es: "25" -> 2025

    const oggi = new Date();                                      // Ottiene la data corrente
    const annoCorrente = oggi.getFullYear();                     
    const meseCorrente = oggi.getMonth() + 1;          // Mesi partono da 0 in JS quindi aggiungiamo +1 (gennaio = 0, 0 + 1 = 1)                       

    if (mese < 1 || mese > 12) {
        return alert("Mese di scadenza non valido.");
    }

    if (anno < annoCorrente || (anno === annoCorrente && mese < meseCorrente)) {
        return alert("La carta di credito è scaduta.");
    }

    if (!/^\d{3}$/.test(cvv)) {
        return alert("Il CVV deve contenere 3 cifre.");
    }
    fetch('cart.php', {                       // fetch invia un richiesta HTTP POST a cart.php
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, // Specifica che i dati sono in formato URL-encoded, come in un form HTML
        body: new URLSearchParams({ nickname })       // Invia solo il nickname
    })
    .then(res => res.json())              // Converte la risposta in JSON
    .then(data => {                        // Gestisce la risposta JSON
        if (data.success) {                             // Se l'acquisto è andato a buon fine
            document.getElementById('popup').style.display = 'none';    // Chiude il popup
            document.getElementById('conferma').style.display = 'block';   // Mostra il popup con scritto "Acquisto riuscito!"
        } else if (data.out_of_stock) {
            alert("Le seguenti skin sono esaurite:\n\n" + data.out_of_stock.join('\n'));      // Mostra un alert con il nome delle skin esaurite
        }
    });
}
    </script>
<script src="assets/js/dark-mode.js"></script>
</body>
</html>

<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isLoggedIn() && isAdmin()) {          // Se l'utente è loggato e admin, reindirizza alla dashboard admin
    header('Location: admin/dashboard.php');
    exit;
}


if (session_status() === PHP_SESSION_NONE) {             // Avvia la sessione se non è già attiva
    session_start();
}

$search = $_GET['search'] ?? '';                         // Recupera i filtri dai parametri GET (se presenti), altrimenti una stringa vuota
$filter = $_GET['filter'] ?? '';               
$prezzoFiltro = $_GET['prezzo'] ?? '';                
$sort = $_GET['sort'] ?? '';


$sql = "SELECT * FROM skin WHERE 1";       // Inizializza la query SQL con una condizione sempre vera (1) per semplificare l'aggiunta di filtri
$params = [];

if (!empty($search)) {                    // Applica filtro per nome, se presente
    $sql .= " AND nome LIKE ?";
    $params[] = "%$search%";
}

if ($filter === 'available') {                   // Applica filtro per disponibilità (disponibili / non disponibili)
    $sql .= " AND quantita > 0";
} elseif ($filter === 'unavailable') {
    $sql .= " AND quantita = 0";
}

if (!empty($prezzoFiltro)) {                               // Applica filtro per prezzo, con supporto per operatori logici
    if (preg_match('/^(<=|>=|<|>)(\d+(?:[.,]\d{1,2})?)$/', $prezzoFiltro, $match)) {
        $operatore = $match[1];
        $valore = str_replace(',', '.', $match[2]);        // Supporta sia virgola sia punto
        $sql .= " AND prezzo $operatore ?";
        $params[] = $valore;
    } elseif (preg_match('/^\d+(?:[.,]\d{1,2})?$/', $prezzoFiltro)) {
        $valore = str_replace(',', '.', $prezzoFiltro);
        $sql .= " AND prezzo = ?";
        $params[] = $valore;
    }
}
switch ($sort) {                        // Applica ordinamento se specificato
    case 'az':
        $sql .= " ORDER BY nome ASC";        // Ordina per nome in ordine crescente
        break;
    case 'za':
        $sql .= " ORDER BY nome DESC";          // Ordina per nome in ordine decrescente
        break;
    case 'prezzo_asc':
        $sql .= " ORDER BY prezzo ASC";           // Ordina per prezzo in ordine crescente
        break;
    case 'prezzo_desc':
        $sql .= " ORDER BY prezzo DESC";           // Ordina per prezzo in ordine decrescente
        break; 
    default:
        break;                               // Ordine di default (dettato dal db), nessun ordinamento specificato
}


$stmt = $pdo->prepare($sql);                  // Prepara la query SQL
$stmt->execute($params);                      // Esegue la query con i parametri forniti
$skins = $stmt->fetchAll();                   // Recupera tutte le skin che soddisfano i criteri di ricerca e filtro


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') { // Verifica se la richiesta è di tipo POST, se esiste l'azione specificata e se è "add_to_cart"
    $skin_id = $_POST['skin_id'];                                               // Estrae l'ID della skin dal corpo della richiesta POST

    if (!isset($_SESSION['carrello'])) {         // Se la variabile di sessione 'carrello' non è ancora stata inizializzata, la crea come array vuoto
        $_SESSION['carrello'] = [];
    }

    if (!in_array($skin_id, $_SESSION['carrello'])) {    // Se l'ID della skin non è già presente nel carrello di sessione, lo aggiunge
        $_SESSION['carrello'][] = $skin_id;
    

        if (isset($_SESSION['user_id'])) {         // Se l'utente è loggato prepara una query per inserire nel database la coppia utente-skin
            $stmt = $pdo->prepare("INSERT IGNORE INTO carrello (id_utente, id_skin) VALUES (?, ?)");       // "INSERT IGNORE" evita duplicati se la riga esiste già
            $stmt->execute([$_SESSION['user_id'], $skin_id]);     // Esegue la query con l'ID dell'utente e della skin
        }
    }
    

    echo json_encode(['success' => true]);  // Restituisce una risposta JSON al client per confermare che la skin è stata aggiunta
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>LoL Skin Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        function aggiungiAlCarrello(skinId) {
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'add_to_cart',
                    skin_id: skinId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("Skin aggiunta al carrello!");
                }
            });
        }
    </script>
</head>
<body>
<nav>
    <ul>
        <img src="assets/img/logo.png" alt="Logo" style="height: 40px;">
        <li><a href="index.php">Home</a></li>
        <li><a href="cart.php">Carrello</a></li>
        <li><button id="toggle-dark">🌓 Dark Mode</button></li>

        <?php if (!isLoggedIn()): ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Registrati</a></li>
        <?php else: ?>
            <li><span>Ciao, <?= htmlspecialchars($_SESSION['username']) ?></span></li>
            <li><a href="logout.php">Logout</a></li>
        <?php endif; ?>
    </ul>
</nav>

    <main class="container">
        <h1>Summoner's Shop</h1>

<form method="GET">
    <input type="text" name="search" placeholder="Cerca skin..." value="<?= htmlspecialchars($search) ?>">
    <input type="text" name="prezzo" placeholder="Prezzo..." value="<?= htmlspecialchars($_GET['prezzo'] ?? '') ?>">
    
    <select name="filter">
        <option value="">Tutte</option>
        <option value="available" <?= $filter === 'available' ? 'selected' : '' ?>>Solo disponibili</option>
        <option value="unavailable" <?= $filter === 'unavailable' ? 'selected' : '' ?>>Non disponibili</option>
    </select>

    <select name="sort">
        <option value="">Ordine di default</option>
        <option value="az" <?= ($_GET['sort'] ?? '') === 'az' ? 'selected' : '' ?>>A-Z</option>
        <option value="za" <?= ($_GET['sort'] ?? '') === 'za' ? 'selected' : '' ?>>Z-A</option>
        <option value="prezzo_asc" <?= ($_GET['sort'] ?? '') === 'prezzo_asc' ? 'selected' : '' ?>>Prezzo crescente</option>
        <option value="prezzo_desc" <?= ($_GET['sort'] ?? '') === 'prezzo_desc' ? 'selected' : '' ?>>Prezzo decrescente</option>
    </select>

    <button type="submit">Cerca</button>
</form>



        <div class="skin-grid">
            <?php foreach ($skins as $skin): ?>
                <div class="skin-card">
                    <img src="assets/img/<?= htmlspecialchars($skin['immagine']) ?>" alt="<?= htmlspecialchars($skin['nome']) ?>">
                    <h3><?= htmlspecialchars($skin['nome']) ?></h3>
                    <p>Prezzo: €<?= number_format($skin['prezzo'], 2) ?></p>
                    <p><?= $skin['quantita'] > 0 ? "Disponibili: {$skin['quantita']}" : "<strong>Out of stock</strong>" ?></p>

                    <?php if (isLoggedIn() && $skin['quantita'] > 0): ?>
                        <button onclick="aggiungiAlCarrello(<?= $skin['id'] ?>)">Aggiungi al carrello</button>
                    <?php elseif (!isLoggedIn()): ?>
                        <a href="login.php">Login per acquistare</a>
                    <?php else: ?>
                        <button disabled>Non disponibile</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="chi-siamo-container">
    <div class="chi-siamo-header" onclick="toggleChiSiamo()">
        <span>Chi siamo</span>
        <span id="freccia">&#9654;</span> 
    </div>
    <div id="chi-siamo-content" class="chi-siamo-content">
        <p>
            Siamo un gruppo di appassionati di League of Legends, nati con l'obiettivo di creare uno spazio sicuro, semplice e moderno dove ogni evocatore possa trovare la propria skin preferita. Il nostro progetto nasce dall’unione tra passione per il gioco e competenze tecniche nel campo del web development, portando alla creazione di questo store online pensato per tutti, dai novizi agli high elo.
            Il nostro store è sviluppato interamente da zero, curando ogni dettaglio sia estetico che funzionale: dalla navigazione intuitiva, alla gestione del carrello, fino a un sistema di amministrazione completo. Crediamo nella trasparenza, nella sicurezza dei dati e in un'interfaccia utente chiara e accessibile.
            Ci impegniamo costantemente per migliorare l’esperienza di acquisto, aggiornando regolarmente il catalogo. Ogni skin disponibile sul sito è selezionata con cura, e ci assicuriamo che l’inventario sia sempre aggiornato in tempo reale.
            Se ami personalizzare i tuoi campioni e vuoi distinguerti sulla Landa, sei nel posto giusto. Questo sito è stato pensato per te: che tu sia un collezionista, un competitivo o semplicemente un fan dell’estetica del gioco, troverai sempre qualcosa che fa al caso tuo.
            Grazie per aver scelto il nostro store. GLHF!
        </p>
    </div>
</div>
    </main>

   <footer>
    <div class="footer-container">
        <p>&copy; 2025 Summoner's Shop. Tutti i diritti riservati.</p>
        <ul class="footer-links">
            <li><a href="https://youtube.com/shorts/41iWg91yFv0?si=SJ_-3r8-p_JZ7alH">Privacy</a></li>
            <li><a href="https://youtube.com/shorts/41iWg91yFv0?si=SJ_-3r8-p_JZ7alH">Termini</a></li>
            <li><a href="https://youtube.com/shorts/41iWg91yFv0?si=SJ_-3r8-p_JZ7alH">Contatti</a></li>
        </ul>
    </div>
</footer> 

<script src="assets/js/dark-mode.js"></script>
</body>
</html>

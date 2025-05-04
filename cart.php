<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {                          // Controlla se l'utente è loggato
    header('Location: login.php');            // Reindirizza alla pagina di login se non lo è
    exit;
}

if (!isset($_SESSION['cart'])) {                  // Inizializza il carrello se non esiste
    $_SESSION['cart'] = [];                       // come array vuoto
}

if (isset($_GET['remove'])) {                         // Gestione della rimozione di un oggetto dal carrello
    $removeId = $_GET['remove'];                      // Ottiene l'ID dell'oggetto da rimuovere
    unset($_SESSION['cart'][$removeId]);              // Rimuove la skin dal carrello
}

 
$cartItems = [];                           // Inizializza le variabili che conterranno gli articoli del carrello e il totale
$total = 0;

if (!empty($_SESSION['cart'])) {                                                       // Se il carrello non è vuoto, ottiene gli oggetti dal database
    $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));            // Crea i segnaposto per la query    
    $stmt = $pdo->prepare("SELECT * FROM skin WHERE id IN ($placeholders)");                  // Prepara la query per ottenere gli oggetti nel carrello
    $stmt->execute(array_keys($_SESSION['cart']));                                       // Esegue la query con gli ID degli oggetti nel carrello
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);                                      // Ottiene gli oggetti dal database

    foreach ($cartItems as &$item) {                                  // Per ogni oggetto nel carrello, calcola il totale e aggiungi la quantità
        $id = $item['id'];                                            // Ottiene l'ID dell'oggetto
        $quantity = $_SESSION['cart'][$id];                           // Quantità scelta dell'utente
        $item['quantity'] = $quantity;                                // Aggiunge la quantità ai dati della skin
        $item['subtotal'] = $quantity * $item['prezzo'];              // Calcola il prezzo totale per quella skin
        $total += $item['subtotal'];                                  // Aggiunge al totale complessivo
    }
}

if (isset($_POST['purchase'])) {                         // Se l'utente ha premuto "Acquista"
    foreach ($_SESSION['cart'] as $id => $qty) {
        $stmt = $pdo->prepare("UPDATE skin SET disponibilita = disponibilita - ? WHERE id = ? AND disponibilita >= ?"); // Esegue una query per decrementare la quantità disponibile nel DB
        $stmt->execute([$qty, $id, $qty]);               // Riduce la quantità dal db solo se c'è abbastanza disponibilità             
    }

    $_SESSION['cart'] = [];                    // Svuota il carrello dopo l'acquisto
    $message = "Acquisto completato!";          
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Carrello</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h1>Il tuo carrello</h1>

    <?php if (isset($message)) echo "<p style='color: green;'>$message</p>"; ?>

    <?php if (empty($cartItems)): ?>
        <p>Il tuo carrello è vuoto.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Skin</th>
                <th>Prezzo</th>
                <th>Quantità</th>
                <th>Subtotale</th>
                <th>Azioni</th>
            </tr>
            <?php foreach ($cartItems as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['nome']) ?></td>
                    <td><?= number_format($item['prezzo'], 2) ?>€</td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= number_format($item['subtotal'], 2) ?>€</td>
                    <td><a href="cart.php?remove=<?= $item['id'] ?>">Rimuovi</a></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3"><strong>Totale:</strong></td>
                <td><strong><?= number_format($total, 2) ?>€</strong></td>
                <td></td>
            </tr>
        </table>

        <form method="post">
            <button type="submit" name="purchase">Acquista</button>
        </form>
    <?php endif; ?>
</body>
</html>

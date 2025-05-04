<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// Gestione aggiunta al carrello (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['skin_id'])) {
    $skin_id = (int) $_POST['skin_id'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$skin_id])) {
        $_SESSION['cart'][$skin_id]++;
    } else {
        $_SESSION['cart'][$skin_id] = 1;
    }

    // Redirect per evitare il reinvio del form
    header("Location: cart.php");
    exit;
}

// Recupera le informazioni sulle skin nel carrello
$cartItems = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
    $stmt = $pdo->prepare("SELECT * FROM skin WHERE id IN ($placeholders)");
    $stmt->execute(array_keys($_SESSION['cart']));
    $skins = $stmt->fetchAll();

    foreach ($skins as $skin) {
        $qty = $_SESSION['cart'][$skin['id']];
        $subtotal = $skin['prezzo'] * $qty;
        $total += $subtotal;
        $cartItems[] = [
            'id' => $skin['id'],
            'nome' => $skin['nome'],
            'immagine' => $skin['immagine'],
            'prezzo' => $skin['prezzo'],
            'quantita' => $qty,
            'subtotal' => $subtotal
        ];
    }
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
            <li><a href="index.php">Home</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
            <li><a href="cart.php">Carrello</a></li>
        </ul>
    </nav>

    <main class="container">
        <h1>Il tuo carrello</h1>

        <?php if (!empty($cartItems)): ?>
            <div class="cart-grid">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <img src="assets/img/<?= htmlspecialchars($item['immagine']) ?>" alt="<?= htmlspecialchars($item['nome']) ?>">
                        <h3><?= htmlspecialchars($item['nome']) ?></h3>
                        <p>Prezzo: €<?= number_format($item['prezzo'], 2) ?></p>
                        <p>Quantità: <?= $item['quantita'] ?></p>
                        <p>Subtotale: €<?= number_format($item['subtotal'], 2) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            <h2>Totale: €<?= number_format($total, 2) ?></h2>
        <?php else: ?>
            <p>Il tuo carrello è vuoto.</p>
        <?php endif; ?>
        
    </main>
</body>
</html>

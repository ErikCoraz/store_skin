<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';

// Query dinamica
$sql = "SELECT * FROM skin WHERE 1";
$params = [];

if (!empty($search)) {
    $sql .= " AND nome LIKE ?";
    $params[] = "%$search%";
}

if ($filter === 'available') {
    $sql .= " AND quantita > 0";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$skins = $stmt->fetchAll();

// Gestione carrello via sessione 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $skin_id = $_POST['skin_id'];

    if (!isset($_SESSION['carrello'])) {
        $_SESSION['carrello'] = [];
    }

    if (!in_array($skin_id, $_SESSION['carrello'])) {
        $_SESSION['carrello'][] = $skin_id;
    
        // Salva anche nel DB
        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO carrello (id_utente, id_skin) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $skin_id]);
        }
    }
    

    echo json_encode(['success' => true]);
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
        <li><a href="index.php">Home</a></li>
        <li><a href="cart.php">Carrello</a></li>

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
        <h1>Store di Skin LoL</h1>

        <form method="GET">
            <input type="text" name="search" placeholder="Cerca skin..." value="<?= htmlspecialchars($search) ?>">
            <select name="filter">
                <option value="">--Filtra--</option>
                <option value="available" <?= $filter === 'available' ? 'selected' : '' ?>>Solo disponibili</option>
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
    </main>
</body>
</html>

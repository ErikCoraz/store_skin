<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$search = $_GET['search'] ?? '';                   // Inizializza variabili per query dinamica
$filter = $_GET['filter'] ?? ''; 

$sql = "SELECT * FROM skin WHERE 1";              // Query di base per recuperare tutte le skin
$params = [];


if (!empty($search)) {                 // Aggiunta filtro di ricerca
    $sql .= " AND nome LIKE ?";
    $params[] = "%$search%";
}


if ($filter === 'available') {                           // Aggiunta filtro (es. solo disponibili)
    $sql .= " AND quantita > 0";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$skins = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoL Skin Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav>
        <ul> 
            <li><a href="index.php">Home</a></li>                  <!-- Menu di navigazione -->
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
            <li><a href="cart.php">Carrello</a></li>
        </ul>
    </nav>

    <main class="container">
        <h1>Benvenuto nello Store di Skin di League of Legends</h1>

        <!-- Barra di ricerca e filtri -->
        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Cerca una skin..." value="<?= htmlspecialchars($search) ?>">
            <select name="filter">
                <option value="">-- Filtra --</option>
                <option value="available" <?= $filter === 'available' ? 'selected' : '' ?>>Solo disponibili</option>
            </select>
            <button type="submit">Cerca</button>
        </form>

        <!-- Visualizzazione skin -->
        <div class="skin-grid">
            <?php if (count($skins) > 0): ?>
                <?php foreach ($skins as $skin): ?>
                    <div class="skin-card">
                        <img src="assets/img/<?= htmlspecialchars($skin['immagine']) ?>" alt="<?= htmlspecialchars($skin['nome']) ?>">
                        <h3><?= htmlspecialchars($skin['nome']) ?></h3>
                        <p>Prezzo: €<?= number_format($skin['prezzo'], 2) ?></p>
                        <p><?= $skin['quantita'] > 0 ? "Disponibile: {$skin['quantita']}" : "<strong>Out of stock</strong>" ?></p>
                        <?php if (isLoggedIn()): ?>
                            <?php if ($skin['quantita'] > 0): ?>
                                <form method="POST" action="cart.php">
                                    <input type="hidden" name="skin_id" value="<?= $skin['id'] ?>">
                                    <button type="submit">Aggiungi al carrello</button>
                                </form>
                            <?php else: ?>
                                <button disabled>Non disponibile</button>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="login.php" class="login-link">Effettua il login per acquistare</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nessuna skin trovata.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>

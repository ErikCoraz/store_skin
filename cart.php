<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'];
$carrello = $_SESSION['carrello'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rimuovi_id'])) {            // Rimuovi skin dal carrello
    $idToRemove = (int) $_POST['rimuovi_id'];

    // Rimuove dalla sessione
    $_SESSION['carrello'] = array_values(array_filter($carrello, fn($id) => $id != $idToRemove));

    // Rimuove dal DB
    $stmt = $pdo->prepare("DELETE FROM carrello WHERE id_utente = ? AND id_skin = ? LIMIT 1");
    $stmt->execute([$user_id, $idToRemove]);

    // Redirect per evitare reinvio POST
    header("Location: cart.php");
    exit;
}

$carrello = $_SESSION['carrello'] ?? [];

$skins = [];
if (!empty($carrello)) {
    $placeholders = implode(',', array_fill(0, count($carrello), '?'));
    $stmt = $pdo->prepare("SELECT * FROM skin WHERE id IN ($placeholders)");
    $stmt->execute($carrello);
    $skins = $stmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nickname'])) {             // Acquista skin
    $nickname = trim($_POST['nickname']);

    $stmt = $pdo->prepare("UPDATE skin SET quantita = quantita - 1 WHERE id = ? AND quantita > 0");
    foreach ($carrello as $id) {
        $stmt->execute([$id]);
    }

    $_SESSION['carrello'] = [];                                      // Svuota carrello
    $stmt = $pdo->prepare("DELETE FROM carrello WHERE id_utente = ?");
    $stmt->execute([$user_id]);

    echo json_encode(['success' => true]);
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
            <li><span>Carrello</span></li>
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
                
                <!-- 🔴 Bottone Rimuovi -->
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
                }
            });
        }
    </script>
</body>
</html>

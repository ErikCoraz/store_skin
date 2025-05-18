<?php
require_once '../includes/db.php';            
require_once '../includes/auth.php';          

requireAdmin();                               // Permette l'accesso solo se l'utente è admin (altrimenti reindirizza)

$stmt = $pdo->query("SELECT * FROM skin ORDER BY id DESC");  // Recupera tutte le skin ordinate dalla più recente
$skinList = $stmt->fetchAll();                // Memorizza tutte le skin in un array
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - LoL Skin Store</title>
    <link rel="stylesheet" href="../assets/css/style.css">  
    <style>

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        main {
            flex: 1; 
        }
    </style>     
</head>
<body>
    <nav>
        <ul>
            <img src="../assets/img/logo.png" alt="Logo" style="height: 40px;">
            <li><a href="../index.php">Home</a></li>
            <li><a href="add_skin.php">Aggiungi Skin</a></li>
            <li><button id="toggle-dark">🌓 Dark Mode</button></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>
<main>

    <div class="container">
        <h1>Dashboard Amministratore</h1>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?= htmlspecialchars($_SESSION['success']) ?></div>        <!-- Messaggio di successo -->
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>



        <table>                <!-- Tabella con tutte le skin presenti nel database -->
            <thead>
                <tr>
                    <th>ID</th>               
                    <th>Nome</th>              
                    <th>Campione</th>          
                    <th>Prezzo</th>            
                    <th>Quantità</th>          
                    <th>Immagine</th>          
                    <th>Azioni</th>            
                </tr>
            </thead>
            <tbody>
                <?php foreach ($skinList as $skin): ?>        <!-- Cicla ogni skin e stampa una riga nella tabella -->
                <tr>
                    <td><?= htmlspecialchars($skin['id']) ?></td>              
                    <td><?= htmlspecialchars($skin['nome']) ?></td>             
                    <td><?= htmlspecialchars($skin['campione']) ?></td>         
                    <td><?= htmlspecialchars($skin['prezzo']) ?>€</td>          
                    <td><?= htmlspecialchars($skin['quantita']) ?></td>        

                    <td>
                        <?php if ($skin['immagine']): ?>                 <!-- Se c'è un'immagine -->
                            <img src="../assets/img/<?= htmlspecialchars($skin['immagine']) ?>" alt="Skin" width="60">    <!-- Miniatura -->
                        <?php else: ?>
                            Nessuna                       <!-- Se non c'è immagine -->
                        <?php endif; ?>
                    </td>

                    <td>
                        <a href="edit_skin.php?id=<?= $skin['id'] ?>">Modifica</a> |
                        <a href="delete_skin.php?id=<?= $skin['id'] ?>" onclick="return confirm('Sei sicuro di voler eliminare questa skin?')">Elimina</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<script src="../assets/js/dark-mode.js"></script>
<footer>
    <div class="footer-container">
        <p>&copy; 2025 Summoner's Shop. Tutti i diritti riservati.</p>
        <ul class="footer-links">
            <li><a>📞 +39 370 319 2498</a></li>
            <li><a>📧 summoner@shop.com</a></li>
            <li><a>🏠 Faenza</a></li>
        </ul>
    </div>
</footer> 
</body>
</html>

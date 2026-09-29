<?php
    require_once "partials/session_check.php";

    // Alleen uitpakken-gebruikers mogen voorraad verwijderen
    if (!($_SESSION['mag_insert'] ?? false)) {
        header("Location: vooraad_beheer.php");
        exit();
    }

    $conn = require_once "partials/dbconnection.php";
    $voorraadId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $fout = '';
    $succes = false;
    $stuk = null;

    if ($voorraadId <= 0) {
        header("Location: vooraad_beheer.php");
        exit();
    }

    // Haal het stuk op
    $stmt = $conn->prepare("SELECT id, leertype, kleur, prijs, status FROM voorraad WHERE id = ?");
    $stmt->bind_param("i", $voorraadId);
    $stmt->execute();
    $stuk = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$stuk) {
        header("Location: vooraad_beheer.php");
        exit();
    }

    // Alleen beschikbare stukken mogen verwijderd worden (niet 'besteld')
    if ($stuk['status'] !== 'beschikbaar') {
        $fout = 'Je kunt alleen beschikbare stukken verwijderen. Dit stuk is al besteld.';
    }

    // Verwijderen bevestigd?
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bevestig']) && $fout === '') {
        // Verwijder eerst de ontvangst_items die naar dit stuk verwijzen
        $deleteItemsStmt = $conn->prepare("DELETE FROM ontvangst_items WHERE voorraad_id = ?");
        $deleteItemsStmt->bind_param("i", $voorraadId);
        $deleteItemsStmt->execute();
        $deleteItemsStmt->close();

        // Verwijder daarna het stuk zelf
        $deleteStmt = $conn->prepare("DELETE FROM voorraad WHERE id = ?");
        $deleteStmt->bind_param("i", $voorraadId);
        
        try {
            if (!$deleteStmt->execute()) {
                throw new Exception("Query mislukt: " . $deleteStmt->error);
            }
            $deleteStmt->close();
            $succes = true;
        } catch (Throwable $e) {
            error_log("Delete voorraad fout: " . $e->getMessage());
            $fout = 'Verwijderen mislukt: ' . $e->getMessage();
            $deleteStmt->close();
        }
    }

    $conn->close();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stuk leer verwijderen - Circuleather</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body class="subpage">
    <div class="pagina-wrapper">
        <a class="terug-link" href="vooraad_beheer.php">&laquo; Terug naar voorraad</a>

        <div class="pagina-kaart invoer-kaart">
            <?php if ($succes): ?>
                <h1>Stuk leer verwijderd</h1>
                <div class="melding melding-succes">
                    Stuk #<?php echo $voorraadId; ?> is verwijderd.
                </div>
                <a class="btn-primary btn-link" href="vooraad_beheer.php">Terug naar voorraad</a>

            <?php else: ?>
                <h1>Stuk leer verwijderen</h1>

                <?php if ($fout !== ''): ?>
                    <div class="melding melding-fout"><?php echo htmlspecialchars($fout); ?></div>
                    <a class="btn-primary btn-link" href="vooraad_beheer.php">Terug naar voorraad</a>
                <?php else: ?>
                    <p class="rij-hint">
                        Weet je zeker dat je dit stuk leer wilt verwijderen? Dit kan niet ongedaan gemaakt worden.
                    </p>

                    <div style="background: #fee; border: 1px solid #f99; padding: 15px; border-radius: 5px; margin: 20px 0;">
                        <p><strong>Stuk #<?php echo $voorraadId; ?></strong></p>
                        <p><?php echo htmlspecialchars($stuk['leertype']); ?> — <?php echo htmlspecialchars($stuk['kleur']); ?> — &euro;<?php echo number_format((float) $stuk['prijs'], 2); ?></p>
                    </div>

                    <form method="POST" action="">
                        <div class="form-acties">
                            <button class="btn-primary" type="submit" name="bevestig" value="1" 
                                    style="background: #d00;">
                                Ja, verwijderen
                            </button>
                            <a class="terug-link" href="vooraad_beheer.php">Annuleren</a>
                        </div>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

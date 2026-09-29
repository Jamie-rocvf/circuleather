<?php
    require_once "partials/session_check.php";

    // Alleen inpakken-gebruikers mogen bestellingen verwijderen
    if (!($_SESSION['mag_orders'] ?? false)) {
        header("Location: orders.php");
        exit();
    }

    $conn = require_once "partials/dbconnection.php";
    $bestellingId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $fout = '';
    $succes = false;

    if ($bestellingId <= 0) {
        header("Location: orders.php");
        exit();
    }

    // Haal de bestelling op + controleer of die verzonden is
    $bestellingStmt = $conn->prepare("SELECT ID, status FROM bestellingen WHERE ID = ?");
    $bestellingStmt->bind_param("i", $bestellingId);
    $bestellingStmt->execute();
    $bestelling = $bestellingStmt->get_result()->fetch_assoc();
    $bestellingStmt->close();

    if (!$bestelling) {
        header("Location: orders.php");
        exit();
    }

    // Alleen verzonden bestellingen mogen verwijderd worden
    if ($bestelling['status'] !== 'verzonden') {
        $fout = 'Je kunt alleen verzonden bestellingen verwijderen.';
    }

    // Verwijderen bevestigd?
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bevestig']) && $fout === '') {
        try {
            $conn->begin_transaction();

            // Stap 1: Haal voorraad-ID's op VOORDAT we iets verwijderen
            $itemsStmt = $conn->prepare(
                "SELECT voorraad_id FROM bestelling_items WHERE bestelling_id = ?"
            );
            $itemsStmt->bind_param("i", $bestellingId);
            $itemsStmt->execute();
            $items = $itemsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $itemsStmt->close();

            // Stap 2: Verwijder bestelling_items (ze verwijzen naar voorraad)
            $deleteItemsStmt = $conn->prepare(
                "DELETE FROM bestelling_items WHERE bestelling_id = ?"
            );
            $deleteItemsStmt->bind_param("i", $bestellingId);
            $deleteItemsStmt->execute();
            $deleteItemsStmt->close();

            // Stap 3: Verwijder voorraad-records en ontvangst_items
            if (!empty($items)) {
                $voorraadIds = array_map(fn($item) => (int) $item['voorraad_id'], $items);
                $placeholders = implode(',', array_fill(0, count($voorraadIds), '?'));
                $types = str_repeat('i', count($voorraadIds));

                // Verwijder ontvangst_items die naar deze voorraad verwijzen
                $deleteOntvangstItemsStmt = $conn->prepare(
                    "DELETE FROM ontvangst_items WHERE voorraad_id IN ($placeholders)"
                );
                $deleteOntvangstItemsStmt->bind_param($types, ...$voorraadIds);
                $deleteOntvangstItemsStmt->execute();
                $deleteOntvangstItemsStmt->close();

                // Verwijder de voorraad zelf
                $deleteVoorraadStmt = $conn->prepare(
                    "DELETE FROM voorraad WHERE id IN ($placeholders)"
                );
                $deleteVoorraadStmt->bind_param($types, ...$voorraadIds);
                $deleteVoorraadStmt->execute();
                $deleteVoorraadStmt->close();
            }

            // Stap 4: Verwijder de bestelling zelf
            $deleteBestellingStmt = $conn->prepare(
                "DELETE FROM bestellingen WHERE ID = ?"
            );
            $deleteBestellingStmt->bind_param("i", $bestellingId);
            $deleteBestellingStmt->execute();
            $deleteBestellingStmt->close();

            $conn->commit();
            $succes = true;

        } catch (Throwable $e) {
            $conn->rollback();
            error_log("Delete bestellingen fout: " . $e->getMessage());
            // Toon de echte fout (voor debugging)
            $fout = 'Verwijderen mislukt: ' . $e->getMessage();
        }
    }

    $conn->close();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bestelling verwijderen - Circuleather</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body class="subpage">
    <div class="pagina-wrapper">
        <a class="terug-link" href="orders.php">&laquo; Terug naar bestellingen</a>

        <div class="pagina-kaart invoer-kaart">
            <?php if ($succes): ?>
                <h1>Bestelling verwijderd</h1>
                <div class="melding melding-succes">
                    Bestelling #<?php echo $bestellingId; ?> en alle bijbehorende leerpartijen zijn verwijderd.
                </div>
                <a class="btn-primary btn-link" href="orders.php">Terug naar bestellingen</a>

            <?php else: ?>
                <h1>Bestelling verwijderen</h1>

                <?php if ($fout !== ''): ?>
                    <div class="melding melding-fout"><?php echo htmlspecialchars($fout); ?></div>
                    <a class="btn-primary btn-link" href="orders.php">Terug naar bestellingen</a>
                <?php else: ?>
                    <p class="rij-hint">
                        <strong>Let op:</strong> Als je deze bestelling verwijdert, worden ook alle bijbehorende leerpartijen uit de voorraad verwijderd.
                        Dit kan niet ongedaan gemaakt worden.
                    </p>

                    <div style="background: #fee; border: 1px solid #f99; padding: 15px; border-radius: 5px; margin: 20px 0;">
                        <p><strong>Bestelling #<?php echo $bestellingId; ?></strong></p>
                        <p>Status: <strong>Verzonden</strong></p>
                        <p style="margin-top: 15px; color: #d00;">
                            Weet je zeker dat je deze bestelling wilt verwijderen?
                        </p>
                    </div>

                    <form method="POST" action="">
                        <div class="form-acties">
                            <button class="btn-primary" type="submit" name="bevestig" value="1" 
                                    style="background: #d00;">
                                Ja, verwijderen
                            </button>
                            <a class="terug-link" href="orders.php">Annuleren</a>
                        </div>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

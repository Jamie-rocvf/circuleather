<?php
    require_once "partials/session_check.php";

    // Alleen inpakken-gebruikers mogen bestellingen wijzigen
    if (!($_SESSION['mag_orders'] ?? false)) {
        header("Location: orders.php");
        exit();
    }

    $conn = require_once "partials/dbconnection.php";
    $bestellingId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $fout = '';
    $succes = false;
    $bestelling = null;

    if ($bestellingId <= 0) {
        header("Location: orders.php");
        exit();
    }

    // Haal de bestelling op
    $stmt = $conn->prepare("SELECT ID, locatie, email, status, besteldatum, verstuurdatum FROM bestellingen WHERE ID = ?");
    $stmt->bind_param("i", $bestellingId);
    $stmt->execute();
    $bestelling = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$bestelling) {
        header("Location: orders.php");
        exit();
    }

    // Formulier verstuurd?
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $status = $_POST['status'] ?? $bestelling['status'];
        $verstuurdatum = trim($_POST['verstuurdatum'] ?? '');

        // Controleer of de status geldig is
        $geldiegeStatussen = ['in behandeling', 'verzonden'];
        if (!in_array($status, $geldiegeStatussen, true)) {
            $fout = 'Ongeldige status.';
        } else {
            // Als status = verzonden, verstuurdatum verplicht
            if ($status === 'verzonden' && $verstuurdatum === '') {
                $fout = 'Verstuurdatum is verplicht bij status "Verzonden".';
            } elseif ($status === 'in behandeling') {
                $verstuurdatum = null; // Zet verstuurdatum op null als "in behandeling"
            } else {
                // Valideer de datum
                $dateCheck = DateTime::createFromFormat('Y-m-d', $verstuurdatum);
                if (!$dateCheck || $dateCheck->format('Y-m-d') !== $verstuurdatum) {
                    $fout = 'Ongeldige datumindeling (gebruik YYYY-MM-DD).';
                }
            }

            if ($fout === '') {
                // Update de bestelling
                if ($status === 'verzonden') {
                    $updateStmt = $conn->prepare(
                        "UPDATE bestellingen SET status = ?, verstuurdatum = ? WHERE ID = ?"
                    );
                    $updateStmt->bind_param("ssi", $status, $verstuurdatum, $bestellingId);
                } else {
                    $updateStmt = $conn->prepare(
                        "UPDATE bestellingen SET status = ?, verstuurdatum = NULL WHERE ID = ?"
                    );
                    $updateStmt->bind_param("si", $status, $bestellingId);
                }

                if ($updateStmt->execute()) {
                    $updateStmt->close();
                    $succes = true;
                } else {
                    $fout = 'Update mislukt: ' . $updateStmt->error;
                    $updateStmt->close();
                }
            }
        }
    }

    $conn->close();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bestelling bewerken - Circuleather</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body class="subpage">
    <div class="pagina-wrapper">
        <a class="terug-link" href="orders.php">&laquo; Terug naar bestellingen</a>

        <div class="pagina-kaart invoer-kaart">
            <h1>Bestelling bewerken</h1>

            <?php if ($succes): ?>
                <div class="melding melding-succes">Bestelling #<?php echo $bestellingId; ?> succesvol bijgewerkt.</div>
                <a class="btn-primary btn-link" href="orders.php">Terug naar bestellingen</a>
            <?php else: ?>
                <?php if ($fout !== ''): ?>
                    <div class="melding melding-fout"><?php echo htmlspecialchars($fout); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="formulier-header">
                        <div>
                            <p><strong>Bestelling #<?php echo $bestellingId; ?></strong></p>
                            <p>Locatie: <?php echo htmlspecialchars($bestelling['locatie']); ?></p>
                            <p>Email: <?php echo htmlspecialchars($bestelling['email']); ?></p>
                            <p>Besteld: <?php echo htmlspecialchars($bestelling['besteldatum']); ?></p>
                        </div>
                    </div>

                    <label>Status
                        <select class="form-input" name="status">
                            <option value="in behandeling" <?php echo $bestelling['status'] === 'in behandeling' ? 'selected' : ''; ?>>In behandeling</option>
                            <option value="verzonden" <?php echo $bestelling['status'] === 'verzonden' ? 'selected' : ''; ?>>Verzonden</option>
                        </select>
                    </label>

                    <label>Verstuurdatum (alleen nodig als "Verzonden")
                        <input class="form-input" type="date" name="verstuurdatum" 
                               value="<?php echo $bestelling['verstuurdatum'] ?: ''; ?>">
                    </label>

                    <div class="form-acties">
                        <button class="btn-primary" type="submit">Opslaan</button>
                        <a class="terug-link" href="orders.php">Annuleren</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

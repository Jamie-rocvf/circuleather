<?php
    require_once "partials/session_check.php";

    // Alleen uitpakken-gebruikers mogen voorraad aanpassen
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
    $stmt = $conn->prepare("SELECT id, leertype, dikteMM, lengteCM, breedteCM, gewichtG, kleur, prijs FROM voorraad WHERE id = ?");
    $stmt->bind_param("i", $voorraadId);
    $stmt->execute();
    $stuk = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$stuk) {
        header("Location: vooraad_beheer.php");
        exit();
    }

    // Formulier verstuurd?
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $leertype = trim($_POST['leertype'] ?? '');
        $dikteMM = (int) ($_POST['dikteMM'] ?? 0);
        $lengteCM = (int) ($_POST['lengteCM'] ?? 0);
        $breedteCM = (int) ($_POST['breedteCM'] ?? 0);
        $kleur = trim($_POST['kleur'] ?? '');
        $prijs = (float) ($_POST['prijs'] ?? 0);
        $gewichtG = (float) ($_POST['gewichtG'] ?? 0);

        if ($leertype === '') {
            $fout = 'Leertype is verplicht.';
        } elseif ($prijs < 0) {
            $fout = 'Prijs kan niet negatief zijn.';
        } else {
            // Update de voorraad
            $updateStmt = $conn->prepare(
                "UPDATE voorraad 
                 SET leertype = ?, dikteMM = ?, lengteCM = ?, breedteCM = ?, gewichtG = ?, kleur = ?, prijs = ? 
                 WHERE id = ?"
            );
            $updateStmt->bind_param(
                "siiiidsi",
                $leertype, $dikteMM, $lengteCM, $breedteCM, $gewichtG, $kleur, $prijs, $voorraadId
            );

            if ($updateStmt->execute()) {
                $updateStmt->close();
                $succes = true;
            } else {
                $fout = 'Update mislukt: ' . $updateStmt->error;
                $updateStmt->close();
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
    <title>Stuk leer bewerken - Circuleather</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body class="subpage">
    <div class="pagina-wrapper">
        <a class="terug-link" href="vooraad_beheer.php">&laquo; Terug naar voorraad</a>

        <div class="pagina-kaart invoer-kaart">
            <h1>Stuk leer bewerken</h1>

            <?php if ($succes): ?>
                <div class="melding melding-succes">Stuk #<?php echo $voorraadId; ?> succesvol bijgewerkt.</div>
                <a class="btn-primary btn-link" href="vooraad_beheer.php">Terug naar voorraad</a>
            <?php else: ?>
                <?php if ($fout !== ''): ?>
                    <div class="melding melding-fout"><?php echo htmlspecialchars($fout); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <label>Leertype
                        <input class="form-input" type="text" name="leertype" 
                               value="<?php echo htmlspecialchars($stuk['leertype']); ?>" required>
                    </label>
                    <label>Dikte (mm)
                        <input class="form-input" type="number" name="dikteMM" 
                               value="<?php echo $stuk['dikteMM']; ?>" min="0">
                    </label>
                    <label>Lengte (cm)
                        <input class="form-input" type="number" name="lengteCM" 
                               value="<?php echo $stuk['lengteCM']; ?>" min="0">
                    </label>
                    <label>Breedte (cm)
                        <input class="form-input" type="number" name="breedteCM" 
                               value="<?php echo $stuk['breedteCM']; ?>" min="0">
                    </label>
                    <label>Kleur
                        <input class="form-input" type="text" name="kleur" 
                               value="<?php echo htmlspecialchars($stuk['kleur']); ?>">
                    </label>
                    <label>Prijs (&euro;)
                        <input class="form-input" type="number" name="prijs" 
                               value="<?php echo $stuk['prijs']; ?>" min="0" step="0.01">
                    </label>
                    <label>Gewicht (g)
                        <input class="form-input" type="number" name="gewichtG" 
                               value="<?php echo $stuk['gewichtG']; ?>" min="0" step="0.1">
                    </label>

                    <div class="form-acties">
                        <button class="btn-primary" type="submit">Opslaan</button>
                        <a class="terug-link" href="vooraad_beheer.php">Annuleren</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
    session_start(); // sessie starten om login-gegevens te kunnen bewaren

    // Al ingelogd? Dan direct door naar de voorraadpagina
    if (isset($_SESSION['ingelogd'])) {
        header("Location: vooraad_beheer.php");
        exit();
    }

    // require_once: laadt het bestand precies 1 keer in; het geeft de databaseverbinding terug
    $conn = require_once "partials/dbconnection.php";
    $foutmelding = '';

    // Alleen uitvoeren als het formulier is verstuurd (POST). "??" geeft een standaardwaarde als iets ontbreekt
    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST['action'] ?? '') === 'inloggen') {
        $gebruiker = trim($_POST['username'] ?? ''); // trim(): haalt spaties voor/achter de tekst weg
        $wachtwoord = $_POST['password'] ?? '';

        // prepare(): maakt een veilige query klaar; het ? wordt later ingevuld (voorkomt SQL-injectie)
        $stmt = $conn->prepare("SELECT id, password, mag_insert, mag_orders FROM gebruikers WHERE username = ?");
        $stmt->bind_param("s", $gebruiker); // bind_param(): vult het ? in ("s" = string)
        $stmt->execute();                   // execute(): voert de query uit
        $rij = $stmt->get_result()->fetch_assoc(); // fetch_assoc(): haalt 1 rij op als array (of null)
        $stmt->close();                     // close(): ruimt de query op

        if (!$rij) {
            $foutmelding = 'Gebruikersnaam niet gevonden.';
        // password_verify(): vergelijkt het ingevoerde wachtwoord met de opgeslagen hash
        } elseif (!password_verify($wachtwoord, $rij['password'])) {
            $foutmelding = 'Onjuist wachtwoord.';
        } else {
            // Login gelukt: bewaar gegevens en rechten in de sessie
            $_SESSION['ingelogd'] = true;
            $_SESSION['username'] = $gebruiker;
            $_SESSION['mag_insert'] = (bool) $rij['mag_insert']; // (bool): zet 1/0 om naar true/false
            $_SESSION['mag_orders'] = (bool) $rij['mag_orders'];
            $_SESSION['last_activity'] = time();
            header("Location: vooraad_beheer.php");
            exit();
        }
    }
    $conn->close(); // databaseverbinding sluiten
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inloggen - Circuleather</title>
    <link rel="stylesheet" type="text/css" href="css/style.css?v=<?php echo filemtime(__DIR__ . '/css/style.css'); ?>">
</head>
<body class="subpage subpage-gecentreerd">
    <div class="pagina-kaart auth-kaart">
        <h1>Inloggen bij Circuleather</h1>

        <?php if ($foutmelding !== ''): ?>
            <div class="melding melding-fout"><?php echo htmlspecialchars($foutmelding); /* htmlspecialchars(): maakt tekst veilig tegen XSS */ ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['timeout'])): ?>
            <div class="melding melding-fout">Je sessie is verlopen, log opnieuw in.</div>
        <?php endif; ?>

        <?php if (isset($_GET['geregistreerd'])): ?>
            <div class="melding melding-succes">Account aangemaakt, je kan nu inloggen.</div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="action" value="inloggen">
            <input class="form-input" type="text" name="username" placeholder="Gebruikersnaam" required>
            <input class="form-input" type="password" name="password" placeholder="Wachtwoord" required>
            <button class="btn-primary" type="submit">Inloggen</button>
        </form>

        <p>Nog geen account? <a href="registreer.php">Registreer hier</a></p>
    </div>
</body>
</html>

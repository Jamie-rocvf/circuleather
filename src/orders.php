<?php
    require_once "partials/session_check.php";

    if (!($_SESSION['mag_orders'] ?? false)) {
        header("Location: vooraad_beheer.php");
        exit();
    }

    // Klik op "Nieuwe bestelling aanmaken": bestelmodus aan en door naar de voorraad,
    // waar je kan filteren en de gewenste stukken leer aanvinkt.
    if (isset($_GET['start_bestelling'])) {
        $_SESSION['bestel_modus'] = true;
        $_SESSION['mandje'] = [];
        header("Location: vooraad_beheer.php");
        exit();
    }

    $conn = require_once "partials/dbconnection.php";
    $fout = '';
    $bestelModus = $_SESSION['bestel_modus'] ?? false;

    // Bestelling opslaan: alle stukken uit het mandje in 1 keer, in 1 transactie
    if ($bestelModus && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $locatie = trim($_POST['locatie'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $besteldatum = $_POST['besteldatum'] ?? date('Y-m-d');
        $mandjeIds = array_values(array_unique(array_map('intval', $_SESSION['mandje'] ?? [])));

        if ($locatie === '' || $email === '') {
            $fout = 'Vul een locatie en email in.';
        } elseif (empty($mandjeIds)) {
            $fout = 'Je hebt nog geen stukken geselecteerd.';
        } else {
            $placeholders = implode(',', array_fill(0, count($mandjeIds), '?'));
            $idTypes = str_repeat('i', count($mandjeIds));

            try {
                $conn->begin_transaction();

                // Prijs en beschikbaarheid altijd server-side opzoeken (en de rijen locken, zodat
                // niemand anders tegelijk hetzelfde stuk kan bestellen).
                $lockStmt = $conn->prepare("SELECT id, prijs FROM voorraad WHERE id IN ($placeholders) AND status = 'beschikbaar' FOR UPDATE");
                $lockStmt->bind_param($idTypes, ...$mandjeIds);
                $lockStmt->execute();
                $beschikbaar = $lockStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $lockStmt->close();

                if (count($beschikbaar) !== count($mandjeIds)) {
                    $conn->rollback();
                    $_SESSION['mandje'] = array_map('intval', array_column($beschikbaar, 'id'));
                    $fout = 'Een of meer gekozen stukken zijn niet meer beschikbaar en zijn uit je selectie gehaald. Controleer je selectie en probeer opnieuw.';
                } else {
                    $bestellingStmt = $conn->prepare("INSERT INTO bestellingen (locatie, email, status, besteldatum) VALUES (?, ?, 'in behandeling', ?)");
                    $bestellingStmt->bind_param("sss", $locatie, $email, $besteldatum);
                    $bestellingStmt->execute();
                    $bestellingId = $bestellingStmt->insert_id;
                    $bestellingStmt->close();

                    // Elk stuk leer is uniek, dus aantal is altijd 1
                    $itemStmt = $conn->prepare("INSERT INTO bestelling_items (bestelling_id, voorraad_id, aantal, prijs) VALUES (?, ?, 1, ?)");
                    $statusStmt = $conn->prepare("UPDATE voorraad SET status = 'besteld' WHERE id = ?");

                    foreach ($beschikbaar as $stuk) {
                        $voorraadId = (int) $stuk['id'];
                        $prijs = (float) $stuk['prijs'];

                        $itemStmt->bind_param("iid", $bestellingId, $voorraadId, $prijs);
                        $itemStmt->execute();

                        $statusStmt->bind_param("i", $voorraadId);
                        $statusStmt->execute();
                    }
                    $itemStmt->close();
                    $statusStmt->close();

                    $conn->commit();
                    unset($_SESSION['bestel_modus'], $_SESSION['mandje']);
                    $conn->close();

                    header("Location: orders.php?besteld=" . $bestellingId);
                    exit();
                }
            } catch (Throwable $e) {
                $conn->rollback();
                error_log($e);
                $fout = 'Opslaan mislukt, probeer het opnieuw.';
            }
        }
    }

    // Gekozen stukken (mandje) ophalen voor het afrond-overzicht
    $mandjeItems = [];
    if ($bestelModus) {
        $mandjeIds = array_values(array_unique(array_map('intval', $_SESSION['mandje'] ?? [])));
        if (!empty($mandjeIds)) {
            $placeholders = implode(',', array_fill(0, count($mandjeIds), '?'));
            $mandjeStmt = $conn->prepare("SELECT id, leertype, lengteCM, breedteCM, kleur, prijs FROM voorraad WHERE id IN ($placeholders) AND status = 'beschikbaar' ORDER BY id");
            $mandjeStmt->bind_param(str_repeat('i', count($mandjeIds)), ...$mandjeIds);
            $mandjeStmt->execute();
            $mandjeItems = $mandjeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $mandjeStmt->close();
        }
    }
    $mandjeTotaal = array_sum(array_column($mandjeItems, 'prijs'));

    $bestellingenStmt = $conn->prepare("SELECT ID, locatie, email, status, besteldatum, verstuurdatum FROM bestellingen ORDER BY besteldatum DESC");
    $bestellingenStmt->execute();
    $bestellingen = $bestellingenStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $bestellingenStmt->close();

    // Let op: hier GEEN status != 'besteld'-filter op voorraad, zoals op vooraad_beheer.php.
    // De items van een bestelling moeten juist wel zichtbaar zijn in de bestelling zelf.
    $itemsStmt = $conn->prepare("
        SELECT bi.bestelling_id, bi.aantal, bi.prijs, v.leertype, v.kleur, v.dikteMM, v.lengteCM, v.breedteCM
        FROM bestelling_items bi
        JOIN voorraad v ON v.id = bi.voorraad_id
        ORDER BY bi.bestelling_id
    ");
    $itemsStmt->execute();
    $alleItems = $itemsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $itemsStmt->close();
    $conn->close();

    // Items groeperen per bestelling, zodat we ze straks per bestelling kunnen tonen
    $itemsPerBestelling = [];
    foreach ($alleItems as $item) {
        $itemsPerBestelling[$item['bestelling_id']][] = $item;
    }
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bestellingen - Circuleather</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body class="subpage">
    <div class="pagina-wrapper">
        <a class="terug-link" href="vooraad_beheer.php">&laquo; Terug naar voorraad</a>

        <div class="pagina-kaart invoer-kaart">
            <h1><?php echo $bestelModus ? 'Bestelling afronden' : 'Nieuwe bestelling'; ?></h1>

            <?php if ($fout !== ''): ?>
                <div class="melding melding-fout"><?php echo htmlspecialchars($fout); ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['besteld'])): ?>
                <div class="melding melding-succes">Bestelling #<?php echo (int) $_GET['besteld']; ?> aangemaakt.</div>
            <?php endif; ?>

            <?php if (!$bestelModus): ?>
                <p class="rij-hint">Je stelt een bestelling samen op de voorraadpagina: daar kan je filteren en de gewenste stukken leer aanvinken. Daarna kom je hier terug om de bestelling af te ronden.</p>
                <a class="btn-primary btn-link" href="orders.php?start_bestelling=1">+ Nieuwe bestelling aanmaken</a>
            <?php elseif (empty($mandjeItems)): ?>
                <p class="geen-data">Je hebt nog geen stukken geselecteerd.</p>
                <div class="form-acties">
                    <a class="btn-primary btn-link" href="vooraad_beheer.php">Stukken kiezen</a>
                    <a class="terug-link" href="vooraad_beheer.php?stop_bestelling=1">Annuleren</a>
                </div>
            <?php else: ?>
                <p class="rij-hint">Dit zijn de stukken die je hebt geselecteerd. Vul de gegevens in en sla de bestelling op; de stukken verdwijnen daarna uit de voorraad (status wordt "besteld").</p>

                <ul class="mandje-lijst">
                    <?php foreach ($mandjeItems as $stuk): ?>
                        <li>
                            <span>#<?php echo (int) $stuk['id']; ?> &mdash; <?php echo htmlspecialchars($stuk['leertype']); ?>, <?php echo htmlspecialchars($stuk['lengteCM']); ?> x <?php echo htmlspecialchars($stuk['breedteCM']); ?> cm, <?php echo htmlspecialchars($stuk['kleur']); ?></span>
                            <span>&euro;<?php echo number_format((float) $stuk['prijs'], 2); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p class="mandje-totaal">Totaal: &euro;<?php echo number_format((float) $mandjeTotaal, 2); ?></p>

                <form method="POST" action="">
                    <div class="formulier-header">
                        <label>Locatie
                            <input class="form-input" type="text" name="locatie" required>
                        </label>
                        <label>Email
                            <input class="form-input" type="email" name="email" required>
                        </label>
                        <label>Besteldatum
                            <input class="form-input" type="date" name="besteldatum" value="<?php echo date('Y-m-d'); ?>">
                        </label>
                    </div>

                    <div class="form-acties">
                        <button class="btn-primary" type="submit">Bestelling opslaan</button>
                        <a class="terug-link" href="vooraad_beheer.php">&laquo; Verder selecteren</a>
                        <a class="terug-link" href="vooraad_beheer.php?stop_bestelling=1">Annuleren</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <div class="pagina-kaart invoer-kaart">
            <h1>Bestellingen</h1>
            <?php if (empty($bestellingen)): ?>
                <p class="geen-data">Er zijn nog geen bestellingen.</p>
            <?php endif; ?>
        </div>

        <?php foreach ($bestellingen as $bestelling): ?>
            <?php
                $items = $itemsPerBestelling[$bestelling['ID']] ?? [];
                $totaal = 0;
                foreach ($items as $item) {
                    $totaal += $item['aantal'] * $item['prijs'];
                }
            ?>
            <div class="pagina-kaart order-kaart">
                <div class="order-kop">
                    <h2>Bestelling #<?php echo (int) $bestelling['ID']; ?> &mdash; <?php echo htmlspecialchars($bestelling['locatie']); ?></h2>
                    <span class="order-status"><?php echo htmlspecialchars($bestelling['status']); ?></span>
                </div>
                <div class="order-meta">
                    <span>Email: <?php echo htmlspecialchars($bestelling['email']); ?></span>
                    <span>Besteld: <?php echo htmlspecialchars($bestelling['besteldatum']); ?></span>
                    <span>Verstuurd: <?php echo $bestelling['verstuurdatum'] ? htmlspecialchars($bestelling['verstuurdatum']) : '-'; ?></span>
                </div>

                <?php if (empty($items)): ?>
                    <p class="geen-data">Geen items in deze bestelling.</p>
                <?php else: ?>
                    <div class="tabel-scroll">
                        <table class="order-items">
                            <thead>
                                <tr>
                                    <th>Leertype</th>
                                    <th>Maat</th>
                                    <th>Dikte</th>
                                    <th>Kleur</th>
                                    <th>Aantal</th>
                                    <th>Prijs</th>
                                    <th>Subtotaal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['leertype']); ?></td>
                                        <td><?php echo htmlspecialchars($item['lengteCM']); ?> x <?php echo htmlspecialchars($item['breedteCM']); ?> cm</td>
                                        <td><?php echo htmlspecialchars($item['dikteMM']); ?> mm</td>
                                        <td><?php echo htmlspecialchars($item['kleur']); ?></td>
                                        <td><?php echo (int) $item['aantal']; ?></td>
                                        <td>&euro;<?php echo number_format((float) $item['prijs'], 2); ?></td>
                                        <td>&euro;<?php echo number_format($item['aantal'] * $item['prijs'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6">Totaal</td>
                                    <td>&euro;<?php echo number_format($totaal, 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>

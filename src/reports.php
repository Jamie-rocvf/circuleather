<?php
    require_once "partials/session_check.php";
    
    $conn = require_once "partials/dbconnection.php";

    // Haal statistieken op
    
    // 1. Totale waarde voorraad (beschikbare stukken)
    $totaalStmt = $conn->prepare("SELECT COUNT(*) as aantal, SUM(prijs) as totaalWaarde FROM voorraad WHERE status = 'beschikbaar'");
    $totaalStmt->execute();
    $totaalRow = $totaalStmt->get_result()->fetch_assoc();
    $totaalStmt->close();
    $aantalBeschikbaar = $totaalRow['aantal'] ?? 0;
    $totaalWaarde = $totaalRow['totaalWaarde'] ?? 0;

    // 2. Aantal stukken per leertype
    $typeStmt = $conn->prepare(
        "SELECT leertype, COUNT(*) as aantal, SUM(prijs) as waarde 
         FROM voorraad WHERE status = 'beschikbaar' 
         GROUP BY leertype ORDER BY aantal DESC"
    );
    $typeStmt->execute();
    $types = $typeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $typeStmt->close();

    // 3. Meest populaire kleuren
    $kleurStmt = $conn->prepare(
        "SELECT kleur, COUNT(*) as aantal 
         FROM voorraad WHERE status = 'beschikbaar' 
         GROUP BY kleur ORDER BY aantal DESC LIMIT 5"
    );
    $kleurStmt->execute();
    $kleuren = $kleurStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $kleurStmt->close();

    // 4. Bestellingen statistieken
    $bestellStmt = $conn->prepare(
        "SELECT status, COUNT(*) as aantal 
         FROM bestellingen GROUP BY status"
    );
    $bestellStmt->execute();
    $bestellStatussen = $bestellStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $bestellStmt->close();

    // 5. Totale omzet bestellingen
    $omzetStmt = $conn->prepare(
        "SELECT SUM(prijs * aantal) as totaal_omzet 
         FROM bestelling_items"
    );
    $omzetStmt->execute();
    $omzetRow = $omzetStmt->get_result()->fetch_assoc();
    $omzetStmt->close();
    $totaalOmzet = $omzetRow['totaal_omzet'] ?? 0;

    // 6. Gemiddelde prijs per leertype
    $gemiddeldStmt = $conn->prepare(
        "SELECT leertype, AVG(prijs) as gemiddelde 
         FROM voorraad WHERE status = 'beschikbaar' 
         GROUP BY leertype ORDER BY gemiddelde DESC"
    );
    $gemiddeldStmt->execute();
    $gemiddelde = $gemiddeldStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $gemiddeldStmt->close();

    // 7. Dikte verdeling
    $dikteStmt = $conn->prepare(
        "SELECT dikteMM, COUNT(*) as aantal 
         FROM voorraad WHERE status = 'beschikbaar' 
         GROUP BY dikteMM ORDER BY dikteMM ASC"
    );
    $dikteStmt->execute();
    $diktes = $dikteStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $dikteStmt->close();

    $conn->close();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapportage - Circuleather</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        .statistiek-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .stat-kaart {
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        .stat-getal {
            font-size: 32px;
            font-weight: bold;
            color: #0066cc;
            margin: 10px 0;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .tabel-container {
            overflow-x: auto;
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f5f5f5;
            font-weight: bold;
            color: #333;
        }
        tr:hover {
            background: #fafafa;
        }
        .label-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-beschikbaar {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .status-besteld {
            background: #fff3e0;
            color: #e65100;
        }
        .status-verzonden {
            background: #e3f2fd;
            color: #1565c0;
        }
    </style>
</head>
<body>
    <div class="pagina-wrapper">
        <a class="terug-link" href="vooraad_beheer.php">&laquo; Terug naar voorraad</a>

        <h1>Rapportage & Statistieken</h1>

        <!-- Samenvattingstatistieken -->
        <div class="statistiek-grid">
            <div class="stat-kaart">
                <div class="stat-label">Beschikbare stukken</div>
                <div class="stat-getal"><?php echo $aantalBeschikbaar; ?></div>
            </div>
            <div class="stat-kaart">
                <div class="stat-label">Totale waarde voorraad</div>
                <div class="stat-getal">&euro;<?php echo number_format((float) $totaalWaarde, 2); ?></div>
            </div>
            <div class="stat-kaart">
                <div class="stat-label">Totale omzet bestellingen</div>
                <div class="stat-getal">&euro;<?php echo number_format((float) $totaalOmzet, 2); ?></div>
            </div>
        </div>

        <!-- Voorraad per leertype -->
        <div class="pagina-kaart">
            <h2>Voorraad per leertype</h2>
            <div class="tabel-container">
                <table>
                    <thead>
                        <tr>
                            <th>Leertype</th>
                            <th>Aantal stukken</th>
                            <th>Totale waarde</th>
                            <th>Gemiddelde prijs/stuk</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($types as $type): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($type['leertype']); ?></strong></td>
                                <td><?php echo $type['aantal']; ?></td>
                                <td>&euro;<?php echo number_format((float) $type['waarde'], 2); ?></td>
                                <td>&euro;<?php echo number_format((float) ($type['waarde'] / $type['aantal']), 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Meest populaire kleuren -->
        <div class="pagina-kaart">
            <h2>Meest populaire kleuren (top 5)</h2>
            <div class="tabel-container">
                <table>
                    <thead>
                        <tr>
                            <th>Kleur</th>
                            <th>Aantal stukken</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kleuren as $kleur): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($kleur['kleur']); ?></td>
                                <td><?php echo $kleur['aantal']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Dikte verdeling -->
        <div class="pagina-kaart">
            <h2>Verdeling naar dikte (mm)</h2>
            <div class="tabel-container">
                <table>
                    <thead>
                        <tr>
                            <th>Dikte (mm)</th>
                            <th>Aantal stukken</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($diktes as $dikte): ?>
                            <tr>
                                <td><?php echo $dikte['dikteMM']; ?> mm</td>
                                <td><?php echo $dikte['aantal']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bestellingen status -->
        <div class="pagina-kaart">
            <h2>Bestellingen per status</h2>
            <div class="tabel-container">
                <table>
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Aantal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bestellStatussen as $stat): ?>
                            <tr>
                                <td>
                                    <span class="label-status status-<?php echo htmlspecialchars(str_replace(' ', '', $stat['status'])); ?>">
                                        <?php echo htmlspecialchars($stat['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $stat['aantal']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Gemiddelde prijzen -->
        <div class="pagina-kaart">
            <h2>Gemiddelde prijs per leertype</h2>
            <div class="tabel-container">
                <table>
                    <thead>
                        <tr>
                            <th>Leertype</th>
                            <th>Gemiddelde prijs</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gemiddelde as $g): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($g['leertype']); ?></td>
                                <td>&euro;<?php echo number_format((float) $g['gemiddelde'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$conn = require_once "partials/dbconnection.php";

// Filters (waardes komen automatisch uit de database)
$geselecteerdLeertype = trim($_GET['leertype'] ?? '');
$geselecteerdKleur = trim($_GET['kleur'] ?? '');
$geselecteerdDikte = trim($_GET['dikte'] ?? '');
$geselecteerdMaat = trim($_GET['maat'] ?? '');

$leertypesStmt = $conn->prepare("SELECT DISTINCT leertype FROM voorraad ORDER BY leertype");
$leertypesStmt->execute();
$leertypes = $leertypesStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$leertypesStmt->close();

$kleurenStmt = $conn->prepare("SELECT DISTINCT kleur FROM voorraad ORDER BY kleur");
$kleurenStmt->execute();
$kleuren = $kleurenStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$kleurenStmt->close();

$diktesStmt = $conn->prepare("SELECT DISTINCT dikteMM FROM voorraad ORDER BY dikteMM");
$diktesStmt->execute();
$diktes = $diktesStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$diktesStmt->close();

$matenStmt = $conn->prepare("SELECT DISTINCT maatCMXCM FROM voorraad ORDER BY maatCMXCM");
$matenStmt->execute();
$maten = $matenStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$matenStmt->close();

$where = [];
$params = [];
$types = '';

if ($geselecteerdLeertype !== '') {
    $where[] = 'leertype = ?';
    $params[] = $geselecteerdLeertype;
    $types .= 's';
}
if ($geselecteerdKleur !== '') {
    $where[] = 'kleur = ?';
    $params[] = $geselecteerdKleur;
    $types .= 's';
}
if ($geselecteerdDikte !== '') {
    $where[] = 'dikteMM = ?';
    $params[] = $geselecteerdDikte;
    $types .= 's';
}
if ($geselecteerdMaat !== '') {
    $where[] = 'maatCMXCM = ?';
    $params[] = $geselecteerdMaat;
    $types .= 's';
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

// filterQuery zorgt dat de paginering-links de actieve filters onthouden
$filterQuery = '';
if ($geselecteerdLeertype !== '') {
    $filterQuery .= '&leertype=' . urlencode($geselecteerdLeertype);
}
if ($geselecteerdKleur !== '') {
    $filterQuery .= '&kleur=' . urlencode($geselecteerdKleur);
}
if ($geselecteerdDikte !== '') {
    $filterQuery .= '&dikte=' . urlencode($geselecteerdDikte);
}
if ($geselecteerdMaat !== '') {
    $filterQuery .= '&maat=' . urlencode($geselecteerdMaat);
}

// Paginering
$itemsPerPagina = 12;
$huidigePagina = isset($_GET['pagina']) ? max(1, (int) $_GET['pagina']) : 1;
$offset = ($huidigePagina - 1) * $itemsPerPagina;

$totaalStmt = $conn->prepare("SELECT COUNT(*) AS totaal FROM voorraad" . $whereSql);
if ($params) {
    $totaalStmt->bind_param($types, ...$params);
}
$totaalStmt->execute();
$totaalRij = $totaalStmt->get_result()->fetch_assoc();
$totaalPaginas = max(1, (int) ceil($totaalRij['totaal'] / $itemsPerPagina));
$totaalStmt->close();

// Toon max 5 paginaknoppen, gecentreerd rond de huidige pagina
$maxPaginaKnoppen = 5;
$vanafPagina = max(1, $huidigePagina - intdiv($maxPaginaKnoppen, 2));
$totPagina = min($totaalPaginas, $vanafPagina + $maxPaginaKnoppen - 1);
$vanafPagina = max(1, $totPagina - $maxPaginaKnoppen + 1);

$selectParams = $params;
$selectParams[] = $itemsPerPagina;
$selectParams[] = $offset;

$stmt = $conn->prepare("SELECT id, leertype, dikteMM, maatCMXCM, gewichtG, kleur, prijs FROM voorraad" . $whereSql . " LIMIT ? OFFSET ?");
$stmt->bind_param($types . "ii", ...$selectParams);
$stmt->execute();
$producten = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voorraad beheer</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>

    <header class="header">
        <div class="logo"><img src="png/logo.png" alt="logo"></div>
        <div class="titel"><img src="png/titel.png" alt="titel"></div>
        <div class="inNout">
            <div class="inNoutB"><button>insert</button></div>
            <div class="inNoutB"><button>orders</button></div>
        </div>
    </header>

    <div class="main-container">

        <aside class="sidebar">
            <form class="filter-opties" method="get">
                <p>Filter opties</p>
                <div class="filter-selects">
                <select class="filter-select" name="leertype" onchange="this.form.submit()">
                    <option value="">Alle leertypes</option>
                    <?php foreach ($leertypes as $rij): ?>
                        <option value="<?php echo htmlspecialchars($rij['leertype']); ?>"<?php echo $rij['leertype'] === $geselecteerdLeertype ? ' selected' : ''; ?>><?php echo htmlspecialchars($rij['leertype']); ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="filter-select" name="kleur" onchange="this.form.submit()">
                    <option value="">Alle kleuren</option>
                    <?php foreach ($kleuren as $rij): ?>
                        <option value="<?php echo htmlspecialchars($rij['kleur']); ?>"<?php echo $rij['kleur'] === $geselecteerdKleur ? ' selected' : ''; ?>><?php echo htmlspecialchars($rij['kleur']); ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="filter-select" name="dikte" onchange="this.form.submit()">
                    <option value="">Alle diktes</option>
                    <?php foreach ($diktes as $rij): ?>
                        <option value="<?php echo htmlspecialchars($rij['dikteMM']); ?>"<?php echo $rij['dikteMM'] == $geselecteerdDikte && $geselecteerdDikte !== '' ? ' selected' : ''; ?>><?php echo htmlspecialchars($rij['dikteMM']); ?> mm</option>
                    <?php endforeach; ?>
                </select>
                <select class="filter-select" name="maat" onchange="this.form.submit()">
                    <option value="">Alle maten</option>
                    <?php foreach ($maten as $rij): ?>
                        <option value="<?php echo htmlspecialchars($rij['maatCMXCM']); ?>"<?php echo $rij['maatCMXCM'] == $geselecteerdMaat && $geselecteerdMaat !== '' ? ' selected' : ''; ?>><?php echo htmlspecialchars($rij['maatCMXCM']); ?> cm</option>
                    <?php endforeach; ?>
                </select>
                </div>
                <a href="vooraad_beheer.php" class="reset-filters">Reset filters</a>
            </form>
            <nav class="pagination">
                <div class="page-numbers">
                    <?php for ($i = $vanafPagina; $i <= $totPagina; $i++): ?>
                        <a class="page-btn<?php echo $i === $huidigePagina ? ' active' : ''; ?>" href="?pagina=<?php echo $i; ?><?php echo $filterQuery; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
                <div class="page-nav">
                    <a class="page-btn<?php echo $huidigePagina <= 1 ? ' disabled' : ''; ?>" href="?pagina=<?php echo max(1, $huidigePagina - 1); ?><?php echo $filterQuery; ?>">&laquo;</a>
                    <a class="page-btn<?php echo $huidigePagina >= $totaalPaginas ? ' disabled' : ''; ?>" href="?pagina=<?php echo min($totaalPaginas, $huidigePagina + 1); ?><?php echo $filterQuery; ?>">&raquo;</a>
                </div>
            </nav>
        </aside>

        <main class="content">
            <div class="product-grid">
                <?php foreach ($producten as $product): ?>
                    <div class="product-card">
                        <div class="naam-box"><span>leertype</span><span><?php echo htmlspecialchars($product['leertype']); ?></span></div>
                        <div class="specs">
                            <div class="spec-row"><span>Dikte</span><span><?php echo htmlspecialchars($product['dikteMM']); ?> mm</span></div>
                            <div class="spec-row"><span>Maat</span><span><?php echo htmlspecialchars($product['maatCMXCM']); ?> cm</span></div>
                            <div class="spec-row"><span>Gewicht</span><span><?php echo htmlspecialchars($product['gewichtG']); ?> g</span></div>
                            <div class="spec-row"><span>Kleur</span><span><?php echo htmlspecialchars($product['kleur']); ?></span></div>
                        </div>
                        <div class="hoeveelheid-box">&euro;<?php echo htmlspecialchars(number_format((float) $product['prijs'], 2)); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>

    </div>
</body>
</html>
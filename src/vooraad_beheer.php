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
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
    --leer-donker: #3b2a1a;
    --leer-midden: #a67c52;
    --leer-licht: #f5ead9;
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
}

html {
    height: 100%;
}

body {
    height: 100%;
    background-image: url('png/background.jpg');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    color: var(--leer-donker);
}

/* Header Styling */
.header {
    position: relative;
    height: 13%;
    flex-shrink: 0;
    background-image: url('png/leer header.jpg');
    background-size: fill;
    background-position: center;
    border-bottom: 4px solid var(--leer-donker);
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
}

.logo,
.titel {
    height: 60%;
    background-color: rgba(166, 124, 82, 0.92);
    border: 2px solid var(--leer-donker);
    border-radius: 12%;
    padding: 0 3%;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.logo {
    position: absolute;
    left: 2%;
    top: 50%;
    transform: translateY(-50%);
    font-weight: bold;
}

.logo img,
.titel img {
    height: 80%;
    width: auto;
}

/* Main Layout (Sidebar + Content) */
.main-container {
    display: flex;
    flex: 1;
    min-height: 0;
    overflow: hidden;
}

/* Sidebar Styling */
.sidebar {
    width: 18%;
    min-height: 100%;
    overflow: hidden;
    background-image: url('png/hout.jpg');
    background-size: fill;
    background-repeat: repeat;
    background-position: center;
    border-right: 3px solid var(--leer-donker);
    display: flex;
    flex-direction: column;
    padding: 3%;
    gap: 2%;
}

.filter-opties {
    flex: 1;
    display: flex;
    flex-direction: column;
    border: 2px solid var(--leer-donker);
    padding: 7%;
    background-color: rgba(245, 234, 217, 0.92);
    font-size: 1rem;
    font-weight: 600;
}

.filter-opties p {
    flex-shrink: 0;
    margin-bottom: 4%;
}

.filter-selects {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.filter-select {
    width: 100%;
    padding: 3% 4%;
    border: 2px solid var(--leer-donker);
    background-color: var(--leer-licht);
    font-size: 0.9rem;
    font-weight: 400;
    color: var(--leer-donker);
}

.filter-select:last-child {
    margin-bottom: 50%;
}

.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 2%;
}

.page-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 16%;
    aspect-ratio: 1;
    border: 1px solid var(--leer-donker);
    border-radius: 20%;
    background-color: var(--leer-licht);
    color: var(--leer-donker);
    font-size: 0.8rem;
    text-decoration: none;
    transition: background-color 0.15s ease, color 0.15s ease;
}

.page-btn:hover {
    background-color: var(--leer-midden);
    color: #fff;
}

.page-btn.active {
    background-color: var(--leer-donker);
    color: #fff;
    font-weight: 600;
}

.page-btn.disabled {
    opacity: 0.4;
    pointer-events: none;
}

/* Content & Grid Styling */
.content {
    flex: 1;
    min-height: 0;
    display: flex;
    flex-direction: column;
    padding: 3% 4%;
    overflow: hidden;
}

.product-grid {
    flex: 1;
    min-height: 0;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    grid-template-rows: repeat(3, minmax(0, 1fr));
    gap: 3%;
}
@media (max-width: 1000px) {
    .product-grid {
        grid-template-columns: repeat(auto-fit, minmax(35%, 1fr));
    }
}

@media (max-width: 600px) {
    .product-grid {
        grid-template-columns: repeat(auto-fit, minmax(50%, 1fr));
    }
}

.product-card {
    border: 2px solid var(--leer-donker);
    background-color: var(--leer-licht);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4%;
    padding: 5%;
    min-height: 0;
    overflow: hidden;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 14px rgba(0, 0, 0, 0.25);
}

/* Specifieke elementen binnen de productkaarten */
.naam-box {
    width: 100%;
    text-align: center;
    font-size: 0.95rem;
    font-weight: 600;
    flex-shrink: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-transform: capitalize;
    justify-content: space-between;
    display: flex;
    padding: 0 6%;
}

.naam-box span:first-child {
    font-weight: 600;
}

.specs {
    width: 100%;
    flex: 1;
    min-height: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 4%;
    overflow: hidden;
}

.spec-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.8rem;
    padding: 0 6%;
}

.spec-row span:first-child {
    color: var(--leer-midden);
    font-weight: 600;
}

.hoeveelheid-box {
    flex-shrink: 0;
    border: 1px solid var(--leer-midden);
    padding: 2% 8%;
    font-size: 0.85rem;
    font-weight: 700;
    color: #fff;
    background-color: var(--leer-donker);
}
</style>
</head>
<body>

    <header class="header">
        <div class="logo"><img src="png/logo.png" alt="logo"></div>
        <div class="titel"><img src="png/titel.png" alt="titel"></div>
        <div class="inNout">
            <div class="insert"><button>insert</button></div>
            <div class="orders"><button>orders</button></div>
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
            </form>
            <nav class="pagination">
                <a class="page-btn<?php echo $huidigePagina <= 1 ? ' disabled' : ''; ?>" href="?pagina=<?php echo max(1, $huidigePagina - 1); ?><?php echo $filterQuery; ?>">&laquo;</a>
                <?php for ($i = 1; $i <= $totaalPaginas; $i++): ?>
                    <a class="page-btn<?php echo $i === $huidigePagina ? ' active' : ''; ?>" href="?pagina=<?php echo $i; ?><?php echo $filterQuery; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                <a class="page-btn<?php echo $huidigePagina >= $totaalPaginas ? ' disabled' : ''; ?>" href="?pagina=<?php echo min($totaalPaginas, $huidigePagina + 1); ?><?php echo $filterQuery; ?>">&raquo;</a>
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
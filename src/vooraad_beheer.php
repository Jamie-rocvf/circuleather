<?php
$producten = array_fill(0, 12, [
    'naam' => 'naam',
    'hoeveelheid' => 'hoeveelheid',
    'afbeelding' => 'png/placeholder.jpeg'
]);

// Paginering (placeholder: $totaalPaginas komt straks uit een COUNT-query, $huidigePagina stuurt de LIMIT/OFFSET)
$huidigePagina = isset($_GET['pagina']) ? max(1, (int) $_GET['pagina']) : 1;
$totaalPaginas = 3;
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
    background-size: cover;
    background-position: center;
    border-bottom: 4px solid var(--leer-donker);
    display: flex;
    align-items: center;
    justify-content: center;
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
    height: 60%;
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
    flex-shrink: 0;
    min-height: 0;
    overflow: hidden;
    background-image: url('png/hout.jpg');
    background-size: cover;
    background-position: center;
    border-right: 3px solid var(--leer-donker);
    display: flex;
    flex-direction: column;
    padding: 3%;
    gap: 2%;
    box-shadow: 2px 0 6px rgba(0, 0, 0, 0.2);
}

.zoekbalk input {
    width: 100%;
    border: 2px solid var(--leer-donker);
    border-radius: 8%;
    padding: 4% 6%;
    background-color: var(--leer-licht);
    font-size: 0.95rem;
    outline: none;
    transition: box-shadow 0.2s ease;
}

.zoekbalk input:focus {
    box-shadow: 0 0 0 3px rgba(166, 124, 82, 0.5);
}

.filter-opties {
    flex: 1;
    border: 2px solid var(--leer-donker);
    border-radius: 4%;
    padding: 5%;
    background-color: rgba(245, 234, 217, 0.92);
    font-size: 1rem;
    font-weight: 600;
}

.recycle-icon {
    margin-top: auto;
    padding: 5%;
}

.recycle-icon img {
    width: 100%;
    border-radius: 8%;
    display: block;
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

.product-card {
    border: 2px solid var(--leer-donker);
    border-radius: 8%;
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
.foto-box {
    border: 2px solid #d8c3a5;
    border-radius: 8%;
    width: 100%;
    flex: 1;
    min-height: 0;
    background-color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.foto-box img {
    max-width: 70%;
    max-height: 100%;
    object-fit: contain;
}

.naam-box {
    width: 100%;
    text-align: center;
    font-size: 0.95rem;
    font-weight: 600;
    flex-shrink: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.hoeveelheid-box {
    flex-shrink: 0;
    border: 1px solid var(--leer-midden);
    border-radius: 50%;
    padding: 2% 8%;
    font-size: 0.8rem;
    color: #6b4b2a;
    background-color: #fff;
}
</style>
</head>
<body>

    <header class="header">
        <div class="logo"><img src="png/logo.png" alt="logo"></div>
        <div class="titel"><img src="png/titel.png" alt="titel"></div>
    </header>

    <div class="main-container">

        <aside class="sidebar">
            <div class="zoekbalk">
                <input type="text" id="searchInput" placeholder="Zoeken...">
            </div>
            <div class="filter-opties">
                <p>Filter opties</p>
            </div>
            <div class="recycle-icon">
                <img src="png/recycle.png" alt="recycle">
            </div>
            <nav class="pagination">
                <a class="page-btn<?php echo $huidigePagina <= 1 ? ' disabled' : ''; ?>" href="?pagina=<?php echo max(1, $huidigePagina - 1); ?>">&laquo;</a>
                <?php for ($i = 1; $i <= $totaalPaginas; $i++): ?>
                    <a class="page-btn<?php echo $i === $huidigePagina ? ' active' : ''; ?>" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                <a class="page-btn<?php echo $huidigePagina >= $totaalPaginas ? ' disabled' : ''; ?>" href="?pagina=<?php echo min($totaalPaginas, $huidigePagina + 1); ?>">&raquo;</a>
            </nav>
        </aside>

        <main class="content">
            <div class="product-grid">
                <?php foreach ($producten as $index => $product): ?>
                    <div class="product-card">
                        <div class="foto-box"><img src="<?php echo htmlspecialchars($product['afbeelding']); ?>" alt="<?php echo htmlspecialchars($product['naam']); ?>"></div>
                        <div class="naam-box"><?php echo htmlspecialchars($product['naam']); ?></div>
                        <div class="hoeveelheid-box"><?php echo htmlspecialchars($product['hoeveelheid']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>

    </div>

    <script src="script.js"></script>
</body>
</html>
<?php
$producten = array_fill(0, 8, [
    'naam' => 'naam',
    'hoeveelheid' => 'hoeveelheid',
    'afbeelding' => 'placeholder.jpg'
]);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>vooraad beheer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="header">
        <div class="logo"><img src="png/logo.png" alt="logo" style="width:50%;height:50%;"></div>
        <div class="titel"><img src="png/titel.png" alt="titel" style="width:50%;height:50%;"></div>
    </header>

    <div class="main-container">

        <aside class="sidebar">
            <div class="zoekbalk">
                <input type="text" id="searchInput" placeholder="zoek balk">
            </div>
            <div class="filter-opties">
                <p>filter opties</p>
            </div>
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
    <div>
        <p>Footer content</p>
    </div>
    <script src="script.js"></script>
</body>
</html>
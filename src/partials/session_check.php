<?php
session_start(); // session_start(): start of hervat de sessie (hier onthoudt PHP wie is ingelogd)

$timeout = 54000; // 90 minuten

// isset(): controleert of een variabele bestaat. Niet ingelogd? Dan terug naar login
if (!isset($_SESSION['ingelogd'])) {
    // header("Location: ..."): stuurt de bezoeker door naar een andere pagina
    header("Location: login.php");
    exit();
}

// time(): huidige tijd in seconden. Te lang inactief? Dan sessie beëindigen
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_destroy(); // session_destroy(): verwijdert alle sessiegegevens
    header("Location: login.php?timeout=1");
    exit();
}

// Onthoud het moment van de laatste activiteit voor de volgende controle
$_SESSION['last_activity'] = time();

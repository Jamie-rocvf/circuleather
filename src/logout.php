<?php
session_start();   // sessie ophalen zodat we hem kunnen verwijderen
session_destroy(); // alle sessiegegevens wissen = uitgelogd
header("Location: login.php"); // terugsturen naar de loginpagina
exit(); // script stoppen zodat er niks meer wordt uitgevoerd
?>

<?php
// Inloggegevens voor de database ("mysql" is de servicenaam uit docker-compose.yml)
$servername = "mysql";
$username = "root";
$password = "password";

// try/catch: vangt fouten op zodat de site niet crasht met een lelijke foutmelding
try {
    // new mysqli(): maakt de verbinding met de database
    $conn = new mysqli($servername, $username, $password, "circuleather");
    if ($conn->connect_error) {
        error_log($conn->connect_error); // error_log(): schrijft de fout weg in het serverlog
        exit($conn->connect_error); // exit(): stopt het script direct
    }
} catch (Exception $e) {
    error_log($e);
    exit($e->getMessage());
}

// return: geeft de verbinding terug aan het bestand dat dit include (via require_once)
return $conn;

<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
?>
<?php

// Datos credenciales de mi base de datos, cámbielos a su conveniencia
include("../other_resources/BBDD_credentials.php");

// Intento de conexión con la base de datos
try {
    $link = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($link === false) {
        die("ERROR: Could not connect. " . mysqli_connect_error());
    }
} catch (Exception $e) {
    echo "No se pudo conectar a la Base de datos";
    echo $e->getMessage();
    exit;
}
?> 
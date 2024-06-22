<?php
$servername = "localhost";
$username = "manutenzione_serv";
$password = "manutenzione";
$database = "manutenzione";

$mysqli = new mysqli($servername, $username, $password, $database);

if ($mysqli->connect_error) {
    die("Connessione al database fallita: " . $mysqli->connect_error);
}
?>

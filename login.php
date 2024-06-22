<?php
include 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT id, password_hash, livello_accesso FROM utenti WHERE username = ?";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $password_hash, $livello_accesso);

        if ($stmt->num_rows > 0) {
            $stmt->fetch();
            if (password_verify($password, $password_hash)) {
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['livello_accesso'] = $livello_accesso;
                error_log("Login effettuato.");
                header("location: gestione_entita.php");
            } else {
                error_log("Password errata.");
            }
        } else {
            error_log("Username non trovato.");
        }
        $stmt->close();
    }else{
        error_log("login.php: POST, prepare failed: (" . $mysqli->errno . ") " . $mysqli->error);
    }
    $mysqli->close();
}
?>

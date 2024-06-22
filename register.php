<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Verifica se l'username è già in uso
    $query = "SELECT id FROM utenti WHERE username = ?";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "Username già in uso";
            $stmt->close();
        } else {
            // Inserisce il nuovo utente
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $livello_accesso = 'base'; // Utente base di default
            $query = "INSERT INTO utenti (username, email, password_hash, livello_accesso) VALUES (?, ?, ?, ?)";
            if ($stmt = $mysqli->prepare($query)) {
                $stmt->bind_param("ssss", $username, $email, $password_hash, $livello_accesso);
                $stmt->execute();
                echo "Registrazione avvenuta con successo";
                $stmt->close();
            }
        }
    }
    $mysqli->close();
}
?>

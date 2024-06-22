<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("location: login.html");
    exit;
}

include 'config.php';

$livello_accesso = $_SESSION['livello_accesso'];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestione Infrastrutture</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <h1>Benvenuto, <?php echo $_SESSION['username']; ?></h1>
    <?php if ($livello_accesso == 'admin'): ?>
        <div id="admin-section">
            <h2>Sezione Admin</h2>
            <a href="gestione_entita.php">Gestione Entità</a><br>
            <a href="gestione_checklist.php">Gestione Checklist</a>
        </div>
    <?php endif; ?>
    <div id="user-section">
        <h2>Entità e Scadenze</h2>
        <!-- Sezione per manutentori e admin -->
        <div id="elenco-entita">
            <!-- Qui verrà caricato l'elenco delle entità e scadenze -->
        </div>
    </div>

    <script>
        $(document).ready(function(){
            $("#elenco-entita").load("elenco_entita.php");
        });
    </script>
</body>
</html>

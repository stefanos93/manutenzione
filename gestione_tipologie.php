<?php
session_start();
include 'config.php';

// Verifica se l'utente è loggato e ha il livello di accesso corretto
if (!isset($_SESSION['loggedin'])){
    header('Location: login.html');
    exit;
}

// Gestione inserimento di una nuova tipologia
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_tipologia'])) {
    $nome_tipologia = $_POST['nome_tipologia'];
    $descrizione_tipologia = $_POST['descrizione_tipologia'];

    $query = "INSERT INTO tipologie (nome, descrizione) VALUES (?, ?)";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("ss", $nome_tipologia, $descrizione_tipologia);
        $stmt->execute();
        $stmt->close();
        header("Location: gestione_tipologie.php");
    }
}

// Gestione modifica di una tipologia esistente
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_tipologia'])) {
    $id_tipologia = $_POST['id_tipologia'];
    $nome_tipologia = $_POST['nome_tipologia'];
    $descrizione_tipologia = $_POST['descrizione_tipologia'];

    $query = "UPDATE tipologie SET nome = ?, descrizione = ? WHERE id = ?";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("ssi", $nome_tipologia, $descrizione_tipologia, $id_tipologia);
        $stmt->execute();
        $stmt->close();
        header("Location: gestione_tipologie.php");
    }
}

// Gestione eliminazione di una tipologia
if (isset($_POST['delete_tipologia'])) {
    $tipologia_id = $_POST['tipologia_id'];

    $query = "DELETE FROM tipologie WHERE id = ?";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("i", $tipologia_id);
        $stmt->execute();
        $stmt->close();
        header("Location: gestione_tipologie.php");
    }
}

// Ottieni tutte le tipologie dal database
$query = "SELECT * FROM tipologie";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    $tipologie_list = [];
    while ($row = $result->fetch_assoc()) {
        $tipologie_list[] = $row;
    }
} else {
    $tipologie_list = [];
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Tipologie</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 20px;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        form {
            margin-top: 20px;
        }
        form input, form select, form textarea {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        form input[type="submit"] {
            width: auto;
            cursor: pointer;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
        }
        .btn-delete, .btn-edit {
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
            background-color: #007bff;
            margin: 2px;
        }
        .btn-delete {
            background-color: #dc3545;
        }
        .btn-edit {
            background-color: #ffc107;
        }
        .btn-fixed-width {
            width: 100px;
        }
        .form-container {
            display: none;
            margin-top: 20px;
        }
        .btn-create {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            border: none;
            display: inline-block;
            margin-top: 20px;
        }
    </style>
    <script>
        function toggleForm(formId) {
            var form = document.getElementById(formId);
            if (form.style.display === "none") {
                form.style.display = "block";
            } else {
                form.style.display = "none";
            }
        }

        function fillForm(tipologia) {
            document.getElementById("id_tipologia").value = tipologia.id;
            document.getElementById("nome_tipologia").value = tipologia.nome;
            document.getElementById("descrizione_tipologia").value = tipologia.descrizione;
        }

        function confirmDelete(tipologiaId, tipologiaNome) {
            if (confirm("Sei sicuro di voler eliminare la tipologia '" + tipologiaNome + "'?")) {
                document.getElementById("form_elimina_" + tipologiaId).submit();
            }
        }
    </script>
</head>
<body>
    <?php include 'menu.php'; ?>
    <div class="container">
        <h2>Gestione Tipologie</h2>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome Tipologia</th>
                    <th>Descrizione</th>
                    <?php if ($_SESSION['livello_accesso'] === 'admin'): ?>
                        <th>Azioni</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tipologie_list as $tipologia): ?>
                    <tr>
                        <td><?php echo $tipologia['id']; ?></td>
                        <td><?php echo $tipologia['nome']; ?></td>
                        <td><?php echo $tipologia['descrizione']; ?></td>
                        <?php if ($_SESSION['livello_accesso'] === 'admin'): ?>
                            <td>
                                <form id="form_elimina_<?php echo $tipologia['id']; ?>" method="post" action="gestione_tipologie.php" style="display:inline-block;">
                                    <input type="hidden" name="tipologia_id" value="<?php echo $tipologia['id']; ?>">
                                    <button type="button" class="btn-delete btn-fixed-width" onclick="confirmDelete(<?php echo $tipologia['id']; ?>, '<?php echo htmlspecialchars($tipologia['nome']); ?>')">Elimina</button>
                                </form>
                                <button type="button" class="btn-edit btn-fixed-width" onclick="toggleForm('form_modifica'); fillForm(<?php echo htmlspecialchars(json_encode($tipologia)); ?>);">Modifica</button>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($_SESSION['livello_accesso'] === 'admin'): ?>
            <button type="button" onclick="toggleForm('form_inserisci');" class="btn-create">Crea Nuova Tipologia</button>
            <div id="form_inserisci" class="form-container" style="display:none;">
                <h3>Inserisci Nuova Tipologia</h3>
                <form method="post" action="gestione_tipologie.php">
                    <input type="text" name="nome_tipologia" placeholder="Nome Tipologia" required><br>
                    <textarea name="descrizione_tipologia" placeholder="Descrizione" required></textarea><br>
                    <input type="submit" name="submit_tipologia" value="Inserisci">
                </form>
            </div>

            <div id="form_modifica" class="form-container" style="display:none;">
                <h3>Modifica Tipologia</h3>
                <form method="post" action="gestione_tipologie.php">
                    <input type="hidden" id="id_tipologia" name="id_tipologia">
                    <input type="text" id="nome_tipologia" name="nome_tipologia" placeholder="Nome Tipologia" required><br>
                    <textarea id="descrizione_tipologia" name="descrizione_tipologia" placeholder="Descrizione" required></textarea><br>
                    <input type="submit" name="update_tipologia" value="Salva Modifiche">
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
session_start();
include 'config.php';

// Verifica se l'utente è loggato
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.html');
    exit;
}

// Gestione inserimento di una nuova checklist
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_checklist'])) {
    $nome_checklist = $_POST['nome_checklist'];
    $descrizione_checklist = $_POST['descrizione_checklist'];
    $scadenza_checklist = $_POST['scadenza_checklist'];
    $tipologie_checklist = $_POST['tipologie_checklist'];
    $punti_checklist = $_POST['punti_checklist'];
    $tipi_risultato = $_POST['tipi_risultato'];

    $query = "INSERT INTO checklist (nome, descrizione, scadenza) VALUES (?, ?, ?)";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("ssi", $nome_checklist, $descrizione_checklist, $scadenza_checklist);
        $stmt->execute();
        $checklist_id = $stmt->insert_id;
        $stmt->close();

        // Inserimento delle tipologie associate alla checklist
        if (!empty($tipologie_checklist)) {
            $query_tipologie = "INSERT INTO checklist_tipologie (checklist_id, tipologia_id) VALUES (?, ?)";
            if ($stmt = $mysqli->prepare($query_tipologie)) {
                foreach ($tipologie_checklist as $tipologia_id) {
                    $stmt->bind_param("ii", $checklist_id, $tipologia_id);
                    $stmt->execute();
                }
                $stmt->close();
            }
        }

        // Inserimento dei punti della checklist
        if (!empty($punti_checklist)) {
            $query_punti = "INSERT INTO punti_checklist (checklist_id, descrizione, tipo_risultato) VALUES (?, ?, ?)";
            if ($stmt = $mysqli->prepare($query_punti)) {
                foreach ($punti_checklist as $index => $descrizione) {
                    $tipo_risultato = $tipi_risultato[$index];
                    $stmt->bind_param("iss", $checklist_id, $descrizione, $tipo_risultato);
                    $stmt->execute();
                }
                $stmt->close();
            }
        }

        header("Location: gestione_checklist.php");
    }
}

// Gestione eliminazione di una checklist
if (isset($_POST['delete_checklist'])) {
    $checklist_id = $_POST['checklist_id'];

    // Elimina la checklist e i punti associati
    $query_delete_punti = "DELETE FROM punti_checklist WHERE checklist_id = ?";
    if ($stmt = $mysqli->prepare($query_delete_punti)) {
        $stmt->bind_param("i", $checklist_id);
        $stmt->execute();
        $stmt->close();
    }

    $query_delete_tipologie = "DELETE FROM checklist_tipologie WHERE checklist_id = ?";
    if ($stmt = $mysqli->prepare($query_delete_tipologie)) {
        $stmt->bind_param("i", $checklist_id);
        $stmt->execute();
        $stmt->close();
    }

    $query_delete_checklist = "DELETE FROM checklist WHERE id = ?";
    if ($stmt = $mysqli->prepare($query_delete_checklist)) {
        $stmt->bind_param("i", $checklist_id);
        $stmt->execute();
        $stmt->close();
        header("Location: gestione_checklist.php");
    }
}

// Gestione modifica di una checklist
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_checklist'])) {
    $checklist_id = $_POST['checklist_id'];
    $nome_checklist = $_POST['nome_checklist'];
    $descrizione_checklist = $_POST['descrizione_checklist'];
    $scadenza_checklist = $_POST['scadenza_checklist'];
    $tipologie_checklist = $_POST['tipologie_checklist'];
    $punti_checklist = $_POST['punti_checklist'];
    $tipi_risultato = $_POST['tipi_risultato'];

    $query = "UPDATE checklist SET nome=?, descrizione=?, scadenza=? WHERE id=?";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("ssii", $nome_checklist, $descrizione_checklist, $scadenza_checklist, $checklist_id);
        $stmt->execute();
        $stmt->close();

        // Aggiorna le tipologie associate alla checklist
        $query_delete_tipologie = "DELETE FROM checklist_tipologie WHERE checklist_id = ?";
        if ($stmt = $mysqli->prepare($query_delete_tipologie)) {
            $stmt->bind_param("i", $checklist_id);
            $stmt->execute();
            $stmt->close();
        }

        if (!empty($tipologie_checklist)) {
            $query_tipologie = "INSERT INTO checklist_tipologie (checklist_id, tipologia_id) VALUES (?, ?)";
            if ($stmt = $mysqli->prepare($query_tipologie)) {
                foreach ($tipologie_checklist as $tipologia_id) {
                    $stmt->bind_param("ii", $checklist_id, $tipologia_id);
                    $stmt->execute();
                }
                $stmt->close();
            }
        }

        // Aggiorna i punti della checklist
        $query_delete_punti = "DELETE FROM punti_checklist WHERE checklist_id = ?";
        if ($stmt = $mysqli->prepare($query_delete_punti)) {
            $stmt->bind_param("i", $checklist_id);
            $stmt->execute();
            $stmt->close();
        }

        if (!empty($punti_checklist)) {
            $query_punti = "INSERT INTO punti_checklist (checklist_id, descrizione, tipo_risultato) VALUES (?, ?, ?)";
            if ($stmt = $mysqli->prepare($query_punti)) {
                foreach ($punti_checklist as $index => $descrizione) {
                    $tipo_risultato = $tipi_risultato[$index];
                    $stmt->bind_param("iss", $checklist_id, $descrizione, $tipo_risultato);
                    $stmt->execute();
                }
                $stmt->close();
            }
        }

        header("Location: gestione_checklist.php");
    }
}

// Ottieni tutte le checklist dal database
$query = "
    SELECT checklist.*, GROUP_CONCAT(tipologie.nome SEPARATOR ', ') AS tipologie_nomi
    FROM checklist
    LEFT JOIN checklist_tipologie ON checklist.id = checklist_tipologie.checklist_id
    LEFT JOIN tipologie ON checklist_tipologie.tipologia_id = tipologie.id
    GROUP BY checklist.id";
$result = $mysqli->query($query);

$checklist_list = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $checklist_list[] = $row;
    }
}

// Ottieni tutte le tipologie dal database
$query = "SELECT * FROM tipologie";
$result_tipologie = $mysqli->query($query);

$tipologie_list = [];
if ($result_tipologie->num_rows > 0) {
    while ($row = $result_tipologie->fetch_assoc()) {
        $tipologie_list[] = $row;
    }
}

// Ottieni le tipologie associate a ciascuna checklist
$query = "SELECT checklist_id, tipologia_id FROM checklist_tipologie";
$result_checklist_tipologie = $mysqli->query($query);

$checklist_tipologie = [];
if ($result_checklist_tipologie->num_rows > 0) {
    while ($row = $result_checklist_tipologie->fetch_assoc()) {
        if (!isset($checklist_tipologie[$row['checklist_id']])) {
            $checklist_tipologie[$row['checklist_id']] = [];
        }
        $checklist_tipologie[$row['checklist_id']][] = $row['tipologia_id'];
    }
}

// Ottieni tutti i punti delle checklist dal database
$query = "SELECT * FROM punti_checklist";
$result_punti = $mysqli->query($query);

$punti_checklist = [];
if ($result_punti->num_rows > 0) {
    while ($row = $result_punti->fetch_assoc()) {
        if (!isset($punti_checklist[$row['checklist_id']])) {
            $punti_checklist[$row['checklist_id']] = [];
        }
        $punti_checklist[$row['checklist_id']][] = $row;
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Checklist</title>
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
        tr.main-checklist {
            background-color: white;
        }
        tr.child-checklist {
            background-color: #f9f9f9;
        }
        form {
            margin-top: 20px;
            display: none; /* Nascondi il form inizialmente */
        }
        form input, form textarea, form select {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        form input[type="submit"], .btn-add, .btn-remove {
            width: auto;
            cursor: pointer;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 3px;
            margin: 2px;
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
        .btn-fixed-width {
            width: 100px; /* Esempio di larghezza fissa */
        }
        .point-container {
            display: flex;
            align-items: center;
        }
        .point-container textarea {
            flex: 1;
            width: 80%;
            margin-right: 10px;
        }
        .point-container select {
            flex: 0 0 15%;
            margin-right: 10px;
        }
        .point-container button {
            flex: 0 0 5%;
        }
    </style>
    <script>
        function toggleForm(formId) {
            var form = document.getElementById(formId);
            form.style.display = form.style.display === "none" ? "block" : "none";
        }

        function fillForm(data) {
            document.getElementById('update_checklist_id').value = data.id;
            document.getElementById('update_nome_checklist').value = data.nome;
            document.getElementById('update_descrizione_checklist').value = data.descrizione;
            document.getElementById('update_scadenza_checklist').value = data.scadenza;

            var tipologie_select = document.getElementById('update_tipologie_checklist');
            for (var i = 0; i < tipologie_select.options.length; i++) {
                tipologie_select.options[i].selected = data.tipologie.includes(parseInt(tipologie_select.options[i].value));
            }

            var pointsContainer = document.getElementById('update_points_container');
            pointsContainer.innerHTML = '';
            data.punti.forEach(function(punto, index) {
                var pointDiv = document.createElement('div');
                pointDiv.className = 'point-container';
                pointDiv.innerHTML = `
                    <textarea name="punti_checklist[]" placeholder="Descrizione Punto Checklist" required>${punto.descrizione}</textarea>
                    <select name="tipi_risultato[]" required>
                        <option value="binario" ${punto.tipo_risultato === 'binario' ? 'selected' : ''}>Binario (Pass/Fail)</option>
                        <option value="valore_numerico" ${punto.tipo_risultato === 'valore_numerico' ? 'selected' : ''}>Valore Numerico</option>
                    </select>
                    ${index > 0 ? '<button type="button" class="btn-remove" onclick="removePoint(this)">-</button>' : ''}
                `;
                pointsContainer.appendChild(pointDiv);
            });
        }




        function addPoint(containerId) {
            var container = document.getElementById(containerId);
            var pointIndex = container.children.length;
            var pointDiv = document.createElement('div');
            pointDiv.className = 'point-container';
            pointDiv.innerHTML = `
                <textarea name="punti_checklist[]" placeholder="Descrizione Punto Checklist" required></textarea>
                <select name="tipi_risultato[]" required>
                    <option value="binario">Binario (Pass/Fail)</option>
                    <option value="valore_numerico">Valore Numerico</option>
                </select>
                ${pointIndex > 0 ? '<button type="button" class="btn-remove" onclick="removePoint(this)">-</button>' : ''}
            `;
            container.appendChild(pointDiv);
        }

        function removePoint(button) {
            button.parentElement.remove();
        }

        window.onload = function() {
            addPoint('create_points_container');  // Add the first point on page load
        }
    </script>
</head>
<body>
    <?php include 'menu.php'; ?>
    <div class="container">
        <h2>Gestione Checklist</h2>

        <table>
            <thead>
                <tr>
                    <th>Nome Checklist</th>
                    <th>Descrizione</th>
                    <th>Scadenza (mesi)</th>
                    <th>Tipologie</th>
                    <?php if ($_SESSION['livello_accesso'] === 'admin'): ?>
                        <th>Azioni</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($checklist_list as $checklist): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($checklist['nome']); ?></td>
                        <td><?php echo htmlspecialchars($checklist['descrizione']); ?></td>
                        <td><?php echo htmlspecialchars($checklist['scadenza']); ?></td>
                        <td><?php echo htmlspecialchars($checklist['tipologie_nomi']); ?></td>
                        <?php if ($_SESSION['livello_accesso'] === 'admin'): ?>
                            <td>
                                <form method="post" action="gestione_checklist.php" style="display:inline-block;">
                                    <input type="hidden" name="checklist_id" value="<?php echo $checklist['id']; ?>">
                                    <button type="button" class="btn-edit btn-fixed-width" onclick="toggleForm('form_modifica'); fillForm(<?php echo htmlspecialchars(json_encode([
                                        'id' => $checklist['id'],
                                        'nome' => $checklist['nome'],
                                        'descrizione' => $checklist['descrizione'],
                                        'scadenza' => $checklist['scadenza'],
                                        'tipologie' => array_map('intval', $checklist_tipologie[$checklist['id']] ?? []),
                                        'punti' => $punti_checklist[$checklist['id']] ?? []
                                    ])); ?>);">Modifica</button>
                                </form>
                                <form method="post" action="gestione_checklist.php" style="display:inline-block;">
                                    <input type="hidden" name="checklist_id" value="<?php echo $checklist['id']; ?>">
                                    <button type="submit" class="btn-delete btn-fixed-width" name="delete_checklist">Elimina</button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>

        </table>

        <?php if ($_SESSION['livello_accesso'] === 'admin'): ?>
            <button class="btn-create" onclick="toggleForm('form_inserimento')">Crea Nuova Checklist</button>

            <form id="form_inserimento" method="post" action="gestione_checklist.php" style="display:none">
                <input type="text" name="nome_checklist" placeholder="Nome Checklist" required><br>
                <textarea name="descrizione_checklist" placeholder="Descrizione Checklist"></textarea><br>
                <input type="number" name="scadenza_checklist" placeholder="Scadenza (mesi)" required><br>
                <label for="tipologie_checklist">Tipologie</label>
                <select name="tipologie_checklist[]" id="tipologie_checklist" multiple>
                    <?php foreach ($tipologie_list as $tipologia): ?>
                        <option value="<?php echo $tipologia['id']; ?>"><?php echo htmlspecialchars($tipologia['nome']); ?></option>
                    <?php endforeach; ?>
                </select><br>
                <h4>Punti della Checklist</h4>
                <div id="create_points_container" class="points-container">
                    <!-- I punti della checklist verranno aggiunti qui -->
                </div>
                <button type="button" class="btn-add" onclick="addPoint('create_points_container')">+</button><br>
                <input type="submit" name="submit_checklist" value="Inserisci">
            </form>

            <form id="form_modifica" method="post" action="gestione_checklist.php" style="display:none">
                <input type="hidden" id="update_checklist_id" name="checklist_id">
                <input type="text" id="update_nome_checklist" name="nome_checklist" required><br>
                <textarea id="update_descrizione_checklist" name="descrizione_checklist"></textarea><br>
                <input type="number" id="update_scadenza_checklist" name="scadenza_checklist" required><br>
                <label for="update_tipologie_checklist">Tipologie</label>
                <select name="tipologie_checklist[]" id="update_tipologie_checklist" multiple>
                    <?php foreach ($tipologie_list as $tipologia): ?>
                        <option value="<?php echo $tipologia['id']; ?>"><?php echo htmlspecialchars($tipologia['nome']); ?></option>
                    <?php endforeach; ?>
                </select><br>
                <h4>Punti della Checklist</h4>
                <div id="update_points_container" class="points-container">
                    <!-- I punti della checklist verranno aggiunti qui -->
                </div>
                <button type="button" class="btn-add" onclick="addPoint('update_points_container')">+</button><br>
                <input type="submit" name="update_checklist" value="Aggiorna">
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

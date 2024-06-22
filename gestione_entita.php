<?php
session_start();
include 'config.php';

// Verifica se l'utente è loggato
if (!isset($_SESSION['loggedin'])){
    header('Location: login.html');
    exit;
}

// Ottieni tutte le entità con le loro tipologie
$query = "
    SELECT entita.*, tipologie.nome AS tipologia_nome 
    FROM entita 
    JOIN tipologie ON entita.tipologia_id = tipologie.id";
$result = $mysqli->query($query);

$entita_list = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $entita_list[] = $row;
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

// Ottieni le relazioni delle entità
$query = "
    SELECT entita_relazioni.*, e1.nome AS entita_nome, e2.nome AS entita_padre_nome
    FROM entita_relazioni
    JOIN entita e1 ON entita_relazioni.entita_id = e1.id
    JOIN entita e2 ON entita_relazioni.entita_padre_id = e2.id";
$result_relazioni = $mysqli->query($query);

$relazioni = [];
$entita_figlie_ids = [];
if ($result_relazioni->num_rows > 0) {
    while ($row = $result_relazioni->fetch_assoc()) {
        if (!isset($relazioni[$row['entita_padre_id']])) {
            $relazioni[$row['entita_padre_id']] = [];
        }
        $relazioni[$row['entita_padre_id']][] = [
            'entita_id' => $row['entita_id'],
            'entita_nome' => $row['entita_nome']
        ];
        $entita_figlie_ids[] = $row['entita_id'];
    }
}

// Gestione inserimento di una nuova entità
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_entita'])) {
    $nome = $_POST['nome'];
    $codice_identificativo = $_POST['codice_identificativo'];
    $descrizione = $_POST['descrizione'];
    $data_messa_in_servizio = $_POST['data_messa_in_servizio'];
    $locazione = $_POST['locazione'];
    $utente = $_POST['utente'];
    $potenza = $_POST['potenza'];
    $prodotti_testati = $_POST['prodotti_testati'];
    $documentazione = $_POST['documentazione'];
    $tipologia_id = $_POST['tipologia_id'];
    $entita_padri = $_POST['entita_padri'];

    $query = "INSERT INTO entita (nome, codice_identificativo, descrizione, data_messa_in_servizio, locazione, utente, potenza, prodotti_testati, documentazione, tipologia_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("sssssssssi", $nome, $codice_identificativo, $descrizione, $data_messa_in_servizio, $locazione, $utente, $potenza, $prodotti_testati, $documentazione, $tipologia_id);
        $stmt->execute();
        $entita_id = $stmt->insert_id;
        $stmt->close();

        // Inserisci le relazioni padre-figlio
        if (!empty($entita_padri)) {
            $query_relazioni = "INSERT INTO entita_relazioni (entita_id, entita_padre_id) VALUES (?, ?)";
            if ($stmt = $mysqli->prepare($query_relazioni)) {
                foreach ($entita_padri as $entita_padre_id) {
                    $stmt->bind_param("ii", $entita_id, $entita_padre_id);
                    $stmt->execute();
                }
                $stmt->close();
            }
        }

        header("Location: gestione_entita.php");
    }
}

// Gestione eliminazione di un'entità
if (isset($_POST['delete_entita'])) {
    $entita_id = $_POST['entita_id'];

    // Elimina anche le relazioni associate all'entità
    $query_delete_relazioni = "DELETE FROM entita_relazioni WHERE entita_id = ? OR entita_padre_id = ?";
    if ($stmt = $mysqli->prepare($query_delete_relazioni)) {
        $stmt->bind_param("ii", $entita_id, $entita_id);
        $stmt->execute();
        $stmt->close();
    }

    // Elimina l'entità stessa
    $query_delete_entita = "DELETE FROM entita WHERE id = ?";
    if ($stmt = $mysqli->prepare($query_delete_entita)) {
        $stmt->bind_param("i", $entita_id);
        $stmt->execute();
        $stmt->close();
        header("Location: gestione_entita.php");
    }
}

// Gestione modifica di un'entità
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_entita'])) {
    $id = $_POST['entita_id'];
    $nome = $_POST['nome'];
    $codice_identificativo = $_POST['codice_identificativo'];
    $descrizione = $_POST['descrizione'];
    $data_messa_in_servizio = $_POST['data_messa_in_servizio'];
    $locazione = $_POST['locazione'];
    $utente = $_POST['utente'];
    $potenza = $_POST['potenza'];
    $prodotti_testati = $_POST['prodotti_testati'];
    $documentazione = $_POST['documentazione'];
    $tipologia_id = $_POST['tipologia_id'];
    $entita_padri = $_POST['entita_padri'];

    $query = "UPDATE entita SET nome=?, codice_identificativo=?, descrizione=?, data_messa_in_servizio=?, locazione=?, utente=?, potenza=?, prodotti_testati=?, documentazione=?, tipologia_id=? WHERE id=?";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("ssssssssssi", $nome, $codice_identificativo, $descrizione, $data_messa_in_servizio, $locazione, $utente, $potenza, $prodotti_testati, $documentazione, $tipologia_id, $id);
        $stmt->execute();
        $stmt->close();

        // Aggiorna le relazioni padre-figlio
        $query_delete_relazioni = "DELETE FROM entita_relazioni WHERE entita_id = ?";
        if ($stmt = $mysqli->prepare($query_delete_relazioni)) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }

        if (!empty($entita_padri)) {
            $query_relazioni = "INSERT INTO entita_relazioni (entita_id, entita_padre_id) VALUES (?, ?)";
            if ($stmt = $mysqli->prepare($query_relazioni)) {
                foreach ($entita_padri as $entita_padre_id) {
                    $stmt->bind_param("ii", $id, $entita_padre_id);
                    $stmt->execute();
                }
                $stmt->close();
            }
        }

        header("Location: gestione_entita.php");
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Entità</title>
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
        tr.main-entity {
            background-color: white;
        }
        tr.child-entity {
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
    </style>
    <script>
        function toggleForm(formId) {
            var form = document.getElementById(formId);
            form.style.display = form.style.display === "none" ? "block" : "none";
        }

        function fillForm(data) {
            document.getElementById('update_entita_id').value = data.id;
            document.getElementById('update_nome').value = data.nome;
            document.getElementById('update_codice_identificativo').value = data.codice_identificativo;
            document.getElementById('update_tipologia_id').value = data.tipologia_id;
            document.getElementById('update_data_messa_in_servizio').value = data.data_messa_in_servizio;
            document.getElementById('update_locazione').value = data.locazione;
            document.getElementById('update_descrizione').value = data.descrizione;
            document.getElementById('update_utente').value = data.utente;
            document.getElementById('update_potenza').value = data.potenza;
            document.getElementById('update_prodotti_testati').value = data.prodotti_testati;
            document.getElementById('update_documentazione').value = data.documentazione;

            var entita_padri_select = document.getElementById('update_entita_padri');
            for (var i = 0; i < entita_padri_select.options.length; i++) {
                entita_padri_select.options[i].selected = data.entita_padri.includes(entita_padri_select.options[i].value);
            }
        }

        function confirmDelete(entitaId, entitaNome) {
            if (confirm("Sei sicuro di voler eliminare l'entità '" + entitaNome + "'?")) {
                document.getElementById("form_elimina_" + entitaId).submit();
            }
        }

        function toggleExpandableContent(rowId, button) {
            var content = document.getElementsByClassName('child-entity-' + rowId);
            for (var i = 0; i < content.length; i++) {
                if (content[i].style.display === 'none' || content[i].style.display === '') {
                    content[i].style.display = 'table-row';
                } else {
                    content[i].style.display = 'none';
                }
            }
            
            if (button.textContent === 'Espandi') {
                button.textContent = 'Collassa';
            } else {
                button.textContent = 'Espandi';
            }
        }
    </script>
</head>
<body>
    <?php include 'menu.php'; ?>
    <div class="container">
        <h2>Gestione Entità</h2>

        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Codice Identificativo</th>
                    <th>Tipologia</th>
                    <th>Data Messa in Servizio</th>
                    <th>Locazione</th>
                    <th>Descrizione</th>
                    <th>Utente</th>
                    <th>Potenza (kW)</th>
                    <th>Prodotti Testati</th>
                    <th>Documentazione</th>
                    <th>Entità Collegate</th>
                    <?php if ($_SESSION['livello_accesso'] === 'admin'): ?>
                        <th>Azioni</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entita_list as $entita): ?>
                    <?php if (!in_array($entita['id'], $entita_figlie_ids)): ?>
                        <tr class="main-entity">
                            <td><?php echo htmlspecialchars($entita['nome']); ?></td>
                            <td><?php echo htmlspecialchars($entita['codice_identificativo']); ?></td>
                            <td><?php echo htmlspecialchars($entita['tipologia_nome']); ?></td>
                            <td><?php echo htmlspecialchars($entita['data_messa_in_servizio']); ?></td>
                            <td><?php echo htmlspecialchars($entita['locazione']); ?></td>
                            <td><?php echo htmlspecialchars($entita['descrizione']); ?></td>
                            <td><?php echo htmlspecialchars($entita['utente']); ?></td>
                            <td><?php echo htmlspecialchars($entita['potenza']); ?></td>
                            <td><?php echo htmlspecialchars($entita['prodotti_testati']); ?></td>
                            <td><?php echo htmlspecialchars($entita['documentazione']); ?></td>
                            <td>
                                <?php if (isset($relazioni[$entita['id']])): ?>
                                    <button type="button" class="btn-edit btn-fixed-width" onclick="toggleExpandableContent(<?php echo $entita['id']; ?>, this)">Espandi</button>
                                <?php else: ?>
                                    Nessuna
                                <?php endif; ?>
                            </td>
                            <?php if ($_SESSION['livello_accesso'] !== 'base'): ?>
                                <td>
                                    <form id="form_elimina_<?php echo $entita['id']; ?>" method="post" action="gestione_entita.php" style="display:inline-block;">
                                        <input type="hidden" name="entita_id" value="<?php echo $entita['id']; ?>">
                                        <button type="button" class="btn-delete btn-fixed-width" onclick="confirmDelete(<?php echo $entita['id']; ?>, '<?php echo htmlspecialchars($entita['nome']); ?>')">Elimina</button>
                                    </form>
                                    <button type="button" class="btn-edit btn-fixed-width" onclick="toggleForm('form_modifica'); fillForm(<?php echo htmlspecialchars(json_encode(array_merge($entita, ['entita_padri' => isset($relazioni[$entita['id']]) ? array_column($relazioni[$entita['id']], 'entita_padre_id') : []]))); ?>);">Modifica</button>
                                </td>
                            <?php endif; ?>
                        </tr>

                        <?php if (isset($relazioni[$entita['id']])): ?>
                            <?php foreach ($relazioni[$entita['id']] as $relazione): ?>
                                <?php foreach ($entita_list as $child): ?>
                                    <?php if ($child['id'] == $relazione['entita_id']): ?>
                                        <tr class="child-entity child-entity-<?php echo $entita['id']; ?>" style="display:none">
                                            <td><?php echo htmlspecialchars($child['nome']); ?></td>
                                            <td><?php echo htmlspecialchars($child['codice_identificativo']); ?></td>
                                            <td><?php echo htmlspecialchars($child['tipologia_nome']); ?></td>
                                            <td><?php echo htmlspecialchars($child['data_messa_in_servizio']); ?></td>
                                            <td><?php echo htmlspecialchars($child['locazione']); ?></td>
                                            <td><?php echo htmlspecialchars($child['descrizione']); ?></td>
                                            <td><?php echo htmlspecialchars($child['utente']); ?></td>
                                            <td><?php echo htmlspecialchars($child['potenza']); ?></td>
                                            <td><?php echo htmlspecialchars($child['prodotti_testati']); ?></td>
                                            <td><?php echo htmlspecialchars($child['documentazione']); ?></td>
                                            <td></td>
                                            <?php if ($_SESSION['livello_accesso'] === 'admin'): ?>
                                                <td>
                                                    <form id="form_elimina_<?php echo $child['id']; ?>" method="post" action="gestione_entita.php" style="display:inline-block;">
                                                        <input type="hidden" name="entita_id" value="<?php echo $child['id']; ?>">
                                                        <button type="button" class="btn-delete btn-fixed-width" onclick="confirmDelete(<?php echo $child['id']; ?>, '<?php echo htmlspecialchars($child['nome']); ?>')">Elimina</button>
                                                    </form>
                                                    <button type="button" class="btn-edit btn-fixed-width" onclick="toggleForm('form_modifica'); fillForm(<?php echo htmlspecialchars(json_encode(array_merge($child, ['entita_padri' => isset($relazioni[$child['id']]) ? array_column($relazioni[$child['id']], 'entita_padre_id') : []]))); ?>);">Modifica</button>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($_SESSION['livello_accesso'] === 'admin'): ?>
            <button class="btn-create" onclick="toggleForm('form_inserimento')">Crea Nuova Entità</button>

            <form id="form_inserimento" method="post" action="gestione_entita.php" style="display:none">
                <input type="text" name="nome" placeholder="Nome" required><br>
                <input type="text" name="codice_identificativo" placeholder="Codice Identificativo" required><br>
                <select name="tipologia_id" required>
                    <option value="">Seleziona Tipologia</option>
                    <?php foreach ($tipologie_list as $tipologia): ?>
                        <option value="<?php echo $tipologia['id']; ?>"><?php echo htmlspecialchars($tipologia['nome']); ?></option>
                    <?php endforeach; ?>
                </select><br>
                <input type="date" name="data_messa_in_servizio" required><br>
                <input type="text" name="locazione" placeholder="Locazione"><br>
                <textarea name="descrizione" placeholder="Descrizione"></textarea><br>
                <input type="text" name="utente" placeholder="Utente"><br>
                <input type="text" name="potenza" placeholder="Potenza (kW)"><br>
                <input type="text" name="prodotti_testati" placeholder="Prodotti Testati"><br>
                <textarea name="documentazione" placeholder="Documentazione"></textarea><br>
                <label for="entita_padri">Entità Padre</label>
                <select name="entita_padri[]" id="entita_padri" multiple>
                    <?php foreach ($entita_list as $entita): ?>
                        <option value="<?php echo $entita['id']; ?>"><?php echo htmlspecialchars($entita['nome']); ?></option>
                    <?php endforeach; ?>
                </select><br>
                <input type="submit" name="submit_entita" value="Inserisci">
            </form>

            <form id="form_modifica" method="post" action="gestione_entita.php" style="display:none">
                <input type="hidden" id="update_entita_id" name="entita_id">
                <input type="text" id="update_nome" name="nome" required><br>
                <input type="text" id="update_codice_identificativo" name="codice_identificativo" required><br>
                <select id="update_tipologia_id" name="tipologia_id" required>
                    <option value="">Seleziona Tipologia</option>
                    <?php foreach ($tipologie_list as $tipologia): ?>
                        <option value="<?php echo $tipologia['id']; ?>"><?php echo htmlspecialchars($tipologia['nome']); ?></option>
                    <?php endforeach; ?>
                </select><br>
                <input type="date" id="update_data_messa_in_servizio" name="data_messa_in_servizio" required><br>
                <input type="text" id="update_locazione" name="locazione"><br>
                <textarea id="update_descrizione" name="descrizione"></textarea><br>
                <input type="text" id="update_utente" name="utente"><br>
                <input type="text" id="update_potenza" name="potenza"><br>
                <input type="text" id="update_prodotti_testati" name="prodotti_testati"><br>
                <textarea id="update_documentazione" name="documentazione"></textarea><br>
                <label for="update_entita_padri">Entità Padre</label>
                <select name="entita_padri[]" id="update_entita_padri" multiple>
                    <?php foreach ($entita_list as $entita): ?>
                        <option value="<?php echo $entita['id']; ?>"><?php echo htmlspecialchars($entita['nome']); ?></option>
                    <?php endforeach; ?>
                </select><br>
                <input type="submit" name="update_entita" value="Aggiorna">
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

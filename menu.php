<!DOCTYPE html>
<html lang="it">
<head>
    <style>
        .navbar {
            overflow: hidden;
            background-color: #333;
            font-family: Arial, sans-serif;
        }
        .navbar a {
            float: left;
            display: block;
            color: white;
            text-align: center;
            padding: 14px 20px;
            text-decoration: none;
        }
        .navbar a:hover {
            background-color: #ddd;
            color: black;
        }
        .navbar a.active {
            background-color: #007bff;
            color: white;
        }
        .header {
            text-align: center;
            padding: 20px;
            background-color: #007bff;
            color: white;
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        Manutenzione Infrastrutture
    </div>
    <div class="navbar">
        <a href="gestione_entita.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'gestione_entita.php' ? 'active' : ''; ?>">Gestione Entità</a>
        <a href="gestione_tipologie.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'gestione_tipologie.php' ? 'active' : ''; ?>">Gestione Tipologie</a>
        <a href="gestione_checklist.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'gestione_checklist.php' ? 'active' : ''; ?>">Gestione Checklist</a>
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>

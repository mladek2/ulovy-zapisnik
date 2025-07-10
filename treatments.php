<?php
session_start();
require_once __DIR__ . '/config/database.php';

$treatments = $pdo->query("SELECT * FROM treatments ORDER BY nazev")->fetchAll();
?>
<!DOCTYPE html>
<html lang=\"cs\">
<head>
    <meta charset=\"UTF-8\">
    <title>Léèiva</title>
    <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css\" rel=\"stylesheet\">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class=\"container mt-4\">
    <h2>Pøehled léèiv</h2>

    <table class=\"table table-bordered\">
        <thead class=\"table-light\">
            <tr>
                <th>Název</th><th>Druh</th><th>Úè. látka</th><th>Dávkování</th><th>Omezení</th><th>Poznámka</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($treatments as $t): ?>
            <tr>
                <td><?= htmlspecialchars($t['nazev']) ?></td>
                <td><?= htmlspecialchars($t['typ_latky']) ?></td>
                <td><?= htmlspecialchars($t['ucinna_latka']) ?></td>
                <td><?= htmlspecialchars($t['davkovani']) ?></td>
                <td><?= htmlspecialchars($t['omezeni_med']) ?></td>
                <td><?= htmlspecialchars($t['poznamky']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>

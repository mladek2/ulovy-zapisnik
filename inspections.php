<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Zpracování filtrů
$selectedLocation = $_GET['location_id'] ?? '';
$selectedHive = $_GET['hive_id'] ?? '';

// Načtení stanovišť uživatele
$locationsStmt = $pdo->prepare("SELECT id, nazev FROM locations WHERE user_id = ? ORDER BY nazev");
$locationsStmt->execute([$userId]);
$locations = $locationsStmt->fetchAll();

// Načtení úlů podle vybraného stanoviště nebo všech
$hivesStmt = $selectedLocation
    ? $pdo->prepare("SELECT id, name FROM hives WHERE location_id = ?")
    : $pdo->prepare("SELECT h.id, h.name FROM hives h JOIN locations l ON h.location_id = l.id WHERE l.user_id = ?");

$hivesStmt->execute($selectedLocation ? [$selectedLocation] : [$userId]);
$hives = $hivesStmt->fetchAll();

// Načtení kontrol
$sql = "SELECT i.*, h.name AS hive_name, l.nazev AS location_name
        FROM inspections i
        JOIN hives h ON i.hive_id = h.id
        JOIN locations l ON h.location_id = l.id
        WHERE l.user_id = ?";
$params = [$userId];

if ($selectedLocation) {
    $sql .= " AND l.id = ?";
    $params[] = $selectedLocation;
}
if ($selectedHive) {
    $sql .= " AND h.id = ?";
    $params[] = $selectedHive;
}

$sql .= " ORDER BY i.inspection_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$inspections = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Kontroly včelstev</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2>Seznam kontrol 🐝</h2>

    <form method="get" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="location_id" class="form-label">Stanoviště</label>
            <select name="location_id" id="location_id" class="form-select" onchange="this.form.submit()">
                <option value="">Všechna stanoviště</option>
                <?php foreach ($locations as $loc): ?>
                    <option value="<?= $loc['id'] ?>" <?= $selectedLocation == $loc['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($loc['nazev']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-4">
            <label for="hive_id" class="form-label">Úl</label>
            <select name="hive_id" id="hive_id" class="form-select" onchange="this.form.submit()">
                <option value="">Všechny úl</option>
                <?php foreach ($hives as $h): ?>
                    <option value="<?= $h['id'] ?>" <?= $selectedHive == $h['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($h['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>Datum</th>
                <th>Stanoviště</th>
                <th>Úl</th>
                <th>Královna</th>
                <th>Vajíčka</th>
                <th>Spad varoa</th>
                <th>Zásoby (dm²)</th>
                <th>Poznámka</th>
                <th>Akce</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($inspections as $i): ?>
            <tr>
                <td><?= htmlspecialchars($i['inspection_date']) ?></td>
                <td><?= htmlspecialchars($i['location_name']) ?></td>
                <td><?= htmlspecialchars($i['hive_name']) ?></td>
                <td><?= $i['queen_seen'] ? 'Viděna' : '-' ?></td>
                <td><?= $i['eggs_seen'] ? 'Ano' : '-' ?></td>
                <td><?= (int)$i['spad_varoa'] ?></td>
                <td><?= number_format($i['zasoby'], 1, ',', ' ') ?></td>
                <td><?= htmlspecialchars($i['notes']) ?></td>
                <td><a href="edit-inspection.php?id=<?= $i['id'] ?>" class="btn btn-sm btn-outline-secondary">Upravit</a>
                <a href="delete.php?type=inspection&id=<?= $i['id'] ?>" class="btn btn-sm btn-danger">🗑️ Smazat</a></td>

            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>

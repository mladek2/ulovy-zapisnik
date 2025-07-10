<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$locationId = $_GET['id'] ?? null;

if (!$locationId) {
    die("Neplatné ID stanoviště.");
}

// Získat minimální zásobu
$pref = $pdo->prepare("SELECT minimalni_zasoby FROM user_preferences WHERE user_id = ?");
$pref->execute([$userId]);
$minZasoby = $pref->fetchColumn() ?? 5;

// Načíst informace o stanovišti
$stmt = $pdo->prepare("SELECT * FROM locations WHERE id = ?");
$stmt->execute([$locationId]);
$location = $stmt->fetch();

if (!$location) {
    die("Stanoviště nenalezeno.");
}

// Úly na stanovišti s poslední kontrolou
$query = $pdo->prepare("SELECT h.*, i.zasoby, i.inspection_date AS datum_kontroly FROM hives h
    LEFT JOIN inspections i ON i.id = (
        SELECT i2.id FROM inspections i2 WHERE i2.hive_id = h.id ORDER BY i.inspection_date DESC LIMIT 1
    )
    JOIN user_hive_permissions p ON p.hive_id = h.id
    WHERE h.location_id = ? AND p.user_id = ?
    ORDER BY h.id");
$query->execute([$locationId, $userId]);
$hives = $query->fetchAll();
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Detail stanoviště</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2>Detail stanoviště: <?= htmlspecialchars($location['nazev']) ?></h2>

    <p><strong>Katastr:</strong> <?= htmlspecialchars($location['cadastral_area']) ?></p>
    <p><strong>Číslo stanoviště:</strong> <?= htmlspecialchars($location['code']) ?></p>
    <p><strong>Medný výnos:</strong> <?= htmlspecialchars($location['medny_vynos']) ?> kg</p>
 <a href="delete.php?type=location&id=<?= $location['id'] ?>" class="btn btn-sm btn-danger">🗑️ Smazat</a>
    <h4 class="mt-4">Úly na stanovišti</h4>
    <table class="table table-sm table-hover">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Původ</th>
                <th>Zásoby</th>
                <th>Kontrola</th>
                <th>Akce</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($hives as $h): ?>
            <?php $low = ($h['zasoby'] !== null && $h['zasoby'] < $minZasoby); ?>
            <tr class="<?= $low ? 'table-danger' : '' ?>">
                <td><?= $h['id'] ?></td>
                <td><?= htmlspecialchars($h['puvod_vcelstva']) ?></td>
                <td><?= $h['zasoby'] !== null ? $h['zasoby'] . ' dm²' : '–' ?></td>
                <td><?= $h['datum_kontroly'] ?? '–' ?></td>
                <td>
                    <a href="hive-detail.php?id=<?= $h['id'] ?>" class="btn btn-sm btn-outline-primary">Detail úlu</a>
                   

                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>

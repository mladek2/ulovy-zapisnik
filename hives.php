<?php
session_start();
require_once __DIR__ . '/config/database.php';
//require_once __DIR__ . '/helpers/permissions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$orderBy = 'l.nazev, h.name';
if (isset($_GET['sort']) && in_array($_GET['sort'], ['location', 'name'])) {
    if ($_GET['sort'] === 'location') {
        $orderBy = 'l.nazev';
    } elseif ($_GET['sort'] === 'name') {
        $orderBy = 'h.name';
    }
}

$userId = $_SESSION['user_id'];

// Získání minimální zásoby ze user_preferences
$pref = $pdo->prepare("SELECT minimalni_zasoby FROM user_preferences WHERE user_id = ?");
$pref->execute([$userId]);
$minZasoby = $pref->fetchColumn() ?? 5;

// Získání úlů, na které má uživatel přístup + poslední inspekce
$query = $pdo->prepare("
    SELECT 
        h.*, 
        l.nazev AS stan_nazev,
        i.inspection_date AS datum_kontroly
    FROM hives h
    JOIN locations l ON h.location_id = l.id
    JOIN user_hive_permissions p ON h.id = p.hive_id
    LEFT JOIN inspections i ON i.id = (
        SELECT i2.id 
        FROM inspections i2 
        WHERE i2.hive_id = h.id 
        ORDER BY i2.inspection_date DESC 
        LIMIT 1
    )
    WHERE p.user_id = ?
    ORDER BY $orderBy
");
$query->execute([$userId]);
$hives = $query->fetchAll();

?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Moje úly</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2>Seznam úlů</h2>
    <a href="create-hive.php" class="btn btn-success mb-3">+ Nový úl</a>
    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th><a href="?sort=location">Stanoviště</a></th>
                <th><a href="?sort=name">Úl</a></th>
               
                <th>Rámková míra</th>
                <th>Zásoby</th>
                <th>Poslední kontrola</th>
                <th>Akce</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($hives as $h): ?>
                <?php $lowStock = ($h['zasoby'] !== null && $h['zasoby'] < $minZasoby); ?>
                <tr class="<?= $lowStock ? 'table-danger' : '' ?>">
                    <td><?= htmlspecialchars($h['stan_nazev']) ?></td>
                    <td><?= htmlspecialchars($h['name'] ?: 'Úl #' . $h['id']) ?></td>
                    <td><?= htmlspecialchars($h['ramkova_mira']) ?></td>
                    <td><?= $h['zasoby'] !== null ? $h['zasoby'] . ' dm²' : '–' ?></td>
                    <td><?= $h['datum_kontroly'] ?? '–' ?></td>
                    <td>
                        <a href="hive-detail.php?id=<?= $h['id'] ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                        <a href="edit-hive.php?id=<?= $h['id'] ?>" class="btn btn-sm btn-outline-secondary">Upravit</a>
                       <a href="create-inspection.php?hive_id=<?= $h['id'] ?>&location_id=<?= $h['location_id'] ?>" class="btn btn-sm btn-outline-success">Provést kontrolu</a>
                        <a href="delete.php?type=hive&id=<?= $h['id'] ?>" class="btn btn-sm btn-danger">🗑️ Smazat</a>


                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>

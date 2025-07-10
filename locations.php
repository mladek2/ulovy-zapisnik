<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

$pref = $pdo->prepare("SELECT minimalni_zasoby FROM user_preferences WHERE user_id = ?");
$pref->execute([$userId]);
$minZasoby = $pref->fetchColumn() ?? 5;

$stmt = $pdo->prepare("SELECT * FROM locations WHERE user_id = ?");
$stmt->execute([$userId]);
$locations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Stanoviště</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2>Seznam stanovišť</h2>

    <a href="create-location.php" class="btn btn-success mb-3">+ Nové stanoviště</a>

    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Název</th>
                <th>Katastr</th>
                <th>Číslo</th>
                <th>Medný výnos</th>
                <th>Akce</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($locations as $l): ?>
            <tr>
                <td><?= htmlspecialchars($l['nazev']) ?></td>
               <td><?= htmlspecialchars($l['cadastral_area']) ?></td>
                <td><?= htmlspecialchars($l['code']) ?></td>
                <td><?= htmlspecialchars($l['medny_vynos']) ?> kg</td>
                <td>
                    <a href="location-detail.php?id=<?= $l['id'] ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                    <a href="edit-location.php?id=<?= $l['id'] ?>" class="btn btn-sm btn-outline-secondary">Upravit</a>
                    <a href="delete.php?type=location&id=<?= $l['id'] ?>" class="btn btn-sm btn-danger">🗑️ Smazat</a>

                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>

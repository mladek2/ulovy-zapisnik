<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$returnUrl = 'index.php';

$allowedTypes = ['location', 'hive', 'inspection', 'mother'];
if (!in_array($type, $allowedTypes) || $id <= 0) {
    echo "Neplatný požadavek.";
    exit;
}

$deletionSuccess = false;
$error = '';
$redirectUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['confirm'] === 'yes') {
        if ($type === 'location') {
            $check = $pdo->prepare("SELECT COUNT(*) AS pocet FROM hives WHERE location_id = ?");
            $check->execute([$id]);
            $count = (int)$check->fetchColumn();
            if ($count > 0) {
                $error = "Nelze smazat stanoviště, které obsahuje úly.";
            } else {
                $query = $pdo->prepare("DELETE FROM locations WHERE id = ?");
                $query->execute([$id]);
                $deletionSuccess = true;
                $redirectUrl = 'locations.php';
            }
        } elseif ($type === 'hive') {
            $query = $pdo->prepare("DELETE FROM hives WHERE id = ?");
            $query->execute([$id]);
            $deletionSuccess = true;
            $redirectUrl = 'hives.php';
        } elseif ($type === 'inspection') {
            // Získat hive_id před smazáním
            $stmt = $pdo->prepare("SELECT hive_id FROM inspections WHERE id = ?");
            $stmt->execute([$id]);
            $hiveId = $stmt->fetchColumn();

            $query = $pdo->prepare("DELETE FROM inspections WHERE id = ?");
            $query->execute([$id]);
            $deletionSuccess = true;
            $redirectUrl = 'inspections.php';
        } elseif ($type === 'mother') {
            $query = $pdo->prepare("DELETE FROM mothers WHERE id = ?");
            $query->execute([$id]);
            $deletionSuccess = true;
            $redirectUrl = 'hives.php';
        }
    } else {
        header("Location: $returnUrl");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Potvrzení smazání</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
<?php if ($deletionSuccess): ?>
    <div class="alert alert-success">
        <h4>Záznam byl úspěšně smazán (<?= htmlspecialchars($type) ?> ID <?= $id ?>).</h4>
        <?php if ($type === 'inspection'): ?>
            <a href="inspections.php" class="btn btn-primary">Na přehled kontrol</a>
            <a href="hive-detail.php?id=<?= $hiveId ?>" class="btn btn-outline-secondary">Zpět na úl</a>
        <?php else: ?>
            <a href="<?= htmlspecialchars($redirectUrl) ?>" class="btn btn-primary">Zpět</a>
        <?php endif; ?>
    </div>
<?php elseif ($error): ?>
    <div class="alert alert-danger">
        <h4><?= htmlspecialchars($error) ?></h4>
        <a href="location-detail.php?id=<?= $id ?>" class="btn btn-primary">Zobrazit detail stanoviště</a>
    </div>
<?php else: ?>
    <h2>Opravdu chcete smazat tento záznam (<?= htmlspecialchars($type) ?> ID <?= $id ?>)?</h2>
    <form method="post">
        <input type="hidden" name="confirm" value="yes">
        <button type="submit" class="btn btn-danger">Ano, smazat</button>
        <a href="<?= htmlspecialchars($returnUrl) ?>" class="btn btn-secondary">Zpět</a>
    </form>
<?php endif; ?>
</div>
</body>
</html>

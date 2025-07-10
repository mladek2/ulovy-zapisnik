<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/permissions.php';

$userId = $_SESSION['user_id'] ?? null;
$hiveId = $_GET['id'] ?? null;

if (!$userId || !$hiveId || !hasPermission($userId, $hiveId, 'reader')) {
    die("Přístup odepřen.");
}

$stmt = $pdo->prepare("SELECT h.*, l.nazev AS stan_nazev FROM hives h JOIN locations l ON h.location_id = l.id WHERE h.id = ?");
$stmt->execute([$hiveId]);
$hive = $stmt->fetch();

$matka = $pdo->prepare("SELECT * FROM matky WHERE hive_id = ?");
$matka->execute([$hiveId]);
$queen = $matka->fetch();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Detail úlu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2>Úl č. <?= $hive['id'] ?> – <?= htmlspecialchars($hive['puvod_vcelstva']) ?></h2>

    <p><strong>Stanoviště:</strong> <?= htmlspecialchars($hive['stan_nazev']) ?></p>
    <p><strong>Rámková míra:</strong> <?= htmlspecialchars($hive['ramkova_mira']) ?></p>
    <p><strong>Nástavky:</strong> <?= $hive['pocet_nastavku'] ?></p>
    <p><strong>Krmítko:</strong> <?= $hive['krmitko'] ? 'Ano' : 'Ne' ?> (<?= htmlspecialchars($hive['typ_krmitka']) ?>)</p>
    <p><strong>Mateří mřížka:</strong> <?= $hive['materi_mrizka'] ? 'Ano' : 'Ne' ?></p>
    <p><strong>Medný výnos:</strong> <?= $hive['medny_vynos'] ?> kg</p>

    <hr>
    <h4>Matka</h4>
    <?php if ($queen): ?>
        <p><strong>Rok:</strong> <?= $queen['rok_narozeni'] ?>, <strong>Barva:</strong> <?= $queen['barva_znacky'] ?></p>
        <p><strong>Původ:</strong> <?= htmlspecialchars($queen['puvod']) ?>, <strong>Stav:</strong> <?= $queen['stav'] ?></p>
    <?php else: ?>
        <p>Údaje o matce nejsou zadány.</p>
    <?php endif; ?>

    <a href="edit-hive.php?id=<?= $hiveId ?>" class="btn btn-outline-secondary">Upravit úl</a>
    <a href="edit-mother.php?hive_id=<?= $hiveId ?>" class="btn btn-outline-primary">Upravit matku</a>
</div>
</body>
</html>

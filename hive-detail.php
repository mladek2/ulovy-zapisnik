<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$hiveId = $_GET['id'] ?? null;

if (!$hiveId) {
    die("Chyba: Neplatné ID úlu.");
}

$stmt = $pdo->prepare("SELECT h.*, l.nazev AS location_name FROM hives h JOIN locations l ON h.location_id = l.id WHERE h.id = ? AND h.user_id = ?");
$stmt->execute([$hiveId, $userId]);
$hive = $stmt->fetch();

if (!$hive) {
    die("Úl nebyl nalezen nebo k němu nemáte přístup.");
}

$inspections = $pdo->prepare("SELECT * FROM inspections WHERE hive_id = ? ORDER BY inspection_date DESC");
$inspections->execute([$hiveId]);
$kontroly = $inspections->fetchAll();
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
    <h2>Detail úlu #<?= htmlspecialchars($hive['id']) ?> (<?= htmlspecialchars($hive['puvod_vcelstva']) ?>)</h2>
    
    <p><strong>Stanoviště:</strong> <?= htmlspecialchars($hive['location_name']) ?></p>
    <p><strong>Datum založení:</strong> <?= htmlspecialchars($hive['created_at']) ?></p>
    <p><strong>Rámková míra:</strong> <?= htmlspecialchars($hive['ramkova_mira']) ?></p>
    <p><strong>Nástavky:</strong> <?= htmlspecialchars($hive['pocet_nastavku']) ?></p>
    <p><strong>Zásoby:</strong> <?= htmlspecialchars($hive['zasoby']) ?> dm² (~<?= round($hive['zasoby'] * 0.25, 1) ?> kg)</p>
    <p><strong>Mateří mřížka:</strong> <?= $hive['materi_mrizka'] ? 'Ano' : 'Ne' ?></p>
    <p><strong>Krmítko:</strong> <?= $hive['krmitko'] ? 'Ano' : 'Ne' ?></p>
<a href="create-inspection.php?hive_id=<?= $hive['id'] ?>&location_id=<?= $hive['location_id'] ?>" class="btn btn-sm btn-outline-success">Provést kontrolu</a>

    <a href="edit-hive.php?id=<?= $hive['id'] ?>" class="btn btn-outline-primary">Upravit úl</a>
<a href="delete.php?type=hive&id=<?= $hive['id'] ?>" class="btn btn-sm btn-danger">🗑️ Smazat</a>

    <hr>
    <h4>Kontroly</h4>
    <?php if ($kontroly): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Zásoby</th>
                    <th>Spad Varoa</th>
                    <th>Poznámka</th>
                    <th>Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kontroly as $k): ?>
                    <tr>
                        <td><?= htmlspecialchars($k['inspection_date']) ?></td>
                        <td><?= htmlspecialchars($k['zasoby']) ?></td>
                        <td><?= htmlspecialchars($k['spad_varoa']) ?></td>
                        <td><?= htmlspecialchars($k['notes']) ?></td>
                        <td><a href="edit-inspection.php?id=<?= $k['id'] ?>" class="btn btn-sm btn-outline-secondary">Upravit</a>
                        <a href="delete.php?type=inspection&id=<?= $k['id'] ?>" class="btn btn-sm btn-danger">🗑️ Smazat</a></td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Žádné kontroly nejsou zaznamenány.</p>
    <?php endif; ?>
</div>
</body>
</html>

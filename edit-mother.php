<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/permissions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$hiveId = $_GET['hive_id'] ?? null;

if (!$hiveId || !hasPermission($userId, $hiveId, 'editor')) {
    die("Nemáte oprávnìní upravovat údaje o matce tohoto úlu.");
}

// Získat matku, pokud existuje
$stmt = $pdo->prepare("SELECT * FROM matky WHERE hive_id = ?");
$stmt->execute([$hiveId]);
$matka = $stmt->fetch();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barva = trim($_POST['barva_znacky'] ?? '');
    $rok = intval($_POST['rok_narozeni'] ?? 0);
    $cislo = trim($_POST['cislo'] ?? '');
    $puvod = trim($_POST['puvod'] ?? '');
    $stav = $_POST['stav'] ?? '';
    $datum_nasazeni = $_POST['datum_nasazeni'] ?? null;

    if ($matka) {
        $stmt = $pdo->prepare("UPDATE matky SET barva_znacky = ?, rok_narozeni = ?, cislo = ?, puvod = ?, stav = ?, datum_nasazeni = ? WHERE hive_id = ?");
        $stmt->execute([$barva, $rok, $cislo, $puvod, $stav, $datum_nasazeni, $hiveId]);
        $success = "Údaje o matce byly upraveny.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO matky (hive_id, barva_znacky, rok_narozeni, cislo, puvod, stav, datum_nasazeni) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$hiveId, $barva, $rok, $cislo, $puvod, $stav, $datum_nasazeni]);
        $success = "Údaje o matce byly uloženy.";
    }

    $stmt = $pdo->prepare("SELECT * FROM matky WHERE hive_id = ?");
    $stmt->execute([$hiveId]);
    $matka = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Úprava matky</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container mt-5">
    <h2>Úprava údajù o matce v úlu è. <?= htmlspecialchars($hiveId) ?></h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Barva znaèky</label>
            <input type="text" name="barva_znacky" class="form-control" value="<?= htmlspecialchars($matka['barva_znacky'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Rok znaèení / narození</label>
            <input type="number" name="rok_narozeni" class="form-control" value="<?= htmlspecialchars($matka['rok_narozeni'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Èíslo / evidenèní oznaèení</label>
            <input type="text" name="cislo" class="form-control" value="<?= htmlspecialchars($matka['cislo'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Pùvod matky (napø. chovatel)</label>
            <input type="text" name="puvod" class="form-control" value="<?= htmlspecialchars($matka['puvod'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Stav matky</label>
            <select name="stav" class="form-select">
                <?php
                $stavy = ['nová', 'starší', 'nahrazená'];
                foreach ($stavy as $stav) {
                    $sel = ($matka['stav'] ?? '') === $stav ? 'selected' : '';
                    echo "<option value=\"$stav\" $sel>$stav</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Datum nasazení do úlu</label>
            <input type="date" name="datum_nasazeni" class="form-control" value="<?= htmlspecialchars($matka['datum_nasazeni'] ?? '') ?>">
        </div>

        <button type="submit" class="btn btn-primary">Uložit zmìny</button>
    </form>
</div>
</body>
</html>

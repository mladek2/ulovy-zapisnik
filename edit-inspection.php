<?php
session_start();
require_once __DIR__ . '/config/database.php';
//require_once __DIR__ . '/helpers/permissions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$inspectionId = $_GET['id'] ?? null;
$userId = $_SESSION['user_id'];

if (!$inspectionId) {
    die("ID kontroly není zadáno.");
}

// Načti kontrolu + úl
$stmt = $pdo->prepare("SELECT i.*, h.id AS hive_id FROM inspections i JOIN hives h ON i.hive_id = h.id WHERE i.id = ?");
$stmt->execute([$inspectionId]);
$inspection = $stmt->fetch();



$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datum = $_POST['datum'] ?? '';
    $zasoby = floatval($_POST['zasoby'] ?? 0);
    $plod_z = floatval($_POST['plod_zavickovany'] ?? 0);
    $plod_n = floatval($_POST['plod_nezavickovany'] ?? 0);
    $spad = intval($_POST['spad_varoa'] ?? 0);
    $metoda = $_POST['monitoring_metoda'] ?? '';
    $leceni_id = $_POST['lecivo_id'] ?? null;
    $zihadla = intval($_POST['zihadla'] ?? 0);
    $poznamka = trim($_POST['poznamka'] ?? '');

    $stmt = $pdo->prepare("UPDATE inspections SET 
        inspection_date = ?, 
        zasoby = ?, 
        plod_zavickovany = ?, 
        plod_nezavickovany = ?, 
        spad_varoa = ?, 
        monitoring_metoda = ?, 
        lecivo_id = ?, 
        zihadla = ?, 
        notes = ?
        WHERE id = ?");
    
   $stmt->execute([
    $datum, $zasoby, $plod_z, $plod_n, $spad, $metoda, $leceni_id ?: null, $zihadla, $poznamka, $inspectionId
]);

$success = "Kontrola byla úspěšně upravena.";

// Znovu načti aktuální inspekci
$stmt = $pdo->prepare("SELECT * FROM inspections WHERE id = ?");
$stmt->execute([$inspectionId]);
$inspection = $stmt->fetch();
}

// Načteme léčiva pro výběr
$leky = $pdo->query("SELECT id, nazev FROM treatments ORDER BY nazev")->fetchAll();
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Úprava kontroly</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container mt-5">
    <h2>Úprava kontroly úlu č. <?= htmlspecialchars($inspection['hive_id']) ?></h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label for="datum" class="form-label">Datum kontroly</label>
            <input type="date" name="datum" class="form-control" value="<?= htmlspecialchars($inspection['datum']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Zásoby (dm2 / kg)</label>
            <input type="number" step="0.1" name="zasoby" class="form-control" value="<?= htmlspecialchars($inspection['zasoby']) ?>">
        </div>

        <div class="mb-3 row">
            <div class="col">
                <label class="form-label">Plod zavíčkovaný (dm2)</label>
                <input type="number" step="0.1" name="plod_zavickovany" class="form-control" value="<?= htmlspecialchars($inspection['plod_zavickovany']) ?>">
            </div>
            <div class="col">
                <label class="form-label">Plod nezavíčkovaný (dm2)</label>
                <input type="number" step="0.1" name="plod_nezavickovany" class="form-control" value="<?= htmlspecialchars($inspection['plod_nezavickovany']) ?>">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Spad Varoa (ks)</label>
            <input type="number" name="spad_varoa" class="form-control" value="<?= htmlspecialchars($inspection['spad_varoa']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Metoda monitoringu</label>
            <select name="monitoring_metoda" class="form-select">
                <?php
                $metody = ['', 'smyv', 'cukr', 'CO2', 'podložka'];
                foreach ($metody as $m) {
                    $sel = ($inspection['monitoring_metoda'] === $m) ? 'selected' : '';
                    echo "<option value=\"$m\" $sel>$m</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Použité léčivo</label>
            <select name="lecivo_id" class="form-select">
                <option value="">— žádné —</option>
                <?php foreach ($leky as $lek): ?>
                    <option value="<?= $lek['id'] ?>" <?= $inspection['lecivo_id'] == $lek['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($lek['nazev']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Počet žihadel</label>
            <input type="number" name="zihadla" class="form-control" value="<?= htmlspecialchars($inspection['zihadla']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Poznámka</label>
            <textarea name="poznamka" class="form-control"><?= htmlspecialchars($inspection['notes']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Uložit změny</button>
    </form>
</div>
</body>
</html>

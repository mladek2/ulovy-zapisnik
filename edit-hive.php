<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$hiveId = $_GET['id'] ?? null;
if (!$hiveId) {
    die("Chyba: chybějící ID úlu.");
}

$userId = $_SESSION['user_id'];

// Načti detaily úlu
$stmt = $pdo->prepare("SELECT * FROM hives WHERE id = ? AND user_id = ?");
$stmt->execute([$hiveId, $userId]);
$hive = $stmt->fetch();

if (!$hive) {
    die("Úl nenalezen nebo k němu nemáte přístup.");
}

// Načti stanoviště uživatele
$locations = $pdo->prepare("SELECT id, nazev FROM locations WHERE user_id = ?");
$locations->execute([$userId]);
$stanoviste = $locations->fetchAll();

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location_id = $_POST['location_id'];
    $puvod = $_POST['puvod_vcelstva'];
    $datum = $_POST['datum_zalozeni'];
    $mira = $_POST['ramkova_mira'];
    $nastavky = $_POST['pocet_nastavku'];
    $mrizka = isset($_POST['materi_mrizka']) ? 1 : 0;
    $krmitko = isset($_POST['krmitko']) ? 1 : 0;
    $typKrmitka = $_POST['typ_krmitka'] ?? null;
    $vynos = $_POST['medny_vynos'] ?? 0;
    $zasoby = $_POST['zasoby'] ?? 0;
    $plod_z = $_POST['plod_zavickovany'] ?? 0;
    $plod_n = $_POST['plod_nezavickovany'] ?? 0;
    $stav_matky = isset($_POST['queen_seen']) ? 1 : 0;
    require_once 'mother-handler.php';
//include 'mother-handler.php';
$finalMotherId = $newMotherId ?: $hive['matka_id'] ?? null;
    $stmt = $pdo->prepare("UPDATE hives SET 
        location_id = ?, 
        puvod_vcelstva = ?, 
        created_at = ?, 
        ramkova_mira = ?, 
        pocet_nastavku = ?, 
        materi_mrizka = ?, 
        krmitko = ?, 
        typ_krmitka = ?, 
        medny_vynos = ?,
        zasoby = ?,
        plod_zavickovany = ?,
        plod_nezavickovany = ?,
        queen_seen = ?,
        matka_id = ?
        WHERE id = ?");

   $stmt->execute([
        $location_id, $puvod, $datum, $mira, $nastavky,
        $mrizka, $krmitko, $typKrmitka, $vynos,
        $zasoby, $plod_z, $plod_n, $stav_matky,
        $finalMotherId, $hiveId
    ]);


    $success = "Úl byl úspěšně aktualizován.";

    // znovu načíst aktualizovaná data
   $stmt = $pdo->prepare("SELECT * FROM hives WHERE id = ? AND user_id = ?");
    $stmt->execute([$hiveId, $userId]);
    $hive = $stmt->fetch();
    
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Upravit úl</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2>Úprava úlu</h2>

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label>Stanoviště</label>
            <select name="location_id" class="form-control" required>
                <?php foreach ($stanoviste as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $hive['location_id'] == $s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['nazev']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Původ včelstva</label>
            <input type="text" name="puvod_vcelstva" value="<?= htmlspecialchars($hive['puvod_vcelstva']) ?>" class="form-control">
        </div>
        <div class="mb-3">
            <label>Datum založení</label>
            <input type="date" name="datum_zalozeni" value="<?= htmlspecialchars($hive['created_at']) ?>" class="form-control">
        </div>
        <div class="mb-3">
            <label>Rámková míra</label>
            <input type="text" name="ramkova_mira" value="<?= htmlspecialchars($hive['ramkova_mira']) ?>" class="form-control">
        </div>
        <div class="mb-3">
            <label>Počet nástavků</label>
            <input type="number" name="pocet_nastavku" value="<?= htmlspecialchars($hive['pocet_nastavku']) ?>" class="form-control">
        </div>
        <div class="form-check">
            <input type="checkbox" name="materi_mrizka" class="form-check-input" <?= $hive['materi_mrizka'] ? 'checked' : '' ?>>
            <label class="form-check-label">Mateří mřížka</label>
        </div>
        <div class="form-check">
            <input type="checkbox" name="krmitko" class="form-check-input" <?= $hive['krmitko'] ? 'checked' : '' ?>>
            <label class="form-check-label">Krmítko</label>
        </div>
        <div class="mb-3">
            <label>Typ krmítka</label>
            <input type="text" name="typ_krmitka" value="<?= htmlspecialchars($hive['typ_krmitka']) ?>" class="form-control">
        </div>
        <div class="mb-3">
            <label>Medný výnos (kg)</label>
            <input type="number" step="0.1" name="medny_vynos" value="<?= htmlspecialchars($hive['medny_vynos']) ?>" class="form-control">
        </div>
        <div class="mb-3">
            <label>Zásoby (dm²)</label>
            <input type="number" name="zasoby" step="0.1" value="<?= htmlspecialchars($hive['zasoby']) ?>" class="form-control">
        </div>
        <div class="mb-3">
            <label>Zavíčkovaný plod (dm²)</label>
            <input type="number" name="plod_zavickovany" step="0.1" value="<?= htmlspecialchars($hive['plod_zavickovany']) ?>" class="form-control">
        </div>
        <div class="mb-3">
            <label>Nezavíčkovaný plod (dm²)</label>
            <input type="number" name="plod_nezavickovany" step="0.1" value="<?= htmlspecialchars($hive['plod_nezavickovany']) ?>" class="form-control">
        </div>
        <div class="mb-3">
           <div class="form-check mb-3">
    <input type="checkbox" class="form-check-input" id="queen_seen" name="queen_seen" value="1" <?= $hive['queen_seen'] ? 'checked' : '' ?>>
    <label class="form-check-label" for="queen_seen">Matka byla viděna při poslední kontrole</label>
</div>
        </div>
       <?php include 'partial-mother-form.php'; ?>
        <button type="submit" class="btn btn-primary">Uložit změny</button>
    </form>
</div>
</body>
</html>

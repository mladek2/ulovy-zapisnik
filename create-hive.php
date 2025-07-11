<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once 'mother-handler.php';
//require_once __DIR__ . '/helpers/permissions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Vyber stanoviště uživatele
$locations = $pdo->prepare("SELECT id, nazev FROM locations WHERE user_id = ?");
$locations->execute([$_SESSION['user_id']]);
$mojeStanoviste = $locations->fetchAll();
$locations->execute();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_once 'mother-handler.php';
include 'mother-handler.php';
$finalMotherId = $newMotherId;
    $locationId = $_POST['location_id'] ?? null;
    $puvod = $_POST['puvod_vcelstva'] ?? '';
    $created_at = $_POST['created_at'] ?? null;
    $ramkova_mira = $_POST['ramkova_mira'] ?? '';
    $nastavky = intval($_POST['pocet_nastavku'] ?? 0);
    $krmitko = isset($_POST['krmitko']) ? 1 : 0;
    $typ_krmitka = $_POST['typ_krmitka'] ?? '';
    $mrizka = isset($_POST['materi_mrizka']) ? 1 : 0;
    $vynos = floatval($_POST['medny_vynos'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    
    if (!$locationId) {
        $error = 'Vyberte stanoviště.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO hives (name, location_id, puvod_vcelstva, created_at, ramkova_mira, pocet_nastavku, krmitko, typ_krmitka, materi_mrizka, medny_vynos, user_id, matka_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $locationId, $puvod, $created_at, $ramkova_mira, $nastavky, $krmitko, $typ_krmitka, $mrizka, $vynos, $_SESSION['user_id'], $finalMotherId]);

        $hiveId = $pdo->lastInsertId();

        // Základní oprávnění creator = admin
        $perm = $pdo->prepare("INSERT INTO user_hive_permissions (user_id, hive_id, role) VALUES (?, ?, 'admin')");
        $perm->execute([$userId, $hiveId]);

        $success = 'Úl byl úspěšně vytvořen.';
    }
   
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Vytvořit úl</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container mt-5">
    <h2>Vytvořit nový úl 🐝</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Stanoviště</label>
          <select name="location_id" class="form-control" required>
    <?php foreach ($mojeStanoviste as $loc): ?>
        <option value="<?= $loc['id'] ?>"><?= htmlspecialchars($loc['nazev']) ?></option>
    <?php endforeach; ?>
    
</select>

        </div>
<div class="mb-3">
    <label for="name" class="form-label">Název úlu</label>
    <input type="text" class="form-control" name="name" id="name">
</div>

        <div class="mb-3">
            <label class="form-label">Původ včelstva</label>
            <select name="puvod_vcelstva" class="form-select">
                <option value="">—</option>
                <option value="vlastní oddělek">vlastní oddělek</option>
                <option value="roj">roj</option>
                <option value="vyzimované">vyzimované</option>
                <option value="koupený oddělek">koupený oddělek</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Datum založení</label>
            <input type="date" name="created_at" value="<?= htmlspecialchars($hive['created_at'] ?? date('Y-m-d')) ?>" 
       class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Rámková míra</label>
            <input type="text" name="ramkova_mira" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Počet nástavků</label>
            <input type="number" name="pocet_nastavku" class="form-control">
        </div>

        <div class="form-check mb-2">
            <input type="checkbox" name="krmitko" class="form-check-input" id="krmitko">
            <label class="form-check-label" for="krmitko">Vložené krmítko</label>
        </div>

        <div class="mb-3">
            <label class="form-label">Typ krmítka</label>
            <input type="text" name="typ_krmitka" class="form-control">
        </div>

        <div class="form-check mb-2">
            <input type="checkbox" name="materi_mrizka" class="form-check-input" id="materi_mrizka">
            <label class="form-check-label" for="materi_mrizka">Použita mateří mřížka</label>
        </div>

        <div class="mb-3">
            <label class="form-label">Medný výnos (kg)</label>
            <input type="number" step="0.1" name="medny_vynos" class="form-control">
        </div>
 <?php include 'partial-mother-form.php'; ?>
        <button type="submit" class="btn btn-primary">Vytvořit úl</button>
    </form>
</div>
</body>
</html>

<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$locationId = $_GET['id'] ?? null;

if (!$locationId) {
    die("Neplatné ID stanoviště.");
}

// Načíst data o stanovišti
$stmt = $pdo->prepare("SELECT * FROM locations WHERE id = ?");
$stmt->execute([$locationId]);
$location = $stmt->fetch();

if (!$location) {
    die("Stanoviště nenalezeno.");
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cislo = trim($_POST['cislo'] ?? '');
    $nazev = trim($_POST['nazev'] ?? '');
    $katastr = trim($_POST['katastr'] ?? '');
    $vynos = floatval($_POST['medny_vynos'] ?? 0);

    if (empty($nazev)) {
        $error = "Název je povinný.";
    } else {
         $stmt = $pdo->prepare("UPDATE locations SET 
        code = ?, 
        name = ?, 
        cadastral_area = ?, 
        medny_vynos = ?
        WHERE id = ?");
    $stmt->execute([$cislo, $nazev, $katastr, $vynos, $locationId]);

    $success = "Stanoviště bylo upraveno.";

// Znovu načti aktualizovaná data
$stmt = $pdo->prepare("SELECT * FROM locations WHERE id = ?");
$stmt->execute([$locationId]);
$location = $stmt->fetch();
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Úprava stanoviště</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container mt-5">
    <h2>Úprava stanoviště <?php echo $location['nazev'] ?></h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label for="cislo" class="form-label">Číslo stanoviště</label>
            <input type="text" name="cislo" class="form-control" value="<?= htmlspecialchars($location['code']) ?>">
        </div>

        <div class="mb-3">
            <label for="nazev" class="form-label">Název stanoviště</label>
            <input type="text" name="nazev" class="form-control" value="<?= htmlspecialchars($location['nazev']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="katastr" class="form-label">Katastrální území</label>
            <input type="text" name="katastr" class="form-control" value="<?= htmlspecialchars($location['cadastral_area']) ?>">
        </div>

        <div class="mb-3">
            <label for="medny_vynos" class="form-label">Medný výnos (kg)</label>
            <input type="number" step="0.1" name="medny_vynos" class="form-control" value="<?= htmlspecialchars($location['medny_vynos']) ?>">
        </div>

        <button type="submit" class="btn btn-primary">Uložit změny</button>
    </form>
</div>
</body>
</html>

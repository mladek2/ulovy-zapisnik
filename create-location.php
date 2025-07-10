<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nazev = trim($_POST['nazev'] ?? '');
    $code = trim($_POST['code'] ?? '');
    $cadastral_area = trim($_POST['cadastral_area'] ?? '');
    $vynos = floatval($_POST['medny_vynos'] ?? 0);

    if (!$nazev) {
        $error = "Název je povinný.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO locations (nazev, code, cadastral_area, medny_vynos, user_id) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$nazev, $code, $cadastral_area, $vynos, $_SESSION['user_id']]);

        $success = "Stanoviště bylo vytvořeno.";
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Nové stanoviště</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2>Vytvořit nové stanoviště 📍</h2>

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label>Název stanoviště</label>
            <input type="text" name="nazev" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Číslo</label>
            <input type="text" name="code" class="form-control">
        </div>
        <div class="mb-3">
            <label>Katastr</label>
            <input type="text" name="cadastral_area" class="form-control">
        </div>
        <div class="mb-3">
            <label>Medný výnos (kg)</label>
            <input type="number" name="medny_vynos" step="0.1" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Vytvořit</button>
    </form>
</div>
</body>
</html>

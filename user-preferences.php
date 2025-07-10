<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $minZasoby = trim($_POST['minimalni_zasoby'] ?? '');

    if (!ctype_digit($minZasoby)) {
        $error = "Zadejte prosím celé číslo jako minimální hladinu zásob.";
    } else {
        // Pokud záznam existuje, aktualizujeme, jinak vložíme
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_preferences WHERE user_id = ?");
        $stmt->execute([$userId]);
        $exists = $stmt->fetchColumn();

        if ($exists) {
            $stmt = $pdo->prepare("UPDATE user_preferences SET minimalni_zasoby = ? WHERE user_id = ?");
            $stmt->execute([$minZasoby, $userId]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO user_preferences (user_id, minimalni_zasoby) VALUES (?, ?)");
            $stmt->execute([$userId, $minZasoby]);
        }

        $success = "Předvolby byly úspěšně uloženy.";
    }
}

$stmt = $pdo->prepare("SELECT minimalni_zasoby FROM user_preferences WHERE user_id = ?");
$stmt->execute([$userId]);
$currentValue = $stmt->fetchColumn() ?? '';
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Nastavení předvoleb</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2>Nastavení uživatelských předvoleb</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label for="minimalni_zasoby" class="form-label">Minimální hladina zásob (dm²)</label>
            <input type="number" name="minimalni_zasoby" id="minimalni_zasoby" class="form-control" value="<?= htmlspecialchars($currentValue) ?>" min="0" required>
        </div>
        <button type="submit" class="btn btn-primary">Uložit předvolby</button>
    </form>
</div>
</body>
</html>

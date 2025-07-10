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

// Načteme aktuální data
$stmt = $pdo->prepare("SELECT name, email, chovatel_number, password_hash FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName = trim($_POST['name'] ?? '');
    $newChovatel = trim($_POST['chovatel_number'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($currentPassword)) {
        $error = 'Musíte zadat své aktuální heslo pro provedení změn.';
    } elseif (!password_verify($currentPassword, $user['password_hash'])) {
        $error = 'Aktuální heslo není správné.';
    } else {
        $fields = [];
        $params = [];

        if (!empty($newName) && $newName !== $user['name']) {
            $fields[] = 'name = ?';
            $params[] = $newName;
        }

        if (!empty($newChovatel) && $newChovatel !== $user['chovatel_number']) {
            $fields[] = 'chovatel_number = ?';
            $params[] = $newChovatel;
        }

        if (!empty($newPassword)) {
            if ($newPassword !== $confirmPassword) {
                $error = 'Nová hesla se neshodují.';
            } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $newPassword)) {
                $error = 'Heslo musí mít alespoň 8 znaků, obsahovat malé a velké písmeno a číslici.';
            } else {
                $fields[] = 'password_hash = ?';
                $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
            }
        }

        if (!$error && !empty($fields)) {
            $params[] = $userId;
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $success = 'Změny byly úspěšně uloženy.';
            // reload uživatele
            $stmt = $pdo->prepare("SELECT name, email, chovatel_number, password_hash FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
        } elseif (!$error) {
            $success = 'Neproběhla žádná změna.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
     <link rel="icon" type="image/x-icon" href="favicon.png">
    <title>Úprava údajů – Úlový zápisník</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container mt-5">
    <h2>Úprava údajů uživatele 🧑‍💻</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label for="name" class="form-label">Jméno</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>">
        </div>

        <div class="mb-3">
            <label for="chovatel_number" class="form-label">Číslo chovatele</label>
            <input type="text" name="chovatel_number" class="form-control" value="<?= htmlspecialchars($user['chovatel_number']) ?>">
        </div>

        <hr>

        <div class="mb-3">
            <label for="new_password" class="form-label">Nové heslo</label>
            <input type="password" name="new_password" class="form-control">
        </div>

        <div class="mb-3">
            <label for="confirm_password" class="form-label">Nové heslo znovu</label>
            <input type="password" name="confirm_password" class="form-control">
        </div>

        <hr>

        <div class="mb-3">
            <label for="current_password" class="form-label">Aktuální heslo <span class="text-danger">*</span></label>
            <input type="password" name="current_password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Uložit změny</button>
    </form>
</div>
</body>
</html>

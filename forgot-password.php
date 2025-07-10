<?php
session_start();
$success = $_GET['sent'] ?? false;
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="favicon.png">
    <title>Zapomenuté heslo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container mt-5">
    <h2>Obnova hesla</h2>

    <?php if ($success): ?>
        <div class="alert alert-success">
            Pokud byl e-mail nalezen, byl odeslán odkaz pro reset hesla.
        </div>
    <?php endif; ?>

    <form action="send-reset.php" method="post">
        <div class="mb-3">
            <label for="email">Zadejte svůj e‑mail</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Odeslat odkaz</button>
    </form>
</div>
</body>
</html>

<?php
// mother-handler.php
// Zajistí vytvoření nebo aktualizaci matky podle $_POST

$newMotherId = null;

$barva = trim($_POST['barva'] ?? '');
$rok = intval($_POST['rok_narozeni'] ?? 0);
$puvod = trim($_POST['puvod'] ?? '');
$motherId = $_POST['mother_id'] ?? null;

$hasData = $barva || $rok || $puvod;

if ($hasData) {
    if ($motherId) {
        // UPDATE existující matky
        $stmt = $pdo->prepare("UPDATE matky SET barva = ?, rok_narozeni = ?, puvod = ? WHERE id = ?");
        $stmt->execute([$barva, $rok ?: null, $puvod, $motherId]);
        $newMotherId = $motherId;
    } else {
        // INSERT nové matky
        $stmt = $pdo->prepare("INSERT INTO matky (barva, rok_narozeni, puvod) VALUES (?, ?, ?)");
        $stmt->execute([$barva, $rok ?: null, $puvod]);
        $newMotherId = $pdo->lastInsertId();
    }
}

// $newMotherId můžeš pak použít v insertu/aktu úlu
// např. $stmt->execute([$name, ..., $newMotherId]);

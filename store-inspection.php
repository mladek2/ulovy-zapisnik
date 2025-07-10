<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$meds = $pdo->query("SELECT id, nazev FROM treatments ORDER BY nazev")->fetchAll();
$userId = $_SESSION['user_id'];

// Získání hodnot z formuláře
$hiveId = $_POST['hive_id'] ?? null;
$date = $_POST['inspection_date'] ?? null;
$queenSeen = isset($_POST['queen_seen']) ? 1 : 0;
$eggsSeen = isset($_POST['eggs_seen']) ? 1 : 0;
$queen_cells = (int) ($_POST['matecniky'] ?? 0);
$zasoby = (float) ($_POST['zasoby'] ?? 0);
$plodZ = (float) ($_POST['plod_zavickovany'] ?? 0);
$plodN = (float) ($_POST['plod_nezavickovany'] ?? 0);
$spadVaroa = (int) ($_POST['spad_varoa'] ?? 0);
$lecivoId = $_POST['lecivo_id'] !== '' ? (int) $_POST['lecivo_id'] : null;
$lecivoMnozstvi = (float) ($_POST['lecivo_mnozstvi'] ?? 0);
$testTyp = $_POST['varoa_test'] ?? null;
$testSpad = (int) ($_POST['varoa_test_spad'] ?? 0);
$krmeni = (float) ($_POST['krmeni_l'] ?? 0);
$mrizka = isset($_POST['materi_mrizka']) ? 1 : 0;
$krmitko = isset($_POST['krmitko']) ? 1 : 0;
$zihadla = (int) ($_POST['zihadla'] ?? 0);
$poznamka = trim($_POST['poznamka'] ?? '');

if (!$hiveId || !$date) {
    die('Chybí ID úlu nebo datum kontroly.');
}

$stmt = $pdo->prepare("INSERT INTO inspections (
    hive_id, inspection_date, queen_seen, eggs_seen, queen_cells,
    zasoby, plod_zavickovany, plod_nezavickovany, spad_varoa,
    lecivo_id, lecivo_mnozstvi,
    monitoring_metoda, varoa_test_spad,
    feed_added_liters, queen_excluder, 	feeder_inserted, zihadla, notes
) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

$stmt->execute([
    $hiveId, $date, $queenSeen, $eggsSeen, $queen_cells,
    $zasoby, $plodZ, $plodN, $spadVaroa,
    $lecivoId, $lecivoMnozstvi,
    $testTyp, $testSpad,
    $krmeni, $mrizka, $krmitko, $zihadla, $poznamka
]);

header('Location: inspections.php');
exit;

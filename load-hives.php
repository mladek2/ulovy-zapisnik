<?php
session_start();
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || !isset($_GET['location_id'])) {
    echo json_encode([]);
    exit;
}

$userId = $_SESSION['user_id'];
$locationId = (int) $_GET['location_id'];

$stmt = $pdo->prepare("SELECT id, id AS name FROM hives WHERE location_id = ? AND user_id = ?");
$stmt->execute([$locationId, $userId]);
$hives = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($hives);

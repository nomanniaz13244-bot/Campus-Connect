<?php
require_once '../../includes/auth_check.php';
require_login();
require_once '../../config/db.php';

$id = (int)($_POST['id'] ?? 0);

$stmt = $conn->prepare("SELECT seller_id, image_path FROM marketplace_items WHERE item_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row || $row['seller_id'] != $_SESSION['user_id']) {
    die("Not authorized.");
}

$del = $conn->prepare("DELETE FROM marketplace_items WHERE item_id = ?");
$del->bind_param("i", $id);
$del->execute();

if ($row['image_path'] && file_exists(__DIR__ . '/../../' . $row['image_path'])) {
    unlink(__DIR__ . '/../../' . $row['image_path']);
}

header("Location: index.php");
exit;

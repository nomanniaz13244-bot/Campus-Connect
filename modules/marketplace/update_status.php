<?php
require_once '../../includes/auth_check.php';
require_login();
require_once '../../config/db.php';

$id = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';
if (!in_array($status, ['available','sold','removed'], true)) {
    die("Invalid status.");
}

$stmt = $conn->prepare("SELECT seller_id FROM marketplace_items WHERE item_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row || $row['seller_id'] != $_SESSION['user_id']) {
    die("Not authorized.");
}

$update = $conn->prepare("UPDATE marketplace_items SET status = ? WHERE item_id = ?");
$update->bind_param("si", $status, $id);
$update->execute();

header("Location: view.php?id=" . $id);
exit;

<?php
require_once '../../includes/auth_check.php';
require_login();
require_once '../../config/db.php';

$id = (int)($_POST['id'] ?? 0);

$stmt = $conn->prepare("SELECT reporter_id FROM lost_found_items WHERE report_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row || $row['reporter_id'] != $_SESSION['user_id']) {
    die("Not authorized.");
}

$update = $conn->prepare("UPDATE lost_found_items SET status = 'resolved' WHERE report_id = ?");
$update->bind_param("i", $id);
$update->execute();

header("Location: index.php");
exit;

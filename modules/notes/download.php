<?php
require_once '../../includes/auth_check.php';
require_login();
require_once '../../config/db.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT file_path, title FROM notes WHERE note_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$note = $stmt->get_result()->fetch_assoc();

if (!$note) {
    die("Note not found.");
}

$full_path = __DIR__ . '/../../' . $note['file_path'];
if (!file_exists($full_path)) {
    die("File is missing on server.");
}

// increment download count
$conn->query("UPDATE notes SET download_count = download_count + 1 WHERE note_id = " . $id);

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($note['file_path']) . '"');
header('Content-Length: ' . filesize($full_path));
readfile($full_path);
exit;

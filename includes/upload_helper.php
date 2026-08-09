<?php
/**
 * Shared upload helper.
 * Usage: $path = handle_upload($_FILES['image'], 'marketplace', ['jpg','jpeg','png','webp']);
 * Returns relative path (to store in DB) on success, or null if no file was sent.
 * Throws Exception on invalid file.
 */
function handle_upload($file, $subfolder, array $allowed_ext, $max_bytes = 5 * 1024 * 1024) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // no file uploaded, that's fine (optional upload)
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("File upload failed (error code {$file['error']}).");
    }
    if ($file['size'] > $max_bytes) {
        throw new Exception("File is too large. Max size is " . ($max_bytes / 1024 / 1024) . "MB.");
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_ext, true)) {
        throw new Exception("Invalid file type. Allowed: " . implode(', ', $allowed_ext));
    }

    $safe_name = bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
    $target_dir = __DIR__ . "/../assets/uploads/{$subfolder}/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    $target_path = $target_dir . $safe_name;

    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        throw new Exception("Could not save uploaded file.");
    }

    return "assets/uploads/{$subfolder}/{$safe_name}";
}

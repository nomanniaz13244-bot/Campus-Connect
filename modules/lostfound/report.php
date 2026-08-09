<?php
require_once '../../includes/auth_check.php';
require_login();
require_once '../../config/db.php';
require_once '../../includes/upload_helper.php';
$page_title = "Report an Item";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_type = $_POST['item_type'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');

    if (!in_array($item_type, ['lost','found'], true)) $errors[] = "Select whether this is a lost or found item.";
    if ($title === '') $errors[] = "Title is required.";

    $image_path = null;
    if (empty($errors)) {
        try {
            $image_path = handle_upload($_FILES['image'] ?? null, 'lostfound', ['jpg','jpeg','png','webp']);
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO lost_found_items (reporter_id, item_type, title, description, location, image_path) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $_SESSION['user_id'], $item_type, $title, $description, $location, $image_path);
        if ($stmt->execute()) {
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
    }
}

include '../../includes/header.php';
?>
<div class="page-container" style="max-width:600px;">
    <div class="card">
        <h2>Report an Item</h2>
        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Type</label>
                <select name="item_type" class="form-control" required>
                    <option value="">-- Select --</option>
                    <option value="lost">I lost something</option>
                    <option value="found">I found something</option>
                </select>
            </div>
            <div class="form-group">
                <label>Item Title</label>
                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" class="form-control" placeholder="e.g. Library, Block A" value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Image (optional)</label>
                <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
            </div>
            <button type="submit" class="btn btn-primary">Submit Report</button>
            <a href="index.php" class="btn btn-outline" style="margin-left:8px;">Cancel</a>
        </form>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>

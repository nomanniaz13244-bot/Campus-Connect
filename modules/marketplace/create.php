<?php
require_once '../../includes/auth_check.php';
require_login();
require_once '../../config/db.php';
require_once '../../includes/upload_helper.php';
$page_title = "Post an Item";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = $_POST['price'] ?? '';
    $condition_type = $_POST['condition_type'] ?? 'used';

    if ($title === '' || strlen($title) < 3) $errors[] = "Title must be at least 3 characters.";
    if (!is_numeric($price) || $price < 0) $errors[] = "Enter a valid price.";

    $image_path = null;
    if (empty($errors)) {
        try {
            $image_path = handle_upload($_FILES['image'] ?? null, 'marketplace', ['jpg','jpeg','png','webp']);
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO marketplace_items (seller_id, title, description, category, price, condition_type, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssdss", $_SESSION['user_id'], $title, $description, $category, $price, $condition_type, $image_path);
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
        <h2>Post an Item for Sale</h2>
        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category" class="form-control" placeholder="e.g. Books, Electronics" value="<?php echo htmlspecialchars($_POST['category'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Price (Rs.)</label>
                <input type="number" step="0.01" name="price" class="form-control" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Condition</label>
                <select name="condition_type" class="form-control">
                    <option value="new">New</option>
                    <option value="like_new">Like New</option>
                    <option value="used" selected>Used</option>
                    <option value="worn">Worn</option>
                </select>
            </div>
            <div class="form-group">
                <label>Image (optional)</label>
                <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
            </div>
            <button type="submit" class="btn btn-primary">Post Item</button>
            <a href="index.php" class="btn btn-outline" style="margin-left:8px;">Cancel</a>
        </form>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>

<?php
require_once '../../includes/auth_check.php';
require_role(['club_admin','admin']);
require_once '../../config/db.php';
require_once '../../includes/upload_helper.php';
$page_title = "Post Event";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category = $_POST['category'] ?? 'event';
    $description = trim($_POST['description'] ?? '');
    $event_date = $_POST['event_date'] ?? null;
    $location = trim($_POST['location'] ?? '');

    if ($title === '') $errors[] = "Title is required.";
    if (!in_array($category, ['event','workshop','internship'], true)) $errors[] = "Invalid category.";

    $image_path = null;
    if (empty($errors)) {
        try {
            $image_path = handle_upload($_FILES['image'] ?? null, 'events', ['jpg','jpeg','png','webp']);
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $event_date = $event_date !== '' ? $event_date : null;
        $stmt = $conn->prepare("INSERT INTO events (posted_by, title, category, description, event_date, location, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $_SESSION['user_id'], $title, $category, $description, $event_date, $location, $image_path);
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
        <h2>Post an Event / Internship</h2>
        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category" class="form-control">
                    <option value="event">Campus Event</option>
                    <option value="workshop">Workshop</option>
                    <option value="internship">Internship</option>
                </select>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>Date (optional)</label>
                <input type="date" name="event_date" class="form-control">
            </div>
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Image (optional)</label>
                <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
            </div>
            <button type="submit" class="btn btn-primary">Post</button>
            <a href="index.php" class="btn btn-outline" style="margin-left:8px;">Cancel</a>
        </form>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>

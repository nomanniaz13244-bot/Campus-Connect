<?php
require_once '../../includes/auth_check.php';
require_login();
require_once '../../config/db.php';
require_once '../../includes/upload_helper.php';
$page_title = "Upload Notes";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $semester = trim($_POST['semester'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '') $errors[] = "Title is required.";
    if ($subject === '') $errors[] = "Subject is required.";
    if ($semester === '') $errors[] = "Semester is required.";

    $file_path = null;
    if (empty($errors)) {
        try {
            $file_path = handle_upload($_FILES['note_file'] ?? null, 'notes', ['pdf','doc','docx','ppt','pptx','zip'], 15 * 1024 * 1024);
            if ($file_path === null) $errors[] = "Please choose a file to upload.";
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO notes (uploader_id, title, subject, semester, description, file_path) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $_SESSION['user_id'], $title, $subject, $semester, $description, $file_path);
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
        <h2>Upload Notes</h2>
        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Subject</label>
                <input type="text" name="subject" class="form-control" placeholder="e.g. Data Structures" value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Semester</label>
                <input type="text" name="semester" class="form-control" placeholder="e.g. 3rd" value="<?php echo htmlspecialchars($_POST['semester'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>File (PDF, DOC, PPT, ZIP — max 15MB)</label>
                <input type="file" name="note_file" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.zip" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
            <a href="index.php" class="btn btn-outline" style="margin-left:8px;">Cancel</a>
        </form>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>

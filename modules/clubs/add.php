<?php
require_once '../../includes/auth_check.php';
require_login();
require_once '../../config/db.php';
require_once '../../includes/upload_helper.php';

// Only admin can create clubs
if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$page_title = "Add New Club";
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_club'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    
    // Validation
    if (empty($name)) {
        $errors[] = "Club name is required.";
    } elseif (strlen($name) < 2) {
        $errors[] = "Club name must be at least 2 characters.";
    } elseif (strlen($name) > 150) {
        $errors[] = "Club name must not exceed 150 characters.";
    }
    
    if (empty($category)) {
        $errors[] = "Category is required.";
    } elseif (strlen($category) > 80) {
        $errors[] = "Category must not exceed 80 characters.";
    }
    
    if (strlen($description) > 2000) {
        $errors[] = "Description must not exceed 2000 characters.";
    }
    
    // Handle file upload
    $logo_path = null;
    if (!empty($_FILES['logo']['name'])) {
        try {
            $logo_path = handle_upload($_FILES['logo'] ?? null, 'clubs', ['jpg','jpeg','png','webp']);
        } catch (Exception $e) {
            $errors[] = "Upload error: " . $e->getMessage();
        }
    }
    
    // Insert club if no errors
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO clubs (name, description, category, logo_path, club_admin_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        
        if ($stmt) {
            // Use admin ID as club admin, or use NULL
            $admin_id = $_SESSION['user_id'];
            $stmt->bind_param("ssssi", $name, $description, $category, $logo_path, $admin_id);
            
            if ($stmt->execute()) {
                $success = true;
                $_SESSION['success_message'] = "Club '{$name}' created successfully!";
            } else {
                $errors[] = "Database error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
    }
}

include '../../includes/header.php';
?>

<div class="page-container" style="max-width:700px;">
    <div class="card">
        <h2>Add New Club</h2>
        <p style="color:var(--text-muted);">Create a new club for campus community.</p>
    </div>
    
    <?php if ($success): ?>
        <div class="card" style="background:var(--success); color:white; border-radius:6px; margin-bottom:20px;">
            <h3 style="margin:0 0 8px;">✅ Club Created Successfully!</h3>
            <p style="margin:0;">The club has been added to the platform.</p>
            <div style="margin-top:12px;">
                <a href="index.php" class="btn btn-primary" style="background:white; color:var(--success); border:none;">Back to Clubs</a>
            </div>
        </div>
    <?php else: ?>
        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <div class="card" style="background:var(--danger); color:white; border-radius:6px; margin-bottom:12px;">
                    <strong>❌ Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="card">
            <div class="form-group">
                <label for="name" style="font-weight:bold;">Club Name *</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="Enter club name" required maxlength="150" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                <small style="color:var(--text-muted);">2-150 characters</small>
            </div>
            
            <div class="form-group">
                <label for="category" style="font-weight:bold;">Category *</label>
                <select id="category" name="category" class="form-control" required>
                    <option value="">-- Select Category --</option>
                    <option value="Academic" <?php echo ($_POST['category'] ?? '') === 'Academic' ? 'selected' : ''; ?>>Academic</option>
                    <option value="Sports" <?php echo ($_POST['category'] ?? '') === 'Sports' ? 'selected' : ''; ?>>Sports</option>
                    <option value="Arts" <?php echo ($_POST['category'] ?? '') === 'Arts' ? 'selected' : ''; ?>>Arts</option>
                    <option value="Technical" <?php echo ($_POST['category'] ?? '') === 'Technical' ? 'selected' : ''; ?>>Technical</option>
                    <option value="Social" <?php echo ($_POST['category'] ?? '') === 'Social' ? 'selected' : ''; ?>>Social</option>
                    <option value="Recreation" <?php echo ($_POST['category'] ?? '') === 'Recreation' ? 'selected' : ''; ?>>Recreation</option>
                    <option value="Other" <?php echo ($_POST['category'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="description" style="font-weight:bold;">Description</label>
                <textarea id="description" name="description" class="form-control" placeholder="Enter club description..." rows="5" maxlength="2000"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                <small style="color:var(--text-muted);">0-2000 characters</small>
            </div>
            
            <div class="form-group">
                <label for="logo" style="font-weight:bold;">Club Logo</label>
                <input type="file" id="logo" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                <small style="color:var(--text-muted);">JPG, PNG, WebP (Max 5MB)</small>
            </div>
            
            <div style="display:flex; gap:10px;">
                <button type="submit" name="add_club" class="btn btn-primary" style="flex:1;">Create Club</button>
                <a href="index.php" class="btn btn-outline" style="flex:1; text-align:center;">Cancel</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>

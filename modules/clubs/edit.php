<?php
require_once '../../includes/auth_check.php';
require_login();
require_once '../../config/db.php';
require_once '../../includes/upload_helper.php';

// Only admin can edit clubs
if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$club_id = (int)($_GET['id'] ?? 0);

if ($club_id <= 0) {
    header('Location: index.php');
    exit;
}

// Fetch club data
$stmt = $conn->prepare("SELECT * FROM clubs WHERE club_id = ?");
if (!$stmt) {
    die("Database error: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $club_id);
$stmt->execute();
$result = $stmt->get_result();
$club = $result->fetch_assoc();
$stmt->close();

if (!$club) {
    header('Location: index.php');
    exit;
}

$page_title = "Edit Club: " . $club['name'];
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_club'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $logo_path = $club['logo_path']; // Keep existing logo by default
    
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
    if (!empty($_FILES['logo']['name'])) {
        try {
            $new_logo = handle_upload($_FILES['logo'] ?? null, 'clubs', ['jpg','jpeg','png','webp']);
            if ($new_logo) {
                $logo_path = $new_logo;
            }
        } catch (Exception $e) {
            $errors[] = "Upload error: " . $e->getMessage();
        }
    }
    
    // Update club if no errors
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE clubs SET name = ?, description = ?, category = ?, logo_path = ? WHERE club_id = ?");
        
        if ($stmt) {
            $stmt->bind_param("ssssi", $name, $description, $category, $logo_path, $club_id);
            
            if ($stmt->execute()) {
                $success = true;
                $club['name'] = $name;
                $club['description'] = $description;
                $club['category'] = $category;
                $club['logo_path'] = $logo_path;
                $_SESSION['success_message'] = "Club updated successfully!";
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
        <h2>Edit Club</h2>
        <p style="color:var(--text-muted);">Update club information.</p>
    </div>
    
    <?php if ($success): ?>
        <div class="card" style="background:var(--success); color:white; border-radius:6px; margin-bottom:20px;">
            <h3 style="margin:0 0 8px;">✅ Club Updated Successfully!</h3>
            <p style="margin:0;">Changes have been saved.</p>
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
                <input type="text" id="name" name="name" class="form-control" placeholder="Enter club name" required maxlength="150" value="<?php echo htmlspecialchars($club['name']); ?>">
                <small style="color:var(--text-muted);">2-150 characters</small>
            </div>
            
            <div class="form-group">
                <label for="category" style="font-weight:bold;">Category *</label>
                <select id="category" name="category" class="form-control" required>
                    <option value="">-- Select Category --</option>
                    <option value="Academic" <?php echo $club['category'] === 'Academic' ? 'selected' : ''; ?>>Academic</option>
                    <option value="Sports" <?php echo $club['category'] === 'Sports' ? 'selected' : ''; ?>>Sports</option>
                    <option value="Arts" <?php echo $club['category'] === 'Arts' ? 'selected' : ''; ?>>Arts</option>
                    <option value="Technical" <?php echo $club['category'] === 'Technical' ? 'selected' : ''; ?>>Technical</option>
                    <option value="Social" <?php echo $club['category'] === 'Social' ? 'selected' : ''; ?>>Social</option>
                    <option value="Recreation" <?php echo $club['category'] === 'Recreation' ? 'selected' : ''; ?>>Recreation</option>
                    <option value="Other" <?php echo $club['category'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="description" style="font-weight:bold;">Description</label>
                <textarea id="description" name="description" class="form-control" placeholder="Enter club description..." rows="5" maxlength="2000"><?php echo htmlspecialchars($club['description']); ?></textarea>
                <small style="color:var(--text-muted);">0-2000 characters</small>
            </div>
            
            <div class="form-group">
                <label for="logo" style="font-weight:bold;">Club Logo</label>
                <?php if ($club['logo_path']): ?>
                    <div style="margin-bottom:10px; padding:10px; background:var(--border); border-radius:6px;">
                        <small style="color:var(--text-muted);">Current logo:</small>
                        <img src="../../<?php echo htmlspecialchars($club['logo_path']); ?>" style="max-width:150px; max-height:100px; margin-top:8px; border-radius:4px;" alt="Club logo">
                    </div>
                <?php endif; ?>
                <input type="file" id="logo" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                <small style="color:var(--text-muted);">JPG, PNG, WebP (Max 5MB) - Leave empty to keep current logo</small>
            </div>
            
            <div style="display:flex; gap:10px;">
                <button type="submit" name="edit_club" class="btn btn-primary" style="flex:1;">Save Changes</button>
                <a href="index.php" class="btn btn-outline" style="flex:1; text-align:center;">Cancel</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>

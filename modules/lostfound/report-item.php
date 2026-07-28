<?php
include '../../includes/config/database.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    if(empty($title) || empty($description) || empty($location)) {
        $error = "All fields are required!";
    } else {
        // Handle image upload
        $image_url = '';
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../../uploads/lostfound/";
            if(!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = time() . '.' . $file_extension;
            $target_file = $target_dir . $file_name;
            
            if(move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_url = "uploads/lostfound/" . $file_name;
            }
        }
        
        $query = "INSERT INTO lost_items (user_id, title, description, location, status, image_url) 
                  VALUES ('$user_id', '$title', '$description', '$location', '$status', '$image_url')";
        
        if(mysqli_query($conn, $query)) {
            $success = "Item reported successfully!";
        } else {
            $error = "Failed to report item!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Item - Campus Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-warning text-white">
                        <h4 class="mb-0">🔍 Report Lost or Found Item</h4>
                    </div>
                    <div class="card-body">
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                            <a href="index.php" class="btn btn-primary">View All Reports</a>
                        <?php else: ?>
                            <form method="POST" action="" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label class="form-label">Item Title</label>
                                    <input type="text" class="form-control" name="title" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="4" required></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Location</label>
                                            <input type="text" class="form-control" name="location" placeholder="e.g., Library, Cafeteria" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-control" name="status" required>
                                                <option value="lost">Lost</option>
                                                <option value="found">Found</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Image (optional)</label>
                                    <input type="file" class="form-control" name="image" accept="image/*">
                                </div>
                                
                                <button type="submit" class="btn btn-warning w-100">Report Item</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
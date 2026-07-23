<?php
include '../../includes/config/database.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$error = '';
$success = '';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $status = 'available';
    
    // Validation
    if(empty($title) || empty($description) || empty($price)) {
        $error = "All fields are required!";
    } else {
        // Handle image upload
        $image_url = '';
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../../uploads/items/";
            if(!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = time() . '.' . $file_extension;
            $target_file = $target_dir . $file_name;
            
            if(move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_url = "uploads/items/" . $file_name;
            } else {
                $error = "Image upload failed!";
            }
        }
        
        if(empty($error)) {
            $query = "INSERT INTO marketplace_items (user_id, title, description, price, category, image_url, status) 
                      VALUES ('$user_id', '$title', '$description', '$price', '$category', '$image_url', '$status')";
            
            if(mysqli_query($conn, $query)) {
                $success = "Item added successfully!";
            } else {
                $error = "Failed to add item!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Item - Campus Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">📦 Add New Item for Sale</h4>
                    </div>
                    <div class="card-body">
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                            <a href="index.php" class="btn btn-primary">View All Items</a>
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
                                            <label class="form-label">Price (Rs.)</label>
                                            <input type="number" class="form-control" name="price" step="0.01" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Category</label>
                                            <select class="form-control" name="category" required>
                                                <option value="">Select Category</option>
                                                <option value="books">Books</option>
                                                <option value="electronics">Electronics</option>
                                                <option value="furniture">Furniture</option>
                                                <option value="clothing">Clothing</option>
                                                <option value="others">Others</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Item Image</label>
                                    <input type="file" class="form-control" name="image" accept="image/*">
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">Add Item</button>
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
<?php
include '../../includes/config/database.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if(!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$item_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Check if user owns this item
$query = "SELECT * FROM marketplace_items WHERE item_id = '$item_id' AND user_id = '$user_id'";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$item = mysqli_fetch_assoc($result);
$error = '';
$success = '';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    
    if(empty($title) || empty($description) || empty($price)) {
        $error = "All fields are required!";
    } else {
        $update_query = "UPDATE marketplace_items 
                        SET title='$title', description='$description', 
                            price='$price', category='$category' 
                        WHERE item_id='$item_id'";
        
        if(mysqli_query($conn, $update_query)) {
            $success = "Item updated successfully!";
            // Refresh item data
            $result = mysqli_query($conn, $query);
            $item = mysqli_fetch_assoc($result);
        } else {
            $error = "Update failed!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item - Campus Connect</title>
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
                        <h4 class="mb-0">✏️ Edit Item</h4>
                    </div>
                    <div class="card-body">
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Item Title</label>
                                <input type="text" class="form-control" name="title" value="<?php echo $item['title']; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="4" required><?php echo $item['description']; ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Price (Rs.)</label>
                                        <input type="number" class="form-control" name="price" step="0.01" value="<?php echo $item['price']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Category</label>
                                        <select class="form-control" name="category" required>
                                            <option value="books" <?php echo ($item['category'] == 'books') ? 'selected' : ''; ?>>Books</option>
                                            <option value="electronics" <?php echo ($item['category'] == 'electronics') ? 'selected' : ''; ?>>Electronics</option>
                                            <option value="furniture" <?php echo ($item['category'] == 'furniture') ? 'selected' : ''; ?>>Furniture</option>
                                            <option value="clothing" <?php echo ($item['category'] == 'clothing') ? 'selected' : ''; ?>>Clothing</option>
                                            <option value="others" <?php echo ($item['category'] == 'others') ? 'selected' : ''; ?>>Others</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-warning w-100">Update Item</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
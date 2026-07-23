<?php
include '../../includes/config/database.php';

// Check if item ID is provided
if(!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$item_id = $_GET['id'];

// Get item details
$query = "SELECT items.*, users.name as seller_name, users.email as seller_email 
          FROM marketplace_items items 
          JOIN users ON items.user_id = users.user_id 
          WHERE items.item_id = '$item_id'";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$item = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $item['title']; ?> - Campus Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6">
                <?php if($item['image_url']): ?>
                    <img src="../../<?php echo $item['image_url']; ?>" class="img-fluid rounded shadow" alt="Item Image">
                <?php else: ?>
                    <div class="bg-light text-center rounded shadow py-5">
                        <span class="display-1">📦</span>
                        <p class="text-muted">No image available</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h2><?php echo $item['title']; ?></h2>
                        <p class="text-muted">Category: <span class="badge bg-secondary"><?php echo $item['category']; ?></span></p>
                        <hr>
                        <p><?php echo nl2br($item['description']); ?></p>
                        <hr>
                        <h3 class="text-success">Rs. <?php echo number_format($item['price'], 2); ?></h3>
                        
                        <div class="mt-4">
                            <p><strong>Seller:</strong> <?php echo $item['seller_name']; ?></p>
                            <p><strong>Email:</strong> <?php echo $item['seller_email']; ?></p>
                        </div>
                        
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <?php if($_SESSION['user_id'] == $item['user_id']): ?>
                                <div class="mt-3">
                                    <a href="edit-item.php?id=<?php echo $item['item_id']; ?>" class="btn btn-warning">Edit Item</a>
                                    <a href="delete-item.php?id=<?php echo $item['item_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete Item</a>
                                </div>
                            <?php else: ?>
                                <div class="mt-3">
                                    <button class="btn btn-success w-100" onclick="alert('Contact seller at: <?php echo $item['seller_email']; ?>')">
                                        Contact Seller
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="mt-3">
                                <a href="../auth/login.php" class="btn btn-primary w-100">Login to Contact Seller</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="index.php" class="btn btn-secondary">← Back to Marketplace</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
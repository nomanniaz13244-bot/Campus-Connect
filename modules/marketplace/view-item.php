<?php
include '../../includes/config/database.php';

// Search functionality
$search = '';
if(isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where = "WHERE title LIKE '%$search%' OR description LIKE '%$search%'";
} else {
    $where = "";
}

// Get all items
$query = "SELECT items.*, users.name as seller_name 
          FROM marketplace_items items 
          JOIN users ON items.user_id = users.user_id 
          $where 
          ORDER BY items.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace - Campus Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8">
                <h2>📚 Marketplace</h2>
                <p>Buy and sell items with fellow students</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="add-item.php" class="btn btn-primary">+ Sell Item</a>
            </div>
        </div>
        <hr>
        
        <!-- Search Bar -->
        <div class="row mb-4">
            <div class="col-md-6">
                <form method="GET" action="">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search items..." value="<?php echo $search; ?>">
                        <button type="submit" class="btn btn-outline-primary">Search</button>
                        <?php if($search): ?>
                            <a href="index.php" class="btn btn-outline-secondary">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Items Grid -->
        <div class="row">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($item = mysqli_fetch_assoc($result)): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card shadow h-100">
                            <?php if($item['image_url']): ?>
                                <img src="../../<?php echo $item['image_url']; ?>" class="card-img-top" alt="Item Image" style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-light text-center py-5">
                                    <span class="display-1">📦</span>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $item['title']; ?></h5>
                                <p class="card-text text-muted">
                                    <?php echo substr($item['description'], 0, 100); ?>...
                                </p>
                                <h5 class="text-success">Rs. <?php echo number_format($item['price'], 2); ?></h5>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-user"></i> <?php echo $item['seller_name']; ?>
                                    </small>
                                    <span class="badge bg-secondary"><?php echo $item['category']; ?></span>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="view-item.php?id=<?php echo $item['item_id']; ?>" class="btn btn-primary w-100">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <h4>No items found!</h4>
                        <p>Be the first to <a href="add-item.php">sell an item</a>.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
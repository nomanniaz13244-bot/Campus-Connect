<?php
include '../../includes/config/database.php';

// Filter by status
$status_filter = '';
if(isset($_GET['status'])) {
    $status_filter = mysqli_real_escape_string($conn, $_GET['status']);
    $where = "WHERE status = '$status_filter'";
} else {
    $where = "";
}

$query = "SELECT items.*, users.name as reporter_name 
          FROM lost_items items 
          JOIN users ON items.user_id = users.user_id 
          $where 
          ORDER BY items.reported_date DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost & Found - Campus Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8">
                <h2>🔍 Lost & Found</h2>
                <p>Report lost items or find what you've lost</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="report-item.php" class="btn btn-warning">+ Report Item</a>
            </div>
        </div>
        <hr>
        
        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="btn-group" role="group">
                    <a href="index.php" class="btn btn-outline-secondary <?php echo ($status_filter == '') ? 'active' : ''; ?>">All</a>
                    <a href="?status=lost" class="btn btn-outline-danger <?php echo ($status_filter == 'lost') ? 'active' : ''; ?>">Lost</a>
                    <a href="?status=found" class="btn btn-outline-success <?php echo ($status_filter == 'found') ? 'active' : ''; ?>">Found</a>
                </div>
            </div>
        </div>
        
        <!-- Reports List -->
        <div class="row">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($item = mysqli_fetch_assoc($result)): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card shadow">
                            <div class="row g-0">
                                <div class="col-md-4">
                                    <?php if($item['image_url']): ?>
                                        <img src="../../<?php echo $item['image_url']; ?>" class="img-fluid rounded-start" style="height: 150px; width: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-light text-center py-4">
                                            <span class="display-4">🔍</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body">
                                        <h5><?php echo $item['title']; ?></h5>
                                        <p class="text-muted"><?php echo substr($item['description'], 0, 80); ?>...</p>
                                        <div class="d-flex justify-content-between">
                                            <span class="badge bg-secondary"><?php echo $item['location']; ?></span>
                                            <span class="badge <?php echo ($item['status'] == 'lost') ? 'bg-danger' : 'bg-success'; ?>">
                                                <?php echo strtoupper($item['status']); ?>
                                            </span>
                                        </div>
                                        <small class="text-muted">
                                            Reported by: <?php echo $item['reporter_name']; ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="view-item.php?id=<?php echo $item['item_id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                <?php if($item['status'] == 'lost' && $_SESSION['user_id'] == $item['user_id']): ?>
                                    <a href="resolve.php?id=<?php echo $item['item_id']; ?>" class="btn btn-success btn-sm">Mark as Resolved</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <h4>No reports found!</h4>
                        <p><a href="report-item.php">Report a lost or found item</a></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
include '../../includes/config/database.php';

if(!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$item_id = $_GET['id'];
$query = "SELECT items.*, users.name as reporter_name, users.email as reporter_email 
          FROM lost_items items 
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
    <title>View Report - Campus Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6">
                <?php if($item['image_url']): ?>
                    <img src="../../<?php echo $item['image_url']; ?>" class="img-fluid rounded shadow">
                <?php else: ?>
                    <div class="bg-light text-center rounded shadow py-5">
                        <span class="display-1">🔍</span>
                        <p class="text-muted">No image available</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h2><?php echo $item['title']; ?></h2>
                        <span class="badge <?php echo ($item['status'] == 'lost') ? 'bg-danger' : 'bg-success'; ?>">
                            <?php echo strtoupper($item['status']); ?>
                        </span>
                        <span class="badge bg-secondary"><?php echo $item['location']; ?></span>
                        <hr>
                        <p><?php echo nl2br($item['description']); ?></p>
                        <hr>
                        <p><strong>Reported by:</strong> <?php echo $item['reporter_name']; ?></p>
                        <p><strong>Contact:</strong> <?php echo $item['reporter_email']; ?></p>
                        <p><strong>Date:</strong> <?php echo date('d M Y, h:i A', strtotime($item['reported_date'])); ?></p>
                        
                        <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $item['user_id'] && $item['status'] == 'lost'): ?>
                            <a href="resolve.php?id=<?php echo $item['item_id']; ?>" class="btn btn-success w-100">Mark as Resolved</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="index.php" class="btn btn-secondary">← Back</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
include '../../includes/config/database.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Filter by category
$category_filter = '';
$type_filter = '';

if(isset($_GET['category']) && !empty($_GET['category'])) {
    $category_filter = mysqli_real_escape_string($conn, $_GET['category']);
}

if(isset($_GET['type']) && !empty($_GET['type'])) {
    $type_filter = mysqli_real_escape_string($conn, $_GET['type']);
}

// Build query
$where = "WHERE 1=1";
if($category_filter) {
    $where .= " AND category = '$category_filter'";
}
if($type_filter) {
    $where .= " AND event_type = '$type_filter'";
}

$query = "SELECT * FROM events $where ORDER BY date ASC, time ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events & Internships - Campus Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8">
                <h2>📅 Events & Internships</h2>
                <p>Discover upcoming events and internship opportunities</p>
            </div>
            <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                <div class="col-md-4 text-end">
                    <a href="add-event.php" class="btn btn-primary">+ Add Event</a>
                </div>
            <?php endif; ?>
        </div>
        <hr>
        
        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-4">
                <form method="GET" action="">
                    <select class="form-control" name="type" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="event" <?php echo ($type_filter == 'event') ? 'selected' : ''; ?>>Events</option>
                        <option value="internship" <?php echo ($type_filter == 'internship') ? 'selected' : ''; ?>>Internships</option>
                    </select>
                </form>
            </div>
            <div class="col-md-4">
                <form method="GET" action="">
                    <select class="form-control" name="category" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <option value="Technical" <?php echo ($category_filter == 'Technical') ? 'selected' : ''; ?>>Technical</option>
                        <option value="Career" <?php echo ($category_filter == 'Career') ? 'selected' : ''; ?>>Career</option>
                        <option value="Cultural" <?php echo ($category_filter == 'Cultural') ? 'selected' : ''; ?>>Cultural</option>
                        <option value="Sports" <?php echo ($category_filter == 'Sports') ? 'selected' : ''; ?>>Sports</option>
                        <option value="Academic" <?php echo ($category_filter == 'Academic') ? 'selected' : ''; ?>>Academic</option>
                    </select>
                </form>
            </div>
            <div class="col-md-4">
                <a href="index.php" class="btn btn-outline-secondary w-100">Clear Filters</a>
            </div>
        </div>
        
        <!-- Events List -->
        <div class="row">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($event = mysqli_fetch_assoc($result)): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card shadow">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <span class="badge <?php echo ($event['event_type'] == 'internship') ? 'bg-success' : 'bg-primary'; ?>">
                                        <?php echo strtoupper($event['event_type']); ?>
                                    </span>
                                    <span class="badge bg-secondary"><?php echo $event['category']; ?></span>
                                </div>
                                <h5 class="mt-2"><?php echo $event['title']; ?></h5>
                                <p><?php echo substr($event['description'], 0, 100); ?>...</p>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">
                                            📅 <?php echo date('d M Y', strtotime($event['date'])); ?>
                                        </small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">
                                            🕐 <?php echo date('h:i A', strtotime($event['time'])); ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">📍 <?php echo $event['location']; ?></small>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="event-detail.php?id=<?php echo $event['event_id']; ?>" class="btn btn-primary w-100">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <h4>No events found!</h4>
                        <p>Check back later for upcoming events and internships.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
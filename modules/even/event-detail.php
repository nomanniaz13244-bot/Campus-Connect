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

$event_id = $_GET['id'];
$query = "SELECT * FROM events WHERE event_id = '$event_id'";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$event = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $event['title']; ?> - Campus Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <span class="badge <?php echo ($event['event_type'] == 'internship') ? 'bg-success' : 'bg-primary'; ?> p-2">
                                <?php echo strtoupper($event['event_type']); ?>
                            </span>
                            <span class="badge bg-secondary p-2"><?php echo $event['category']; ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <h2><?php echo $event['title']; ?></h2>
                        <hr>
                        <p><?php echo nl2br($event['description']); ?></p>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>📅 Date:</strong> <?php echo date('l, d F Y', strtotime($event['date'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>🕐 Time:</strong> <?php echo date('h:i A', strtotime($event['time'])); ?></p>
                            </div>
                        </div>
                        <p><strong>📍 Location:</strong> <?php echo $event['location']; ?></p>
                        
                        <?php if($event['event_type'] == 'internship'): ?>
                            <div class="alert alert-success mt-3">
                                <h5>💼 Internship Opportunity</h5>
                                <p>Apply before the deadline to secure your position!</p>
                                <button class="btn btn-success" onclick="alert('Application submitted successfully!')">Apply Now</button>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mt-3">
                                <h5>🎉 Register for Event</h5>
                                <p>Don't miss this opportunity to learn and network!</p>
                                <button class="btn btn-primary" onclick="alert('Registration successful!')">Register Now</button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                            <div class="mt-3">
                                <a href="edit-event.php?id=<?php echo $event['event_id']; ?>" class="btn btn-warning">Edit</a>
                                <a href="delete-event.php?id=<?php echo $event['event_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="index.php" class="btn btn-secondary">← Back to Events</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
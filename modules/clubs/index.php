<?php
include '../../includes/config/database.php';

// Get all clubs
$query = "SELECT * FROM clubs ORDER BY name ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clubs & Societies - Campus Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8">
                <h2>🏛️ Clubs & Societies</h2>
                <p>Discover and join campus clubs</p>
            </div>
            <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                <div class="col-md-4 text-end">
                    <a href="add-club.php" class="btn btn-primary">+ Add Club</a>
                </div>
            <?php endif; ?>
        </div>
        <hr>
        
        <div class="row">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($club = mysqli_fetch_assoc($result)): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card shadow h-100">
                            <div class="card-body text-center">
                                <?php if($club['logo_url']): ?>
                                    <img src="../../<?php echo $club['logo_url']; ?>" class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                                        <span class="display-4">🏛️</span>
                                    </div>
                                <?php endif; ?>
                                <h5><?php echo $club['name']; ?></h5>
                                <p class="text-muted"><?php echo substr($club['description'], 0, 80); ?>...</p>
                                <span class="badge bg-secondary"><?php echo $club['category']; ?></span>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="club-detail.php?id=<?php echo $club['club_id']; ?>" class="btn btn-primary w-100">View Club</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <h4>No clubs found!</h4>
                        <p>Check back later for club listings.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
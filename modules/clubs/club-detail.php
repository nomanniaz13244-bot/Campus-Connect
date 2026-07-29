<?php
include '../../includes/config/database.php';

if(!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$club_id = $_GET['id'];

// Get club details
$query = "SELECT * FROM clubs WHERE club_id = '$club_id'";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$club = mysqli_fetch_assoc($result);

// Check if user has already joined
$is_member = false;
$join_status = '';
if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $check_query = "SELECT * FROM club_members WHERE club_id = '$club_id' AND user_id = '$user_id'";
    $check_result = mysqli_query($conn, $check_query);
    if(mysqli_num_rows($check_result) > 0) {
        $is_member = true;
        $member = mysqli_fetch_assoc($check_result);
        $join_status = $member['status'];
    }
}

// Handle join request
if(isset($_POST['join_club'])) {
    if(!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $query = "INSERT INTO club_members (club_id, user_id, status) VALUES ('$club_id', '$user_id', 'pending')";
    if(mysqli_query($conn, $query)) {
        $message = "Join request sent successfully!";
        header("Location: club-detail.php?id=$club_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $club['name']; ?> - Campus Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <?php if($club['logo_url']): ?>
                            <img src="../../<?php echo $club['logo_url']; ?>" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 150px; height: 150px;">
                                <span class="display-1">🏛️</span>
                            </div>
                        <?php endif; ?>
                        <h3 class="mt-3"><?php echo $club['name']; ?></h3>
                        <span class="badge bg-secondary"><?php echo $club['category']; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body">
                        <h4>About</h4>
                        <p><?php echo nl2br($club['description']); ?></p>
                        <hr>
                        
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <?php if($is_member): ?>
                                <div class="alert alert-success">
                                    <?php if($join_status == 'approved'): ?>
                                        ✅ You are a member of this club!
                                    <?php else: ?>
                                        ⏳ Your join request is pending approval.
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <form method="POST" action="">
                                    <button type="submit" name="join_club" class="btn btn-success w-100">Join This Club</button>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <a href="../auth/login.php">Login</a> to join this club.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
include '../../includes/config/database.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if(isset($_GET['id'])) {
    $item_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];
    
    $query = "UPDATE lost_items SET status = 'found' WHERE item_id = '$item_id' AND user_id = '$user_id'";
    mysqli_query($conn, $query);
}

header("Location: index.php");
exit();
?>
<?php
include '../../includes/config/database.php';

if(!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$note_id = $_GET['id'];
$query = "SELECT * FROM notes WHERE note_id = '$note_id'";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) > 0) {
    $note = mysqli_fetch_assoc($result);
    $file_path = "../../" . $note['file_url'];
    
    if(file_exists($file_path)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        readfile($file_path);
        exit();
    } else {
        echo "File not found!";
    }
} else {
    header("Location: index.php");
}
?>
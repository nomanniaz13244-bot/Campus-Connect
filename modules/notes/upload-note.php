<?php
include '../../includes/config/database.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);
    
    if(empty($title) || empty($description) || empty($subject) || empty($semester)) {
        $error = "All fields are required!";
    } else {
        // Handle file upload
        $file_url = '';
        if(isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $target_dir = "../../uploads/notes/";
            if(!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $file_name = time() . '.' . $file_extension;
            $target_file = $target_dir . $file_name;
            
            // Allowed file types
            $allowed = array('pdf', 'doc', 'docx', 'txt');
            if(in_array(strtolower($file_extension), $allowed)) {
                if(move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
                    $file_url = "uploads/notes/" . $file_name;
                } else {
                    $error = "File upload failed!";
                }
            } else {
                $error = "Only PDF, DOC, DOCX, TXT files are allowed!";
            }
        } else {
            $error = "Please select a file to upload!";
        }
        
        if(empty($error)) {
            $query = "INSERT INTO notes (user_id, title, description, subject, semester, file_url) 
                      VALUES ('$user_id', '$title', '$description', '$subject', '$semester', '$file_url')";
            
            if(mysqli_query($conn, $query)) {
                $success = "Note uploaded successfully!";
            } else {
                $error = "Failed to upload note!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Note - Campus Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">📤 Upload Note</h4>
                    </div>
                    <div class="card-body">
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                            <a href="index.php" class="btn btn-primary">View All Notes</a>
                        <?php else: ?>
                            <form method="POST" action="" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" class="form-control" name="title" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="3" required></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Subject</label>
                                            <input type="text" class="form-control" name="subject" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Semester</label>
                                            <select class="form-control" name="semester" required>
                                                <option value="">Select</option>
                                                <option value="1">Semester 1</option>
                                                <option value="2">Semester 2</option>
                                                <option value="3">Semester 3</option>
                                                <option value="4">Semester 4</option>
                                                <option value="5">Semester 5</option>
                                                <option value="6">Semester 6</option>
                                                <option value="7">Semester 7</option>
                                                <option value="8">Semester 8</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">File (PDF, DOC, DOCX, TXT)</label>
                                    <input type="file" class="form-control" name="file" required>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">Upload Note</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
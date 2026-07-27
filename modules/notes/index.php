<?php
include '../../includes/config/database.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Search functionality
$search = '';
$semester = '';
if(isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}
if(isset($_GET['semester'])) {
    $semester = mysqli_real_escape_string($conn, $_GET['semester']);
}

// Build query
$where = "";
if($search) {
    $where .= " AND (title LIKE '%$search%' OR subject LIKE '%$search%')";
}
if($semester) {
    $where .= " AND semester = '$semester'";
}

$query = "SELECT notes.*, users.name as uploader_name 
          FROM notes 
          JOIN users ON notes.user_id = users.user_id 
          WHERE 1=1 $where
          ORDER BY notes.upload_date DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes - Campus Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8">
                <h2>📝 Academic Notes</h2>
                <p>Share and download study materials</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="upload-note.php" class="btn btn-primary">+ Upload Note</a>
            </div>
        </div>
        <hr>
        
        <!-- Search Filters -->
        <div class="row mb-4">
            <div class="col-md-8">
                <form method="GET" action="">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search notes..." value="<?php echo $search; ?>">
                        <button type="submit" class="btn btn-outline-primary">Search</button>
                        <?php if($search): ?>
                            <a href="index.php" class="btn btn-outline-secondary">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            <div class="col-md-4">
                <form method="GET" action="">
                    <select class="form-control" name="semester" onchange="this.form.submit()">
                        <option value="">All Semesters</option>
                        <option value="1" <?php echo ($semester == '1') ? 'selected' : ''; ?>>Semester 1</option>
                        <option value="2" <?php echo ($semester == '2') ? 'selected' : ''; ?>>Semester 2</option>
                        <option value="3" <?php echo ($semester == '3') ? 'selected' : ''; ?>>Semester 3</option>
                        <option value="4" <?php echo ($semester == '4') ? 'selected' : ''; ?>>Semester 4</option>
                        <option value="5" <?php echo ($semester == '5') ? 'selected' : ''; ?>>Semester 5</option>
                        <option value="6" <?php echo ($semester == '6') ? 'selected' : ''; ?>>Semester 6</option>
                        <option value="7" <?php echo ($semester == '7') ? 'selected' : ''; ?>>Semester 7</option>
                        <option value="8" <?php echo ($semester == '8') ? 'selected' : ''; ?>>Semester 8</option>
                    </select>
                </form>
            </div>
        </div>
        
        <!-- Notes List -->
        <div class="row">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($note = mysqli_fetch_assoc($result)): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card shadow">
                            <div class="card-body">
                                <h5><?php echo $note['title']; ?></h5>
                                <p class="text-muted">
                                    <strong>Subject:</strong> <?php echo $note['subject']; ?> | 
                                    <strong>Semester:</strong> <?php echo $note['semester']; ?>
                                </p>
                                <p><?php echo substr($note['description'], 0, 100); ?>...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        Uploaded by: <?php echo $note['uploader_name']; ?>
                                    </small>
                                    <small class="text-muted">
                                        <?php echo date('d M Y', strtotime($note['upload_date'])); ?>
                                    </small>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="download.php?id=<?php echo $note['note_id']; ?>" class="btn btn-success btn-sm">⬇ Download</a>
                                <?php if($_SESSION['user_id'] == $note['user_id']): ?>
                                    <a href="delete-note.php?id=<?php echo $note['note_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <h4>No notes found!</h4>
                        <p>Be the first to <a href="upload-note.php">upload a note</a>.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
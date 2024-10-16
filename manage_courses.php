<?php
include 'db_connect.php';
session_start();

// Ensure only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Handle adding a course
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_name = $_POST['course_name'];
    $conn->query("INSERT INTO courses (course_name) VALUES ('$course_name')");
}

// Handle delete
if (isset($_GET['delete'])) {
    $course_id = $_GET['delete'];
    $conn->query("DELETE FROM courses WHERE course_id = '$course_id'");
}

// Fetch all courses
$courses = $conn->query("SELECT * FROM courses");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Manage Courses</h2>
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <a class="navbar-brand" href="admin_dashboard.php">Dashboard</a>
            <div class="navbar-nav">
                <a class="nav-item nav-link btn btn-danger text-white" href="logout.php">Logout</a>
            </div>
        </nav>   
        <h3>Add New Course</h3>
        <form method="post" class="mb-4">
            <label>Course Name:</label>
            <input type="text" name="course_name" required class="form-control"><br>
            <input type="submit" value="Add Course" class="btn btn-primary">
        </form>

        <h3>Course List</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $courses->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['course_name'] ?></td>
                    <td><a href="?delete=<?= $row['course_id'] ?>" class="btn btn-danger">Delete</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

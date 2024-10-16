<?php
session_start();
include 'db_connect.php';

// Ensure only admin can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Handle adding a new faculty
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_faculty'])) {
    $faculty_name = $_POST['faculty_name'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $course_id = $_POST['course_id'];

    // Insert into users table
    $conn->query("INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'faculty')");
    $user_id = $conn->insert_id; // Get the newly created user_id

    // Insert into faculty table
    $conn->query("INSERT INTO faculty (user_id, faculty_name, course_id) VALUES ('$user_id', '$faculty_name', '$course_id')");
}

// Handle updating faculty
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_faculty'])) {
    $faculty_id = $_POST['faculty_id'];
    $faculty_name = $_POST['faculty_name'];
    $username = $_POST['username'];
    $course_id = $_POST['course_id'];

    // Update faculty name and course
    $conn->query("UPDATE faculty SET faculty_name = '$faculty_name', course_id = '$course_id' WHERE faculty_id = '$faculty_id'");

    // Update username in users table
    $user_id_query = $conn->query("SELECT user_id FROM faculty WHERE faculty_id = '$faculty_id'");
    $user_id = $user_id_query->fetch_assoc()['user_id'];
    $conn->query("UPDATE users SET username = '$username' WHERE user_id = '$user_id'");
}

// Handle deleting a faculty
if (isset($_GET['delete'])) {
    $faculty_id = $_GET['delete'];
    // First, get the user_id linked to the faculty
    $user_id_result = $conn->query("SELECT user_id FROM faculty WHERE faculty_id = '$faculty_id'");
    if ($user_id_result->num_rows > 0) {
        $user_id = $user_id_result->fetch_assoc()['user_id'];
        // Delete from faculty and users
        $conn->query("DELETE FROM faculty WHERE faculty_id = '$faculty_id'");
        $conn->query("DELETE FROM users WHERE user_id = '$user_id'");
    }
}

// Handle search functionality
$search_query = '';
if (isset($_POST['search'])) {
    $search_query = $_POST['search_query'];
}

// Fetch all faculty members with optional search
$faculty_query = "
    SELECT f.faculty_id, f.faculty_name, u.username, f.course_id
    FROM faculty f
    JOIN users u ON f.user_id = u.user_id
    WHERE f.faculty_name LIKE '%$search_query%' OR u.username LIKE '%$search_query%'
";
$faculty_list = $conn->query($faculty_query);




$courses = $conn->query("SELECT * FROM courses");
$course_options = [];
while ($course = $courses->fetch_assoc()) {
    $course_options[] = $course;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Faculty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Manage Faculty</h2>
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <a class="navbar-brand" href="admin_dashboard.php">Dashboard</a>
            <div class="navbar-nav">
                <a class="nav-item nav-link btn btn-danger text-white" href="logout.php">Logout</a>
            </div>
        </nav>   
        <!-- Add New Faculty Form -->
        <h3 class="mt-4">Add New Faculty</h3>
        <form method="post" class="mb-4">
            <input type="hidden" name="add_faculty" value="1">
            <div class="mb-3">
                <label for="faculty_name" class="form-label">Faculty Name:</label>
                <input type="text" name="faculty_name" required class="form-control" id="faculty_name">
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username:</label>
                <input type="text" name="username" required class="form-control" id="username">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" name="password" required class="form-control" id="password">
            </div>
            <div class="mb-3">
                <label for="course_id" class="form-label">Course:</label>
                <select name="course_id" class="form-select" id="course_id">
                    <?php foreach ($course_options as $course): ?>
                        <option value="<?= $course['course_id'] ?>"><?= $course['course_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Faculty</button>
        </form>

        <!-- Search Form -->
        <h3>Search Faculty</h3>
        <form method="post" class="mb-4">
            <div class="input-group">
                <input type="text" name="search_query" placeholder="Search by name or username" class="form-control" value="<?= htmlspecialchars($search_query) ?>">
                <button type="submit" name="search" class="btn btn-secondary">Search</button>
            </div>
        </form>

        <!-- Faculty List -->
        <h3>Faculty List</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Course</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($faculty_list && $faculty_list->num_rows > 0): ?>
                    <?php while ($row = $faculty_list->fetch_assoc()): ?>
                    <tr>
                        <form method="post" class="d-inline-block">
                            <td>
                                <input type="hidden" name="faculty_id" value="<?= $row['faculty_id'] ?>">
                                <input type="text" name="faculty_name" value="<?= htmlspecialchars($row['faculty_name']) ?>" required class="form-control" style="width: 200px;">
                            </td>
                            <td>
                                <input type="text" name="username" value="<?= htmlspecialchars($row['username']) ?>" required class="form-control" style="width: 150px;">
                            </td>
                            <td>
                                <select name="course_id" class="form-select" style="width: 150px;">
                                    <?php foreach ($course_options as $course): ?>
                                        <option value="<?= $course['course_id'] ?>" <?= $course['course_id'] == $row['course_id'] ? 'selected' : '' ?>>
                                            <?= $course['course_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <button type="submit" name="update_faculty" class="btn btn-primary">Update</button>
                                <a href="?delete=<?= $row['faculty_id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this faculty?');">Delete</a>
                            </td>
                        </form>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No faculty members found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

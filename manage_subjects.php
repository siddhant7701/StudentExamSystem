<?php
include 'db_connect.php';
session_start();

// Ensure only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Handle subject assignment to student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_subject'])) {
    $student_id = $_POST['student_id'];
    $subject_id = $_POST['subject_id'];

    // Assign subject
    $conn->query("INSERT INTO student_subjects (student_id, subject_id) VALUES ('$student_id', '$subject_id')");
}

// Handle removing subject from student
if (isset($_GET['remove_subject'])) {
    $student_subject_id = $_GET['remove_subject'];
    $conn->query("DELETE FROM student_subjects WHERE id = '$student_subject_id'");
}

// Fetch all students
$students = $conn->query("SELECT * FROM students");

// Fetch all subjects
$subjects = $conn->query("SELECT * FROM subjects");

// Fetch all student-subject relations
$student_subjects = $conn->query("SELECT ss.id, s.student_name, sub.subject_name 
                                  FROM student_subjects ss 
                                  JOIN students s ON ss.student_id = s.student_id
                                  JOIN subjects sub ON ss.subject_id = sub.subject_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Manage Student Subjects</h2>
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <a class="navbar-brand" href="admin_dashboard.php">Dashboard</a>
            <div class="navbar-nav">
                <a class="nav-item nav-link btn btn-danger text-white" href="logout.php">Logout</a>
            </div>
        </nav>        
        <h3>Assign Subject to Student</h3>
        <form method="post">
            <div class="mb-3">
                <label for="student_id" class="form-label">Student:</label>
                <select name="student_id" class="form-select" id="student_id">
                    <?php while ($student = $students->fetch_assoc()): ?>
                        <option value="<?= $student['student_id'] ?>"><?= $student['student_name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="subject_id" class="form-label">Subject:</label>
                <select name="subject_id" class="form-select" id="subject_id">
                    <?php while ($subject = $subjects->fetch_assoc()): ?>
                        <option value="<?= $subject['subject_id'] ?>"><?= $subject['subject_name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" name="assign_subject" class="btn btn-primary">Assign Subject</button>
        </form>

        <h3 class="mt-5">Student-Subject Assignments</h3>
        <table class="table table-striped mt-3">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Subject Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $student_subjects->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['student_name'] ?></td>
                    <td><?= $row['subject_name'] ?></td>
                    <td><a href="?remove_subject=<?= $row['id'] ?>" class="btn btn-danger">Remove</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();
include 'db_connect.php';

// Ensure only admins and faculty can access
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'faculty')) {
    header('Location: login.php');
    exit();
}

// Set the correct dashboard link based on the user's role
$dashboard_link = ($_SESSION['role'] == 'admin') ? 'admin_dashboard.php' : 'faculty_dashboard.php';

// Handle deleting student
if (isset($_GET['delete'])) {
    $student_id = $_GET['delete'];
    $conn->query("DELETE FROM students WHERE student_id = '$student_id'");
}

// Handle updating student data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_student'])) {
    $student_id = $_POST['student_id'];
    $student_name = $_POST['student_name'];
    $course_id = $_POST['course_id'];
    $can_view_results = isset($_POST['can_view_results']) ? 1 : 0;
    $new_password = $_POST['new_password'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update student details
        $update_query = "UPDATE students SET student_name = ?, course_id = ?, can_view_results = ? WHERE student_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("siii", $student_name, $course_id, $can_view_results, $student_id);
        $stmt->execute();

        // Update password if a new one is provided
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_password_query = "UPDATE users u JOIN students s ON u.user_id = s.user_id SET u.password = ? WHERE s.student_id = ?";
            $stmt_password = $conn->prepare($update_password_query);
            $stmt_password->bind_param("si", $hashed_password, $student_id);
            $stmt_password->execute();
        }

        // Commit transaction
        $conn->commit();
        $_SESSION['message'] = "<div class='alert alert-success'>Student details updated successfully!</div>";
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['message'] = "<div class='alert alert-danger'>Error updating student: " . $e->getMessage() . "</div>";
    }

    header('Location: manage_students.php');
    exit();
}

// Handle adding a new student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    $student_name = $_POST['student_name'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $course_id = $_POST['course_id'];

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'student')");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $user_id = $stmt->insert_id;
        
        $stmt = $conn->prepare("INSERT INTO students (user_id, student_name, course_id) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $user_id, $student_name, $course_id);
        $stmt->execute();
        
        $conn->commit();
        $_SESSION['message'] = "<div class='alert alert-success'>Student added successfully!</div>";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message'] = "<div class='alert alert-danger'>Error adding student: " . $e->getMessage() . "</div>";
    }

    header('Location: manage_students.php');
    exit();
}

// Fetch all students with their details and permissions
$students = $conn->query("SELECT s.student_id, s.student_name, u.username, c.course_name, s.course_id, s.can_view_results 
                          FROM students s 
                          JOIN users u ON s.user_id = u.user_id
                          JOIN courses c ON s.course_id = c.course_id");

$courses = $conn->query("SELECT * FROM courses"); // Fetch all courses for dropdown
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Manage Students</h2>
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <a class="navbar-brand" href="<?= $dashboard_link ?>">Dashboard</a>
            <div class="navbar-nav">
                <a class="nav-item nav-link btn btn-danger text-white" href="logout.php">Logout</a>
            </div>
        </nav>
        <!-- Display session messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <?= $_SESSION['message'] ?>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <!-- Student List -->
        <h3>Student List</h3>
        <div class="mb-3">
            <input type="text" id="search" class="form-control" placeholder="Search by name or username...">
        </div>
        <table class="table table-striped" id="studentTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Course</th>
                    <th>Can View Results</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $students->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['student_name']) ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['course_name']) ?></td>
                    <td><?= $row['can_view_results'] ? 'Yes' : 'No' ?></td>
                    <td>
                        <button class="btn btn-primary btn-edit" data-bs-toggle="modal" data-bs-target="#editStudentModal" 
                                data-id="<?= $row['student_id'] ?>" 
                                data-name="<?= htmlspecialchars($row['student_name']) ?>" 
                                data-course="<?= $row['course_id'] ?>"
                                data-canviewresults="<?= $row['can_view_results'] ?>">
                            Edit
                        </button>
                        <a href="?delete=<?= $row['student_id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Add Student Modal -->
        <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addStudentModalLabel">Add New Student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="student_name" class="form-label">Student Name</label>
                                <input type="text" class="form-control" id="student_name" name="student_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="course_id" class="form-label">Course</label>
                                <select class="form-select" id="course_id" name="course_id" required>
                                    <?php 
                                    $courses->data_seek(0);
                                    while ($course = $courses->fetch_assoc()): 
                                    ?>
                                        <option value="<?= $course['course_id'] ?>"><?= htmlspecialchars($course['course_name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <button type="submit" name="add_student" class="btn btn-success">Add Student</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Student Modal -->
        <div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editStudentModalLabel">Edit Student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="editStudentForm">
                            <input type="hidden" id="edit_student_id" name="student_id">
                            <div class="mb-3">
                                <label for="edit_student_name" class="form-label">Student Name</label>
                                <input type="text" class="form-control" id="edit_student_name" name="student_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_course_id" class="form-label">Course</label>
                                <select class="form-select" id="edit_course_id" name="course_id" required>
                                    <?php 
                                    $courses->data_seek(0);
                                    while ($course = $courses->fetch_assoc()): 
                                    ?>
                                        <option value="<?= $course['course_id'] ?>"><?= htmlspecialchars($course['course_name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="edit_can_view_results" name="can_view_results">
                                <label class="form-check-label" for="edit_can_view_results">Can View Results</label>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password (leave blank to keep current)</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>
                            <button type="submit" name="update_student" class="btn btn-primary">Update Student</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addStudentModal">Add New Student</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelector('#search').addEventListener('input', function() {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('#studentTable tbody tr');
            rows.forEach(row => {
                const name = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                const username = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                if (name.includes(searchText) || username.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function() {
                const studentId = this.getAttribute('data-id');
                const studentName = this.getAttribute('data-name');
                const courseId = this.getAttribute('data-course');
                const canViewResults = this.getAttribute('data-canviewresults') === '1';

                document.querySelector('#edit_student_id').value = studentId;
                document.querySelector('#edit_student_name').value = studentName;
                document.querySelector('#edit_course_id').value = courseId;
                document.querySelector('#edit_can_view_results').checked = canViewResults;
            });
        });
    </script>
</body>
</html>
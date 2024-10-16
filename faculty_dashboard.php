<?php
session_start();
include 'db_connect.php';

// Ensure only faculty can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'faculty') {
    header('Location: login.php');
    exit();
}

$faculty_id = $_SESSION['user_id'];

// Fetch faculty information
$faculty_query = "
    SELECT f.faculty_name, u.username 
    FROM faculty f 
    JOIN users u ON f.user_id = u.user_id 
    WHERE u.user_id = '$faculty_id'
";
$faculty_result = $conn->query($faculty_query);
$faculty_info = $faculty_result ? $faculty_result->fetch_assoc() : null;

// Fetch statistics
$student_count_query = "
    SELECT COUNT(DISTINCT s.student_id) AS total
    FROM students s
    JOIN courses c ON s.course_id = c.course_id
    WHERE c.faculty_id = (
        SELECT faculty_id FROM faculty WHERE user_id = '$faculty_id'
    )
";
$student_count_result = $conn->query($student_count_query);
$student_count = $student_count_result ? $student_count_result->fetch_assoc()['total'] : 0;

$quiz_count_query = "
    SELECT COUNT(*) AS total
    FROM quizzes
    WHERE faculty_id = (
        SELECT faculty_id FROM faculty WHERE user_id = '$faculty_id'
    )
";
$quiz_count_result = $conn->query($quiz_count_query);
$quiz_count = $quiz_count_result ? $quiz_count_result->fetch_assoc()['total'] : 0;

// Fetch students and quizzes
$students_query = "
    SELECT s.student_name, c.course_name 
    FROM students s 
    JOIN courses c ON s.course_id = c.course_id 
    WHERE c.faculty_id = (
        SELECT faculty_id FROM faculty WHERE user_id = '$faculty_id'
    )
";
$students = $conn->query($students_query);

$quizzes = $conn->query("SELECT quiz_title, quiz_id FROM quizzes WHERE faculty_id = (
    SELECT faculty_id FROM faculty WHERE user_id = '$faculty_id'
)");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .navbar {
            background-color: #007bff !important;
        }

        .navbar-brand,
        .nav-link {
            color: white !important;
        }

        .card {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .faculty-info {
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="fas fa-chalkboard-teacher"></i> Faculty Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="manage_students.php"><i class="fas fa-users"></i> View Students</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="create_quiz.php"><i class="fas fa-plus"></i> Create Quiz</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_quizzes.php"><i class="fas fa-tasks"></i> Manage Quizzes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_reports.php"><i class="fas fa-chart-line"></i> View Reports</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile_settings.php"><i class="fas fa-user"></i> Profile</a>
                    </li>
                </ul>
                <span class="navbar-text faculty-info me-2">
                    <i class="fas fa-user"></i> <?= htmlspecialchars($faculty_info['faculty_name']) ?> (<?= htmlspecialchars($faculty_info['username']) ?>)
                </span>
                <a class="btn btn-outline-light" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card text-center">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-users"></i> Students</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><strong><?= $student_count ?></strong> enrolled students</p>
                        <a href="manage_students.php" class="btn btn-primary"><i class="fas fa-eye"></i> View Students</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card text-center">
                    <div class="card-header bg-warning text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-book"></i> Quizzes</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><strong><?= $quiz_count ?></strong> quizzes created</p>
                        <a href="manage_quizzes.php" class="btn btn-warning"><i class="fas fa-cog"></i> Manage Quizzes</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card text-center">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-chart-pie"></i> Reports</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">View quiz reports and statistics</p>
                        <a href="view_reports.php" class="btn btn-success"><i class="fas fa-eye"></i> View Reports</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Students List -->
        <div class="mt-5">
            <h3>Your Students</h3>
            <?php if ($students && $students->num_rows > 0): ?>
                <ul class="list-group">
                    <?php while ($student = $students->fetch_assoc()): ?>
                        <li class="list-group-item">
                            <?= htmlspecialchars($student['student_name']) ?> (Course: <?= htmlspecialchars($student['course_name']) ?>)
                            <button class="btn btn-sm btn-info float-end" data-bs-toggle="modal" data-bs-target="#studentModal" data-student="<?= htmlspecialchars($student['student_name']) ?>">Details</button>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No students assigned to you.</p>
            <?php endif; ?>
        </div>

        <!-- Quizzes List -->
        <div class="mt-5">
            <h3>Your Quizzes</h3>
            <?php if ($quizzes && $quizzes->num_rows > 0): ?>
                <ul class="list-group">
                    <?php while ($quiz = $quizzes->fetch_assoc()): ?>
                        <li class="list-group-item">
                            <?= htmlspecialchars($quiz['quiz_title']) ?>
                            <a href="manage_quizzes.php?quiz_id=<?= $quiz['quiz_id'] ?>" class="btn btn-sm btn-secondary float-end">Edit</a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No quizzes created by you.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

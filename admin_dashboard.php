<?php
session_start();
include 'db_connect.php';

// Ensure only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Fetch data for statistics
$student_count = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
$faculty_count = $conn->query("SELECT COUNT(*) as total FROM faculty")->fetch_assoc()['total'];
$subject_count = $conn->query("SELECT COUNT(*) as total FROM subjects")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #4a148c !important;
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .card {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease-in-out;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            font-weight: bold;
        }
        .admin-info {
            font-size: 0.9rem;
        }
        .btn-custom {
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            transition-duration: 0.4s;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn-custom:hover {
            opacity: 0.8;
        }
        .btn-purple { background-color: #6a1b9a; }
        .btn-teal { background-color: #00796b; }
        .btn-orange { background-color: #e65100; }
        .btn-pink { background-color: #ad1457; }
        .btn-blue { background-color: #0277bd; }
        .btn-green { background-color: #2e7d32; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="fas fa-chart-line"></i> Admin Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="manage_courses.php"><i class="fas fa-book"></i> Manage Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_subjects.php"><i class="fas fa-book-open"></i> Manage Subjects</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_faculty.php"><i class="fas fa-users"></i> Manage Faculty</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_quizzes.php"><i class="fas fa-question-circle"></i> Manage Quizzes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_reports.php"><i class="fas fa-file-alt"></i> View Reports</a>
                    </li>
                </ul>
                <span class="navbar-text admin-info me-2">
                    <i class="fas fa-user-shield"></i> Admin User
                </span>
                <a class="btn btn-outline-light" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header bg-purple text-dark">
                        <h5 class="card-title mb-0"><i class="fas fa-user-graduate"></i> Students</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><strong><?= $student_count ?></strong> students enrolled</p>
                        <a href="manage_students.php" class="btn btn-custom btn-purple"><i class="fas fa-users"></i> Manage Students</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header bg-teal text-dark">
                        <h5 class="card-title mb-0"><i class="fas fa-chalkboard-teacher"></i> Faculty</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><strong><?= $faculty_count ?></strong> faculty members</p>
                        <a href="manage_faculty.php" class="btn btn-custom btn-teal"><i class="fas fa-user-tie"></i> Manage Faculty</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header bg-orange text-dark">
                        <h5 class="card-title mb-0"><i class="fas fa-book"></i> Subjects</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><strong><?= $subject_count ?></strong> subjects available</p>
                        <a href="manage_subjects.php" class="btn btn-custom btn-orange"><i class="fas fa-list"></i> Manage Subjects</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header bg-pink text-dark">
                        <h5 class="card-title mb-0"><i class="fas fa-graduation-cap"></i> Courses</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Manage all courses</p>
                        <a href="manage_courses.php" class="btn btn-custom btn-pink"><i class="fas fa-cog"></i> Manage Courses</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header bg-blue text-dark">
                        <h5 class="card-title mb-0"><i class="fas fa-question-circle"></i> Quizzes</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Oversee all quizzes</p>
                        <a href="manage_quizzes.php" class="btn btn-custom btn-blue"><i class="fas fa-tasks"></i> Manage Quizzes</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header bg-green text-dark">
                        <h5 class="card-title mb-0"><i class="fas fa-chart-bar"></i> Reports</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">View system reports</p>
                        <a href="view_reports.php" class="btn btn-custom btn-green"><i class="fas fa-file-alt"></i> View Reports</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
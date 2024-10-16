<?php
session_start();
include 'db_connect.php';

// Ensure only students can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header('Location: login.php');
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch student details
$student_query = "SELECT s.student_name, u.username 
                  FROM students s 
                  JOIN users u ON s.user_id = u.user_id 
                  WHERE s.user_id = ?";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student_result = $stmt->get_result();
$student_data = $student_result->fetch_assoc();

// Fetch available subjects
$subjects = $conn->query("SELECT * FROM subjects");

// Fetch quizzes if a subject is selected
$quizzes = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['subject_id'])) {
    $subject_id = $_POST['subject_id'];
    $quizzes = $conn->query("SELECT * FROM quizzes WHERE subject_id = '$subject_id'");
}

// Fetch student's quiz scores
$scores_query = "
    SELECT r.score, q.quiz_title 
    FROM results r 
    JOIN quizzes q ON r.quiz_id = q.quiz_id 
    WHERE r.student_id = ?";
$stmt = $conn->prepare($scores_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$scores = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background-color: #007bff !important; }
        .navbar-brand, .nav-link { color: white !important; }
        .card { box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); }
        .student-info { font-size: 0.9rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="fas fa-graduation-cap"></i> Student Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="view_scores.php"><i class="fas fa-chart-bar"></i> View Scores</a>
                    </li>
                </ul>
                <span class="navbar-text student-info me-2">
                    <i class="fas fa-user"></i> <?= htmlspecialchars($student_data['student_name']) ?> (<?= htmlspecialchars($student_data['username']) ?>)
                </span>
                <a class="btn btn-outline-light" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-book"></i> Select Subject to View Quizzes</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="student_dashboard.php">
                            <div class="mb-3">
                                <label for="subject_id" class="form-label">Subject:</label>
                                <select name="subject_id" class="form-select" id="subject_id">
                                    <?php while ($subject = $subjects->fetch_assoc()): ?>
                                        <option value="<?= $subject['subject_id'] ?>"><?= htmlspecialchars($subject['subject_name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Show Quizzes</button>
                        </form>
                    </div>
                </div>

                <?php if ($quizzes && $quizzes->num_rows > 0): ?>
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0"><i class="fas fa-clipboard-list"></i> Available Quizzes</h5>
                        </div>
                        <ul class="list-group list-group-flush">
                            <?php while ($quiz = $quizzes->fetch_assoc()): ?>
                                <li class="list-group-item">
                                    <a href="take_quiz.php?quiz_id=<?= $quiz['quiz_id'] ?>" class="text-decoration-none">
                                        <i class="fas fa-pencil-alt"></i> <?= htmlspecialchars($quiz['quiz_title']) ?>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                <?php elseif ($quizzes): ?>
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i> No quizzes available for this subject.
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-chart-line"></i> Your Quiz Scores</h5>
                    </div>
                    <div class="card-body">
                            <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="view_scores.php" type="submit" class="btn btn-primary"><i class="fas fa-chart-bar"></i> Show Scores</a>

                                    </li>
                            </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>

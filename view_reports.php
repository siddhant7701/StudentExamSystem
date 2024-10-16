<?php
session_start();
include 'db_connect.php';

// Ensure only admin and faculty can access
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'faculty')) {
    header('Location: login.php');
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Fetch all quizzes along with detailed data about the students who took the quizzes
if ($role == 'admin') {
    // Admin can view reports for all quizzes
    $quiz_reports_query = "
        SELECT q.quiz_title, s.student_name, r.score
        FROM quizzes q
        LEFT JOIN results r ON q.quiz_id = r.quiz_id
        LEFT JOIN students s ON r.student_id = s.student_id
        ORDER BY q.quiz_id, s.student_name
    ";
} else {
    // Faculty can view reports only for their quizzes
    $quiz_reports_query = "
        SELECT q.quiz_title, s.student_name, r.score
        FROM quizzes q
        LEFT JOIN results r ON q.quiz_id = r.quiz_id
        LEFT JOIN students s ON r.student_id = s.student_id
        WHERE q.faculty_id = (
            SELECT faculty_id FROM faculty WHERE user_id = ?
        )
        ORDER BY q.quiz_id, s.student_name
    ";
}

$stmt = $conn->prepare($quiz_reports_query);
if ($role != 'admin') {
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$quiz_reports = $stmt->get_result();

// Handle Excel file download
if (isset($_POST['download_excel'])) {
    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=quiz_reports.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "Quiz Title\tStudent Name\tScore\n";
    while ($row = $quiz_reports->fetch_assoc()) {
        echo htmlspecialchars($row['quiz_title'], ENT_QUOTES, 'UTF-8') . "\t" .
             htmlspecialchars($row['student_name'], ENT_QUOTES, 'UTF-8') . "\t" .
             htmlspecialchars($row['score'], ENT_QUOTES, 'UTF-8') . "\n";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .pagination { justify-content: center; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">View Quiz Reports</h2>

        <!-- Navigation Bar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <a class="navbar-brand" href="<?= ($role == 'admin') ? 'admin_dashboard.php' : 'faculty_dashboard.php'; ?>">Dashboard</a>
            <div class="navbar-nav">
                <a class="nav-item nav-link btn btn-danger text-white" href="logout.php">Logout</a>
            </div>
        </nav>

        <!-- Button to Download Excel File -->
        <form method="post" class="mb-3">
            <button type="submit" name="download_excel" class="btn btn-success">Download as Excel</button>
        </form>

        <!-- Filters Section -->
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" id="searchQuizTitle" class="form-control" placeholder="Search by Quiz Title">
            </div>
            <div class="col-md-4">
                <input type="text" id="searchStudentName" class="form-control" placeholder="Search by Student Name">
            </div>
            <div class="col-md-4">
                <input type="number" id="minScore" class="form-control" placeholder="Min Score">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 offset-md-8">
                <input type="number" id="maxScore" class="form-control" placeholder="Max Score">
            </div>
        </div>

        <!-- Quiz Reports Table -->
        <h4 class="mb-3">Quiz Reports</h4>
        <table class="table table-striped" id="quizReportsTable">
            <thead>
                <tr>
                    <th>Quiz Title</th>
                    <th>Student Name</th>
                    <th>Score</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($report = $quiz_reports->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($report['quiz_title']) ?></td>
                    <td><?= htmlspecialchars($report['student_name']) ?></td>
                    <td><?= htmlspecialchars($report['score']) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filter Functionality
        document.getElementById('searchQuizTitle').addEventListener('input', filterReports);
        document.getElementById('searchStudentName').addEventListener('input', filterReports);
        document.getElementById('minScore').addEventListener('input', filterReports);
        document.getElementById('maxScore').addEventListener('input', filterReports);

        function filterReports() {
            var quizTitle = document.getElementById('searchQuizTitle').value.toUpperCase();
            var studentName = document.getElementById('searchStudentName').value.toUpperCase();
            var minScore = parseInt(document.getElementById('minScore').value);
            var maxScore = parseInt(document.getElementById('maxScore').value);

            var rows = document.querySelectorAll('#quizReportsTable tbody tr');

            rows.forEach(function (row) {
                var titleText = row.cells[0].innerText.toUpperCase();
                var studentText = row.cells[1].innerText.toUpperCase();
                var score = parseInt(row.cells[2].innerText);

                var matchesTitle = titleText.indexOf(quizTitle) > -1;
                var matchesStudent = studentText.indexOf(studentName) > -1;
                var matchesScore = (!isNaN(minScore) ? score >= minScore : true) &&
                                   (!isNaN(maxScore) ? score <= maxScore : true);

                row.style.display = matchesTitle && matchesStudent && matchesScore ? '' : 'none';
            });
        }
    </script>
</body>
</html>

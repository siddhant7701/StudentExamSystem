<?php
session_start();
include 'db_connect.php'; // Ensure this file establishes the database connection

// Check if the user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch student data
$query = $conn->prepare("SELECT student_id, course_id, can_view_results FROM students WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$student_result = $query->get_result();

if ($student_result->num_rows > 0) {
    $student_data = $student_result->fetch_assoc();
    $student_id = $student_data['student_id'];
    $course_id = $student_data['course_id'];
    $can_view_results = $student_data['can_view_results'];
} else {
    echo "<p>Student data not found. Please contact the admin.</p>";
    echo '<a href="student_dashboard.php" class="btn btn-primary">Back to Dashboard</a>';
    exit();
}

// Check if the student is allowed to view results
if (!$can_view_results) {
    echo "<p>You are not allowed to see your scores yet. Please check back later or contact your instructor.</p>";
    echo '<a href="student_dashboard.php" class="btn btn-primary">Back to Dashboard</a>';
    exit();
}

// Fetch all scores for the student along with full marks from results
$results_query = "
    SELECT r.score, q.quiz_title, r.full_marks, r.quiz_date
    FROM results r
    JOIN quizzes q ON r.quiz_id = q.quiz_id
    WHERE r.student_id = ?";
$stmt = $conn->prepare($results_query);
$stmt->bind_param('i', $student_id);
$stmt->execute();
$results = $stmt->get_result();

// Check if the statement was prepared successfully
if (!$stmt) {
    echo "Error preparing statement: " . $conn->error;
    exit();
}

if (!$results) {
    echo "<p>Error fetching scores: " . $conn->error . "</p>";
    echo '<a href="student_dashboard.php" class="btn btn-primary">Back to Dashboard</a>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Scores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>View Scores</h1>
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <a class="navbar-brand" href="student_dashboard.php">Dashboard</a>
            <div class="navbar-nav">
                <a class="nav-item nav-link btn btn-danger text-white" href="logout.php">Logout</a>
            </div>
        </nav>  
        <?php if ($results->num_rows > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Quiz Title</th>
                        <th>Score</th>
                        <th>Full Marks</th>
                        <th>Percentage</th>
                        <th>Quiz Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $results->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['quiz_title']); ?></td>
                            <td><?php echo htmlspecialchars($row['score']); ?></td>
                            <td><?php echo htmlspecialchars($row['full_marks']); ?></td>
                            <td>
                                <?php 
                                if ($row['full_marks'] > 0) {
                                    $percentage = ($row['score'] / $row['full_marks']) * 100;
                                    $color = $percentage < 33 ? 'red' : 'black';
                                    echo "<span style='color: $color;'>" . htmlspecialchars(number_format($percentage, 2)) . '%</span>';
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                if (isset($row['quiz_date'])) {
                                    echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($row['quiz_date'])));
                                } else {
                                    echo 'Date not available';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No scores available.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
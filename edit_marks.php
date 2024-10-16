<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'faculty')) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $result_id = $_POST['result_id'];
    $new_score = $_POST['new_score'];
    $conn->query("UPDATE results SET score = '$new_score' WHERE result_id = '$result_id'");
    echo "<div class='alert alert-success'>Marks updated successfully!</div>";
}

// Fetch results for editing
$results = $conn->query("SELECT r.result_id, r.score, s.student_name, q.quiz_title 
                         FROM results r 
                         JOIN students s ON r.student_id = s.student_id
                         JOIN quizzes q ON r.quiz_id = q.quiz_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Marks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Marks</h2>
        <form method="post">
            <div class="mb-3">
                <label for="result_id" class="form-label">Select Student Result:</label>
                <select name="result_id" class="form-select" id="result_id">
                    <?php while ($result = $results->fetch_assoc()): ?>
                        <option value="<?= $result['result_id'] ?>">
                            <?= $result['student_name'] ?> - <?= $result['quiz_title'] ?> (Current Score: <?= $result['score'] ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="new_score" class="form-label">New Score:</label>
                <input type="number" name="new_score" class="form-control" id="new_score" required>
            </div>

            <button type="submit" class="btn btn-primary">Update Marks</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

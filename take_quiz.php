<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header('Location: login.php');
    exit();
}

// Fetch the correct student_id using the user_id from the session
$user_id = $_SESSION['user_id'];
$student_result = $conn->query("SELECT student_id FROM students WHERE user_id = '$user_id'");
if ($student_result->num_rows > 0) {
    $student_data = $student_result->fetch_assoc();
    $student_id = $student_data['student_id'];
} else {
    echo "<div class='alert alert-danger'>Student data not found. Please contact the admin.</div>";
    exit();
}

// Check if quiz_id is provided in the URL
if (isset($_GET['quiz_id'])) {
    $quiz_id = $_GET['quiz_id'];

// If form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $score = 0;
    $negative_marks = 1; // Define negative marks for wrong answers

    foreach ($_POST['answers'] as $question_id => $selected_option) {
        // Fetch the correct answer for each question
        $stmt = $conn->prepare("SELECT choice_text, is_correct FROM choices WHERE question_id = ? AND is_correct = 1");
        $stmt->bind_param('i', $question_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $correct_answer = $result->fetch_assoc();

        // Check if the selected option matches the correct answer
        if ($correct_answer && $correct_answer['choice_text'] === $selected_option) {
            $score++; // Increment score for correct answer
        } else {
            $score -= $negative_marks; // Deduct marks for wrong answer
        }
    }

    // Save the result in the results table using prepared statements
    $stmt = $conn->prepare("INSERT INTO results (student_id, quiz_id, score) VALUES (?, ?, ?)
                            ON DUPLICATE KEY UPDATE score = ?");
    $stmt->bind_param('iiii', $student_id, $quiz_id, $score, $score); // Bind the score for updates too

    if ($stmt->execute()) {
        // Redirect to the score page to avoid form resubmission issues
        header("Location: view_scores.php?quiz_id=$quiz_id&score=$score");
        exit();
    } else {
        // Capture the error if the query fails
        echo "<div class='alert alert-danger'>Error saving the quiz result: " . $stmt->error . "</div>";
    }
}


    // Fetch questions for the quiz
    $stmt = $conn->prepare("SELECT * FROM questions WHERE quiz_id = ?");
    $stmt->bind_param('i', $quiz_id);
    $stmt->execute();
    $questions = $stmt->get_result();

    if ($questions->num_rows == 0) {
        echo "<div class='alert alert-warning'>No questions found for this quiz.</div>";
        exit();
    }
} else {
    echo "<div class='alert alert-danger'>No quiz selected.</div>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Quiz</title>
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
    <div class="container mt-5">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
            <a class="navbar-brand" href="student_dashboard.php"><i class="fas fa-graduation-cap"></i> Dashboard</a>
            <div class="navbar-nav">
                <h2 class="text-center">Take Quiz</h2>
                <a class="nav-item nav-link btn btn-danger text-white" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>
        <form method="post">
            <?php while ($question = $questions->fetch_assoc()): ?>
                <div class="mb-4">
                    <h5><?= htmlspecialchars($question['question_text']) ?></h5>
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM choices WHERE question_id = ?");
                    $stmt->bind_param('i', $question['question_id']);
                    $stmt->execute();
                    $choices = $stmt->get_result();

                    while ($choice = $choices->fetch_assoc()): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="answers[<?= $question['question_id'] ?>]" value="<?= htmlspecialchars($choice['choice_text']) ?>" required>
                            <label class="form-check-label"><?= htmlspecialchars($choice['choice_text']) ?></label>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endwhile; ?>
            <input type="submit" value="Submit Quiz" class="btn btn-primary">
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

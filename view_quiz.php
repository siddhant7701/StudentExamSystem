<?php
include 'db_connect.php';
session_start();

// Ensure only admins and faculty can access
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'faculty')) {
    header('Location: login.php');
    exit();
}

$quiz_id = $_GET['quiz_id'];

// Handle updates to quiz questions and choices
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update existing questions and choices
    if (isset($_POST['questions'])) {
        foreach ($_POST['questions'] as $question_id => $question_text) {
            // Update question text
            $conn->query("UPDATE questions SET question_text = '$question_text' WHERE question_id = '$question_id'");

            // Check if choices exist for this question
            if (isset($_POST['choices'][$question_id])) {
                foreach ($_POST['choices'][$question_id] as $choice_id => $choice_text) {
                    $is_correct = isset($_POST['correct'][$question_id]) && $_POST['correct'][$question_id] == $choice_id ? 1 : 0;
                    $conn->query("UPDATE choices SET choice_text = '$choice_text', is_correct = '$is_correct' WHERE choice_id = '$choice_id'");
                }
            }
        }
    }

    // Handle adding a new question
    if (isset($_POST['add_question'])) {
        $new_question = $_POST['new_question'];
        $conn->query("INSERT INTO questions (quiz_id, question_text) VALUES ('$quiz_id', '$new_question')");
        $new_question_id = $conn->insert_id;

        // Adding choices for the new question (assumes up to 4 choices)
        if (isset($_POST['new_choices'])) {
            foreach ($_POST['new_choices'] as $index => $choice_text) {
                if (!empty($choice_text)) {
                    $is_correct = (isset($_POST['new_correct']) && $_POST['new_correct'] == $index) ? 1 : 0; // Check if this choice is marked as correct
                    $conn->query("INSERT INTO choices (question_id, choice_text, is_correct) VALUES ('$new_question_id', '$choice_text', '$is_correct')");
                }
            }
        }
    }

    echo "<div class='alert alert-success'>Quiz updated successfully!</div>";
}

// Handle question deletion
if (isset($_GET['delete_question_id'])) {
    $question_id_to_delete = $_GET['delete_question_id'];
    // Delete choices first to maintain referential integrity
    $conn->query("DELETE FROM choices WHERE question_id = '$question_id_to_delete'");
    // Now delete the question
    $conn->query("DELETE FROM questions WHERE question_id = '$question_id_to_delete'");
    echo "<div class='alert alert-success'>Question deleted successfully!</div>";
}

// Fetch quiz details, including the faculty who created it
$quiz = $conn->query("SELECT q.quiz_title, f.faculty_name 
                      FROM quizzes q 
                      JOIN faculty f ON q.faculty_id = f.user_id 
                      WHERE q.quiz_id = '$quiz_id'")->fetch_assoc();

// Fetch questions for this quiz
$questions = $conn->query("SELECT * FROM questions WHERE quiz_id = '$quiz_id'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View and Edit Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>View and Edit Quiz: <?= htmlspecialchars($quiz['quiz_title']) ?></h2>
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <a class="navbar-brand" href="<?= $_SESSION['role'] == 'admin' ? 'admin_dashboard.php' : 'faculty_dashboard.php' ?>">Dashboard</a>
            <div class="navbar-nav">
                <a class="nav-item nav-link btn btn-danger text-white" href="logout.php">Logout</a>
            </div>
        </nav>

        <form method="post">
            <?php while ($question = $questions->fetch_assoc()): ?>
            <div class="mb-4">
                <label for="question_<?= $question['question_id'] ?>" class="form-label">Question:</label>
                <input type="text" name="questions[<?= $question['question_id'] ?>]" class="form-control" value="<?= htmlspecialchars($question['question_text']) ?>" id="question_<?= $question['question_id'] ?>">

                <!-- Fetch choices for this question -->
                <?php
                $choices = $conn->query("SELECT * FROM choices WHERE question_id = '{$question['question_id']}'");
                while ($choice = $choices->fetch_assoc()): ?>
                <div class="form-check">
                    <input type="radio" name="correct[<?= $question['question_id'] ?>]" value="<?= $choice['choice_id'] ?>" <?= $choice['is_correct'] ? 'checked' : '' ?> class="form-check-input" id="choice_<?= $choice['choice_id'] ?>">
                    <input type="text" name="choices[<?= $question['question_id'] ?>][<?= $choice['choice_id'] ?>]" value="<?= htmlspecialchars($choice['choice_text']) ?>" class="form-control mb-2">
                </div>
                <?php endwhile; ?>

                <!-- Delete Question Button -->
                <a href="?quiz_id=<?= $quiz_id ?>&delete_question_id=<?= $question['question_id'] ?>" class="btn btn-danger mt-2">Delete Question</a>
            </div>
            <?php endwhile; ?>

            <button type="submit" class="btn btn-primary">Update Quiz</button>
        </form>

        <hr>

        <!-- Add New Question Form -->
        <h3>Add New Question</h3>
        <form method="post">
            <input type="text" name="new_question" placeholder="Enter new question" class="form-control mb-2" required>
            <h4>Choices:</h4>
            <input type="text" name="new_choices[]" placeholder="Choice 1" class="form-control mb-2" required>
            <input type="text" name="new_choices[]" placeholder="Choice 2" class="form-control mb-2" required>
            <input type="text" name="new_choices[]" placeholder="Choice 3" class="form-control mb-2">
            <input type="text" name="new_choices[]" placeholder="Choice 4" class="form-control mb-2">
            <input type="radio" name="new_correct" value="0"> Correct Choice 1
            <input type="radio" name="new_correct" value="1"> Correct Choice 2
            <input type="radio" name="new_correct" value="2"> Correct Choice 3
            <input type="radio" name="new_correct" value="3"> Correct Choice 4
            <button type="submit" name="add_question" class="btn btn-success">Add Question</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

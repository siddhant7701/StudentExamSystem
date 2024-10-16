<?php
session_start();
include 'db_connect.php';

// Ensure only faculty can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'faculty') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $quiz_title = $conn->real_escape_string($_POST['quiz_title']);
    $subject_id = (int) $_POST['subject_id'];
    $user_id = (int) $_SESSION['user_id'];

    // Fetch the corresponding faculty_id from the faculty table using user_id
    $faculty_result = $conn->query("SELECT faculty_id FROM faculty WHERE user_id = '$user_id'");
    if ($faculty_result->num_rows > 0) {
        $faculty_row = $faculty_result->fetch_assoc();
        $faculty_id = $faculty_row['faculty_id'];

        // Insert the quiz with the correct faculty_id
        $quiz_insert_query = "INSERT INTO quizzes (subject_id, faculty_id, quiz_title) VALUES ('$subject_id', '$faculty_id', '$quiz_title')";
        if ($conn->query($quiz_insert_query) === TRUE) {
            $quiz_id = $conn->insert_id; // Get the ID of the newly inserted quiz

            // Insert questions and answers
            foreach ($_POST['questions'] as $index => $question_text) {
                $question_text = $conn->real_escape_string($question_text);
                $conn->query("INSERT INTO questions (quiz_id, question_text) VALUES ('$quiz_id', '$question_text')");
                $question_id = $conn->insert_id;

                foreach ($_POST['options'][$index] as $option_index => $option_text) {
                    $option_text = $conn->real_escape_string($option_text);
                    $is_correct = ($_POST['correct'][$index] == $option_text) ? 1 : 0;
                    $conn->query("INSERT INTO choices (question_id, choice_text, is_correct) VALUES ('$question_id', '$option_text', '$is_correct')");
                }
            }
            $_SESSION['message'] = "<div class='alert alert-success mt-3'>Quiz created successfully!</div>";
        } else {
            $_SESSION['message'] = "<div class='alert alert-danger mt-3'>Error creating quiz: " . $conn->error . "</div>";
        }
    } else {
        $_SESSION['message'] = "<div class='alert alert-danger mt-3'>Faculty not found for this user.</div>";
    }
}

// Fetch subjects for the dropdown
$subjects = $conn->query("SELECT * FROM subjects");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .btn-back {
            margin-bottom: 20px;
        }
        .question-block {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>


    <div class="container mt-5">
        <!-- Display session messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <?= $_SESSION['message'] ?>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <!-- Quiz Creation Form -->
        <h2 class="text-center mb-4">Create New Quiz</h2>
            <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <a class="navbar-brand" href="faculty_dashboard.php ">Dashboard</a>
            <div class="navbar-nav">
                <a class="nav-item nav-link btn btn-danger text-white" href="logout.php">Logout</a>
            </div>
        </nav>
        <form method="post" id="quizForm">
            <div class="mb-3">
                <label for="quiz_title" class="form-label">Quiz Title:</label>
                <input type="text" name="quiz_title" class="form-control" id="quiz_title" placeholder="Enter quiz title" required>
            </div>

            <div class="mb-3">
                <label for="subject_id" class="form-label">Subject:</label>
                <select name="subject_id" class="form-select" id="subject_id" required>
                    <option selected disabled>Select a subject</option>
                    <?php while ($subject = $subjects->fetch_assoc()): ?>
                        <option value="<?= $subject['subject_id'] ?>"><?= htmlspecialchars($subject['subject_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <h4 class="mt-4">Add Questions</h4>
            <div id="questions-container" class="mb-3">
                <div class="question-block border rounded p-3 mb-3">
                    <label>Question 1:</label>
                    <input type="text" name="questions[]" class="form-control mb-2" placeholder="Enter question text" required>
                    <label>Options:</label>
                    <input type="text" name="options[0][]" class="form-control mb-1" placeholder="Option 1" required>
                    <input type="text" name="options[0][]" class="form-control mb-1" placeholder="Option 2" required>
                    <input type="text" name="options[0][]" class="form-control mb-1" placeholder="Option 3" required>
                    <input type="text" name="options[0][]" class="form-control mb-1" placeholder="Option 4" required>
                    <label>Correct Option:</label>
                    <input type="text" name="correct[0]" class="form-control mb-2" placeholder="Enter correct option" required>
                </div>
            </div>
            <button type="button" class="btn btn-outline-secondary mb-3" onclick="addQuestion()">Add Another Question</button>
            <button type="submit" class="btn btn-primary">Create Quiz</button>
        </form>
    </div>

    <script>
    let questionCount = 1;
    function addQuestion() {
        const container = document.getElementById('questions-container');
        const questionBlock = `
            <div class="question-block border rounded p-3 mb-3">
                <label>Question ${questionCount + 1}:</label>
                <input type="text" name="questions[]" class="form-control mb-2" placeholder="Enter question text" required>
                <label>Options:</label>
                <input type="text" name="options[${questionCount}][]" class="form-control mb-1" placeholder="Option 1" required>
                <input type="text" name="options[${questionCount}][]" class="form-control mb-1" placeholder="Option 2" required>
                <input type="text" name="options[${questionCount}][]" class="form-control mb-1" placeholder="Option 3" required>
                <input type="text" name="options[${questionCount}][]" class="form-control mb-1" placeholder="Option 4" required>
                <label>Correct Option:</label>
                <input type="text" name="correct[${questionCount}]" class="form-control mb-2" placeholder="Enter correct option" required>
            </div>
        `;
        container.innerHTML += questionBlock;
        questionCount++;
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

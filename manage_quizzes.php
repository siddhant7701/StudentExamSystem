<?php
include 'db_connect.php';
session_start();

// Ensure only admins and faculty can access
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'faculty')) {
    header('Location: login.php');
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Fetch quizzes based on role
if ($role == 'admin') {
    // Admin can see all quizzes
    $quiz_query = "
        SELECT q.quiz_id, q.quiz_title, s.subject_name, f.faculty_name 
        FROM quizzes q
        JOIN subjects s ON q.subject_id = s.subject_id
        JOIN faculty f ON q.faculty_id = f.faculty_id
    ";
} else {
    // Faculty can only see the quizzes they created
    $quiz_query = "
        SELECT q.quiz_id, q.quiz_title, s.subject_name 
        FROM quizzes q
        JOIN subjects s ON q.subject_id = s.subject_id
        WHERE q.faculty_id = (SELECT faculty_id FROM faculty WHERE user_id = '$user_id')
    ";
}

$quizzes = $conn->query($quiz_query);

// Fetch subjects and faculty for filter options (for admin)
$subjects = $conn->query("SELECT * FROM subjects");
$faculty = $conn->query("SELECT * FROM faculty");

// Check for query success
if (!$quizzes) {
    $_SESSION['message'] = "<div class='alert alert-danger'>Error fetching quizzes: " . $conn->error . "</div>";
    header('Location: manage_quizzes.php');
    exit();
}

// Handle deletion of selected quizzes
if (isset($_GET['delete'])) {
    $quiz_id = $_GET['delete'];
    $delete_query = "DELETE FROM quizzes WHERE quiz_id = $quiz_id";
    if ($conn->query($delete_query)) {
        $_SESSION['message'] = "<div class='alert alert-success'>Quiz deleted successfully!</div>";
    } else {
        $_SESSION['message'] = "<div class='alert alert-danger'>Error deleting quiz: " . $conn->error . "</div>";
    }
    header('Location: manage_quizzes.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Quizzes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Manage Quizzes</h2>

        <!-- Display session messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <?= $_SESSION['message'] ?>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <!-- Back to dashboard button -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <a class="navbar-brand" href="<?= ($role == 'admin') ? 'admin_dashboard.php' : 'faculty_dashboard.php'; ?>">Dashboard</a>
            <div class="navbar-nav">
                <a class="nav-item nav-link btn btn-danger text-white" href="logout.php">Logout</a>
            </div>
        </nav>

        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" id="search" class="form-control" placeholder="Search by quiz title...">
            </div>
            <?php if ($role == 'admin'): ?>
                <div class="col-md-3">
                    <select id="filterSubject" class="form-select">
                        <option value="">Filter by Subject</option>
                        <?php while ($subject = $subjects->fetch_assoc()): ?>
                            <option value="<?= $subject['subject_name'] ?>"><?= $subject['subject_name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="filterFaculty" class="form-select">
                        <option value="">Filter by Faculty</option>
                        <?php while ($fac = $faculty->fetch_assoc()): ?>
                            <option value="<?= $fac['faculty_name'] ?>"><?= $fac['faculty_name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            <?php endif; ?>
        </div>

        <h3>Quiz List</h3>
        <table class="table table-striped" id="quizTable">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th onclick="sortTable(1)">Quiz Title <span>&#8597;</span></th>
                    <th onclick="sortTable(2)">Subject <span>&#8597;</span></th>
                    <?php if ($role == 'admin'): ?>
                        <th onclick="sortTable(3)">Created By <span>&#8597;</span></th>
                    <?php endif; ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $quizzes->fetch_assoc()): ?>
                <tr>
                    <td><input type="checkbox" class="selectItem" value="<?= $row['quiz_id'] ?>"></td>
                    <td><?= $row['quiz_title'] ?></td>
                    <td><?= $row['subject_name'] ?></td>
                    <?php if ($role == 'admin'): ?>
                        <td><?= $row['faculty_name'] ?></td>
                    <?php endif; ?>
                    <td>
                        <a href="view_quiz.php?quiz_id=<?= $row['quiz_id'] ?>" class="btn btn-info">View & Edit</a>
                        <a href="?delete=<?= $row['quiz_id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this quiz?');">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
      
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality
        document.getElementById('search').addEventListener('input', function () {
            var filter = this.value.toUpperCase();
            var rows = document.getElementById('quizTable').getElementsByTagName('tr');
            for (var i = 1; i < rows.length; i++) {
                var title = rows[i].getElementsByTagName('td')[1].innerText.toUpperCase();
                rows[i].style.display = title.indexOf(filter) > -1 ? '' : 'none';
            }
        });

        // Sort table function
        function sortTable(n) {
            var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
            table = document.getElementById("quizTable");
            switching = true;
            // Set the sorting direction to ascending
            dir = "asc"; 
            // Make a loop that will continue until no switching has been done
            while (switching) {
                switching = false;
                rows = table.rows;
                // Loop through all table rows (except the headers)
                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    // Get the two elements you want to compare
                    x = rows[i].getElementsByTagName("TD")[n];
                    y = rows[i + 1].getElementsByTagName("TD")[n];
                    // Check if the two rows should switch place, based on the direction, asc or desc
                    if (dir == "asc") {
                        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir == "desc") {
                        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                }
                if (shouldSwitch) {
                    // If a switch has been marked, make the switch and mark that a switch has been done
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    switchcount++;
                } else {
                    // If no switching has been done AND the direction is "asc"
                    if (switchcount === 0 && dir == "asc") {
                        dir = "desc";
                        switching = true;
                    }
                }
            }
        }

        // Filter functionality for subjects and faculties
        document.getElementById('filterSubject').addEventListener('change', function() {
            filterTable();
        });

        document.getElementById('filterFaculty').addEventListener('change', function() {
            filterTable();
        });

        function filterTable() {
            var subjectFilter = document.getElementById('filterSubject').value.toUpperCase();
            var facultyFilter = document.getElementById('filterFaculty').value.toUpperCase();
            var rows = document.getElementById('quizTable').getElementsByTagName('tr');

            for (var i = 1; i < rows.length; i++) {
                var subjectName = rows[i].getElementsByTagName('td')[2].innerText.toUpperCase();
                var facultyName = rows[i].getElementsByTagName('td')[3]?.innerText.toUpperCase() || '';

                // Check both filters
                if ((subjectFilter === "" || subjectName.indexOf(subjectFilter) > -1) &&
                    (facultyFilter === "" || facultyName.indexOf(facultyFilter) > -1)) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }

        // Select all checkboxes functionality
        document.getElementById('selectAll').addEventListener('change', function() {
            var checkboxes = document.getElementsByClassName('selectItem');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = this.checked;
            }
        });

        // Bulk delete functionality
        document.getElementById('bulkDelete').addEventListener('click', function() {
            var selectedQuizzes = [];
            var checkboxes = document.getElementsByClassName('selectItem');
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    selectedQuizzes.push(checkboxes[i].value);
                }
            }
            if (selectedQuizzes.length > 0) {
                if (confirm("Are you sure you want to delete selected quizzes?")) {
                    // Perform bulk delete action here (e.g., redirect with selected quiz IDs)
                    window.location.href = 'bulk_delete.php?ids=' + selectedQuizzes.join(',');
                }
            } else {
                alert("Please select at least one quiz to delete.");
            }
        });
    </script>
</body>
</html>

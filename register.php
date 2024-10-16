<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password for security
    $role = $_POST['role']; // 'student', 'faculty', or 'admin'

    // Insert into users table
    $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
    if ($conn->query($sql) === TRUE) {
        $user_id = $conn->insert_id; // Get the ID of the inserted user

        // Role-specific logic
        if ($role == 'student') {
            $name = $_POST['student_name'];
            $course_id = $_POST['course_id'];
            $conn->query("INSERT INTO students (user_id, student_name, course_id) VALUES ('$user_id', '$name', '$course_id')");
        } else if ($role == 'faculty') {
            $name = $_POST['faculty_name'];
            $course_id = $_POST['course_id'];
            $conn->query("INSERT INTO faculty (user_id, faculty_name, course_id) VALUES ('$user_id', '$name', '$course_id')");
        } else if ($role == 'admin') {
            // Placeholder for OTP verification for admin
            $otp = rand(100000, 999999); // Generate a random OTP for example
            echo "<div class='alert alert-info'>Admin registration requires OTP verification. OTP: $otp</div>"; 
        }

        header("Location: login.php");
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin-top: 50px;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .btn-custom {
            background-color: #0d6efd;
            color: white;
        }
        .btn-custom:hover {
            background-color: #0b5ed7;
        }
        .header-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-buttons">
            <a href="index.php" class="btn btn-secondary">Dashboard</a>
            <a href="login.php" class="btn btn-custom">Login</a>
        </div>
        
        <h2 class="text-center mb-4">Register</h2>
        <form method="post">
            <!-- Username Field -->
            <div class="mb-3">
                <label for="username" class="form-label">Username:</label>
                <input type="text" name="username" class="form-control" id="username" required>
            </div>

            <!-- Password Field -->
            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" name="password" class="form-control" id="password" required>
            </div>

            <!-- Role Selection (with Admin option) -->
            <div class="mb-3">
                <label for="role" class="form-label">Role:</label>
                <select name="role" class="form-select" id="role" required onchange="toggleNameFields()">
                    <option value="student">Student</option>

                </select>
            </div>

            <!-- Name Field (changes based on role selection) -->
            <div class="mb-3" id="student-name-field">
                <label for="student_name" class="form-label">Student Name:</label>
                <input type="text" name="student_name" class="form-control" placeholder="Name (for students)" id="student_name">
            </div>

            <div class="mb-3" id="faculty-name-field" style="display:none;">
                <label for="faculty_name" class="form-label">Faculty Name:</label>
                <input type="text" name="faculty_name" class="form-control" placeholder="Name (for faculty)" id="faculty_name">
            </div>

            <!-- Course Selection (only visible for student and faculty) -->
            <div class="mb-3" id="course-field">
                <label for="course_id" class="form-label">Course:</label>
                <select name="course_id" class="form-select" id="course_id">
                    <?php
                    $courses = $conn->query("SELECT * FROM courses");
                    while ($course = $courses->fetch_assoc()) {
                        echo "<option value='{$course['course_id']}'>{$course['course_name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-custom w-100">Register</button>
        </form>
    </div>

    <script>
        // Toggle visibility of student_name and faculty_name fields based on role selection
        function toggleNameFields() {
            const role = document.getElementById('role').value;
            const studentField = document.getElementById('student-name-field');
            const facultyField = document.getElementById('faculty-name-field');
            const courseField = document.getElementById('course-field');
            
            if (role === 'student') {
                studentField.style.display = 'block';
                facultyField.style.display = 'none';
                courseField.style.display = 'block';
            } else if (role === 'faculty') {
                studentField.style.display = 'none';
                facultyField.style.display = 'block';
                courseField.style.display = 'block';
            } else if (role === 'admin') {
                studentField.style.display = 'none';
                facultyField.style.display = 'none';
                courseField.style.display = 'none';
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

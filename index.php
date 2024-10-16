<?php
// Start session
session_start();

// Check if user is already logged in and redirect them to their respective dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
    if ($role == 'admin') {
        header('Location: admin_dashboard.php');
        exit();
    } elseif ($role == 'faculty') {
        header('Location: faculty_dashboard.php');
        exit();
    } elseif ($role == 'student') {
        header('Location: student_dashboard.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Moving Background Styling */
        .background {
            position: fixed;
            width: 100%;
            height: 100%;
            background: linear-gradient(-45deg, #0D324D, #7F5A83, #1C92D2, #F2C94C);
            background-size: 400% 400%;
            animation: gradientMove 15s ease infinite;
            z-index: -1;
        }

        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
            color: white;
        }

        h2 {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 20px;
        }

        p {
            font-size: 1.1rem;
            margin-bottom: 30px;
        }

        .btn-option {
            display: inline-block;
            padding: 15px 30px;
            font-size: 1.2rem;
            font-weight: bold;
            color: white;
            background-color: #007BFF;
            border: none;
            border-radius: 5px;
            margin: 10px;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-option:hover {
            background-color:aliceblue;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .option-container {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-bottom: 20px;
        }

        /* Responsive Design for smaller screens */
        @media (max-width: 768px) {
            h2 {
                font-size: 2.5rem;
            }

            p {
                font-size: 1rem;
            }

            .option-container {
                flex-direction: column;
                gap: 15px;
            }

            .btn-option {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="background"></div> <!-- Moving background -->
    <div class="container">
        <h2>Welcome to the Exam Management System</h2>
        <p>Manage your exams, track student performance, and ensure a seamless experience for both faculty and students.</p>
        
        <div class="option-container">
            <!-- Login Button -->
            <a href="login.php" class="btn-option">Login</a>
            <!-- Register Button -->
            <a href="register.php" class="btn-option">Register</a>
        </div>
        
        <!-- Additional Content Section -->
        <div class="additional-info">
            <p>With our system, you can:</p>
            <ul class="list-group list-group-flush">
                <li class="list-group-item bg-transparent text-white">Easily manage quizzes and exams</li>
                <li class="list-group-item bg-transparent text-white">Track student performance with detailed reports</li>
                <li class="list-group-item bg-transparent text-white">Provide seamless login for students, faculty, and admins</li>
            </ul>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

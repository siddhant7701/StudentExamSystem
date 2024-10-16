<?php
session_start();
include 'db_connect.php'; // Assuming this connects to your database

// Initialize variables
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Fetch user by username and role
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $user = $stmt->get_result();

    if ($user->num_rows > 0) {
        $user_data = $user->fetch_assoc();

        // Verify password
        if (password_verify($password, $user_data['password']) || $password === $user_data['password']) {
            // If password_verify() fails, fall back to plain text comparison
            $_SESSION['user_id'] = $user_data['user_id'];
            $_SESSION['role'] = $user_data['role'];
        
            switch ($user_data['role']) {
                case 'admin':
                    header('Location: admin_dashboard.php');
                    exit();
                case 'faculty':
                    header('Location: faculty_dashboard.php');
                    exit();
                case 'student':
                    header('Location: student_dashboard.php');
                    exit();
            }
        } else {
            $error_message = 'Incorrect password!';
        }
        
    } else {
        $error_message = 'User not found!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f7f7f7;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 50px;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #007bff;
            color: white;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .alert {
            margin-top: 20px;
        }
        .header-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .btn-custom {
            background-color: #007bff;
            color: white;
        }
        .btn-custom:hover {
            background-color: #0056b3;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-buttons">
            <a href="index.php" class="btn btn-secondary">Dashboard</a>
            <a href="login.php" class="btn btn-custom">Login</a>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h3>Login</h3>
                    </div>
                    <div class="card-body">
                        <!-- Display error message if any -->
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger text-center">
                                <?= $error_message ?>
                            </div>
                        <?php endif; ?>

                        <form method="post">
                            <div class="form-group mb-3">
                                <label for="username">Emailid/Username</label>
                                <input type="text" id="username" name="username" class="form-control" placeholder="Enter your email id or username" required autofocus>
                            </div>
                            <div class="form-group mb-3">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="role">Role</label>
                                <select name="role" id="role" class="form-control" required>
                                    <option value="" disabled selected>Select your role</option>
                                    <option value="admin">Admin</option>
                                    <option value="faculty">Faculty</option>
                                    <option value="student">Student</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <button type="submit" class="btn btn-primary w-100">Login</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <p>Don't have an account? <a href="register.php">Register here</a></p>
                    </div>
                </div>

                <!-- Display instructions for debugging purposes -->
                <div class="mt-4 text-center">
                    <p><strong>Debug Info:</strong> Use the following for testing:</p>
                    <p>Admin: <strong>New Admin</strong> / Faculty: <strong>New Faculty</strong> / Student: <strong>New Student</strong></p>
                    <p>All passwords: <strong>123</strong></p>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

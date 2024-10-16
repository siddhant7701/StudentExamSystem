<?php
session_start();
include 'db_connect.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user information from the database
$user_query = $conn->prepare("SELECT username, role FROM users WHERE user_id = ?");
if (!$user_query) {
    die("Error preparing query: " . $conn->error);
}
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_info = $user_query->get_result()->fetch_assoc();

// Handle form submission for updating profile information
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = $_POST['username'];
    $new_password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // Update user information in the database
    $update_query = "UPDATE users SET username = ?";
    $params = [$new_username];
    $param_types = "s";

    if ($new_password) {
        $update_query .= ", password = ?";
        $params[] = $new_password;
        $param_types .= "s";
    }

    $update_query .= " WHERE user_id = ?";
    $params[] = $user_id;
    $param_types .= "i";

    // Prepare the statement
    $stmt = $conn->prepare($update_query);
    if (!$stmt) {
        die("Error preparing update query: " . $conn->error);
    }
    $stmt->bind_param($param_types, ...$params);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Profile updated successfully!</div>";
        // Refresh user information
        $user_info = $conn->query("SELECT username, role FROM users WHERE user_id = $user_id")->fetch_assoc();
    } else {
        echo "<div class='alert alert-danger'>Error updating profile: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>


    <div class="container mt-5">
        <h2>Profile Settings</h2>
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <a class="navbar-brand" href="faculty_dashboard.php">Dashboard</a>
            <div class="navbar-nav">
                <a class="nav-item nav-link btn btn-danger text-white" href="logout.php">Logout</a>
            </div>
        </nav>
        <form method="post">
            <!-- Username -->
            <div class="mb-3">
                <label for="username" class="form-label">Username:</label>
                <input type="text" name="username" class="form-control" id="username" value="<?= htmlspecialchars($user_info['username']) ?>" required>
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label">New Password (Leave blank to keep current password):</label>
                <input type="password" name="password" class="form-control" id="password">
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

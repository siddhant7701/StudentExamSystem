<?php
$servername = "localhost";  // Use localhost or the IP address where your MySQL is running
$username = "root";         // Replace with your MySQL username
$password = "";             // Replace with your MySQL password (empty for default XAMPP setup)
$dbname = "exam_management"; // The name of the database we just created

// Create a new connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

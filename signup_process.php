<?php
session_start();
include_once 'db_connect.php'; // Include database connection script

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $employee_id = $_POST['employee_id'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate employee ID length
    if (!preg_match('/^\d{8}$/', $employee_id)) {
        die("Employee ID must be exactly 8 digits.");
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    // Validate password match
    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    // Check if the employee ID already exists
    $sql = "SELECT * FROM users WHERE employee_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        die("Employee ID already exists. Please use a different ID.");
    }
    
    // Close statement
    $stmt->close();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare insert statement
    $sql = "INSERT INTO users (name, email, employee_id, password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $email, $employee_id, $hashed_password);

    // Execute the statement
    if ($stmt->execute()) {
        // Registration successful
        header("Location: login.php");
        exit();
    } else {
        // Error handling
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>

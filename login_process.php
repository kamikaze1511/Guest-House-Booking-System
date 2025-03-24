<?php
session_start();
include_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = $_POST['employee_id'];
    $password = $_POST['password'];

    // Validate input
    $employee_id = mysqli_real_escape_string($conn, $employee_id);
    $password = mysqli_real_escape_string($conn, $password);

    // Query to fetch user data based on employee_id
    $sql = "SELECT * FROM users WHERE employee_id = '$employee_id'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Password is correct, start session and redirect to welcome page
            $_SESSION['employee_id'] = $employee_id;
            $_SESSION['name'] = $row['name']; // Optionally store other user details in session
            header("Location: welcome.php");
            exit();
        } else {
            // Password is incorrect
            echo "Incorrect password. <a href='login.php'>Go back to login</a>";
        }
    } else {
        // User with given employee ID not found
        echo "User not found. <a href='signup.php'>Signup here</a>";
    }
} else {
    // If the form was not submitted via POST method
    echo "Invalid request.";
}

$conn->close();
?>

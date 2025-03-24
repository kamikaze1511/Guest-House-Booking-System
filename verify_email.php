<?php
include_once 'db_connect.php'; // Include database connection script

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Prepare the SQL query to verify the email
    $sql = "SELECT * FROM users WHERE verification_token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Token is valid, update the email_verified status
        $sql_update = "UPDATE users SET email_verified = 1, verification_token = NULL WHERE verification_token = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("s", $token);
        if ($stmt_update->execute()) {
            echo "Email verified successfully! You can now login.";
        } else {
            echo "Error verifying email. Please try again.";
        }
    } else {
        // Invalid token
        echo "Invalid or expired token.";
    }

    $stmt->close();
    $stmt_update->close();
} else {
    echo "No token provided.";
}

$conn->close();
?>

<?php
session_start();
include_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['employee_id']) && !isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_id = $_POST['booking_id'];

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Delete associated guest entries
        $sql = "DELETE FROM guests WHERE booking_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $stmt->close();

        // Delete the booking entry
        $sql = "DELETE FROM bookings WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $stmt->close();

        // Commit the transaction
        $conn->commit();

        // Redirect with success message
        echo "<script>alert('Booking cancelled successfully.'); window.location.href='admin_page.php';</script>";
    } catch (mysqli_sql_exception $exception) {
        // Rollback the transaction in case of an error
        $conn->rollback();

        // Redirect with error message
        echo "<script>alert('Error: " . $exception->getMessage() . "'); window.location.href='welcome.php';</script>";
    }
}

$conn->close();
?>

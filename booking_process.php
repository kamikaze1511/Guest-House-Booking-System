<?php
session_start();
include_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['employee_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = $_SESSION['employee_id'];
    $room_count = $_POST['room_count'];
    $check_in_date = $_POST['check_in_date'];
    $check_out_date = $_POST['check_out_date'];

    // Validate input
    if (empty($room_count) || empty($check_in_date) || empty($check_out_date)) {
        die("Please fill all required fields.");
    }

    // Check room availability
    $sql = "SELECT SUM(room_count) AS booked_rooms FROM bookings WHERE check_out_date >= ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $check_in_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $booked_rooms = $row['booked_rooms'] ?: 0;

    // Fetch total rooms
    $sql = "SELECT total_rooms FROM room_management LIMIT 1";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $total_rooms = $row['total_rooms'];

    if ($booked_rooms + $room_count > $total_rooms) {
        die("Not enough rooms available for the selected dates.");
    }

    // Calculate total price
    $check_in_date_obj = new DateTime($check_in_date);
    $check_out_date_obj = new DateTime($check_out_date);
    $interval = $check_in_date_obj->diff($check_out_date_obj);
    $diffDays = $interval->days ? $interval->days : 1;
    $total_price = 200 * $room_count * $diffDays;

    // Insert booking into database
    $sql = "INSERT INTO bookings (employee_id, room_count, check_in_date, check_out_date, total_price) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sissi", $employee_id, $room_count, $check_in_date, $check_out_date, $total_price);

    if ($stmt->execute()) {
        $booking_id = $stmt->insert_id;

        // Insert guest details into guests table
        $guest_names = $_POST['guest_name'];
        $guest_id_proof_types = $_POST['guest_id_proof_type'];
        $guest_id_proof_numbers = $_POST['guest_id_proof_number'];
        $guest_ages = $_POST['guest_age'];
        $guest_states = $_POST['guest_state'];
        $guest_cities = $_POST['guest_city'];
        $guest_addresses = $_POST['guest_address'];
        $guest_pincodes = $_POST['guest_pincode'];
        $guest_genders = $_POST['guest_gender'];
        $guest_phone_numbers = $_POST['guest_phone_number'];
        $guest_emails = $_POST['guest_email'];
        $guest_id_proof_images = $_FILES['guest_id_proof_image'];

        for ($i = 0; $i < count($guest_names); $i++) {
            // Handle file upload
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($guest_id_proof_images['name'][$i]);
            move_uploaded_file($guest_id_proof_images['tmp_name'][$i], $target_file);

            $sql = "INSERT INTO guests (booking_id, name, id_proof_type, id_proof_number, age, state, city, address, pincode, gender, phone_number, email, id_proof_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssissssssss", $booking_id, $guest_names[$i], $guest_id_proof_types[$i], $guest_id_proof_numbers[$i], $guest_ages[$i], $guest_states[$i], $guest_cities[$i], $guest_addresses[$i], $guest_pincodes[$i], $guest_genders[$i], $guest_phone_numbers[$i], $guest_emails[$i], $target_file);
            $stmt->execute();
        }

        // Booking successful
        echo "<script>alert('Room booked successfully!'); window.location.href='welcome.php';</script>";
    } else {
        // Booking failed
        echo "<script>alert('Error: " . $stmt->error . "'); window.location.href='welcome.php';</script>";
    }

    $stmt->close();
}

$conn->close();
?>

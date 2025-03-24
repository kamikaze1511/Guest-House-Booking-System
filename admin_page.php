<?php
session_start();
include_once 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch total rooms from room_management table
$sql = "SELECT total_rooms FROM room_management LIMIT 1";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_rooms = $row['total_rooms'];

// Fetch all bookings
$sql = "SELECT b.id, b.employee_id, u.name, b.room_count, b.check_in_date, b.check_out_date 
        FROM bookings b 
        JOIN users u ON b.employee_id = u.employee_id 
        WHERE b.check_out_date >= CURDATE()";
$bookings_result = $conn->query($sql);

// Fetch total rooms booked
$sql = "SELECT SUM(room_count) AS booked_rooms FROM bookings WHERE check_out_date >= CURDATE()";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$booked_rooms = $row['booked_rooms'] ?: 0; // Handle null case
$available_rooms = $total_rooms - $booked_rooms;

$search_result = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_date'])) {
    $search_date = $_POST['search_date'];

    // Fetch bookings by date
    $sql = "SELECT b.id, b.employee_id, u.name, b.room_count, b.check_in_date, b.check_out_date 
            FROM bookings b 
            JOIN users u ON b.employee_id = u.employee_id 
            WHERE b.check_in_date <= ? AND b.check_out_date >= ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $search_date, $search_date);
    $stmt->execute();
    $search_result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <link rel="stylesheet" href="adminstyles.css">
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="logo.jpg" alt="Company Logo">
        </div>
        <h2 style=" font-family: 'Times New Roman', Times, serif;">Admin Panel</h2>
        <hr>

        <h3>Manage Rooms</h3>
        <p>Available Rooms: <?php echo $available_rooms; ?></p>
        <form action="update_rooms.php" method="POST">
            <div class="form-group">
                <label for="total_rooms">Total Number of Rooms: <input type="number" id="total_rooms" name="total_rooms" value="<?php echo $total_rooms; ?>" required></label>
            </div>
            <div class="form-group">
                <button type="submit">Update Rooms</button>
            </div>
        </form>

        <h3>Current Bookings</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Number of Rooms</th>
                        <th>Check-in Date</th>
                        <th>Check-out Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $booking['id']; ?></td>
                        <td><?php echo $booking['employee_id']; ?></td>
                        <td><?php echo $booking['name']; ?></td>
                        <td><?php echo $booking['room_count']; ?></td>
                        <td><?php echo $booking['check_in_date']; ?></td>
                        <td><?php echo $booking['check_out_date']; ?></td>
                        <td>
                            <form action="admin_cancel_booking.php" method="POST" style="display:inline;">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                <button type="submit" class="cancel-button">Cancel</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <h3>Search Bookings by Date</h3>
        <form action="admin_page.php" method="POST">
            <div class="form-group">
                <label for="search_date">Date: <input type="date" id="search_date" name="search_date" required></label>
            </div>
            <div class="form-group">
                <button type="submit">Search</button>
            </div>
        </form>

        <?php if (!empty($search_result)): ?>
        <h3>Search Results</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Number of Rooms</th>
                        <th>Check-in Date</th>
                        <th>Check-out Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($search_result as $booking): ?>
                    <tr>
                        <td><?php echo $booking['id']; ?></td>
                        <td><?php echo $booking['employee_id']; ?></td>
                        <td><?php echo $booking['name']; ?></td>
                        <td><?php echo $booking['room_count']; ?></td>
                        <td><?php echo $booking['check_in_date']; ?></td>
                        <td><?php echo $booking['check_out_date']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <div class="nav-bar">
            <a href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>

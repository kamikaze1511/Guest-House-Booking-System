<?php
session_start();
include_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['employee_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch employee's name and ID from the database
$employee_id = $_SESSION['employee_id'];
$sql = "SELECT name, employee_id FROM users WHERE employee_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row) {
    $employee_name = $row['name'];
    $employee_id = $row['employee_id'];
} else {
    $employee_name = "Unknown";
    $employee_id = "Unknown";
}

$stmt->close();

// Fetch total rooms from room_management table
$sql = "SELECT total_rooms FROM room_management LIMIT 1";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_rooms = $row['total_rooms'];

// Fetch total rooms booked
$sql = "SELECT SUM(room_count) AS booked_rooms FROM bookings WHERE check_out_date >= CURDATE()";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$booked_rooms = $row['booked_rooms'] ?: 0; // Handle null case
$available_rooms = $total_rooms - $booked_rooms;

// Fetch the number of rooms allotted to the logged-in employee
$sql = "SELECT SUM(room_count) AS employee_rooms FROM bookings WHERE employee_id = ? AND check_out_date >= CURDATE()";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$employee_rooms = $row['employee_rooms'] ?: 0; // Handle null case

$stmt->close();

// Fetch current bookings for the logged-in employee
$sql = "SELECT id, room_count, check_in_date, check_out_date FROM bookings WHERE employee_id = ? AND check_out_date >= CURDATE()";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$bookings_result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Page</title>
    <link rel="stylesheet" href="welcom.css">
    <script>
        function updatePrice() {
            var roomCount = document.getElementById('room_count').value;
            var checkInDate = new Date(document.getElementById('check_in_date').value);
            var checkOutDate = new Date(document.getElementById('check_out_date').value);
            var priceElement = document.getElementById('price');

            var oneDay = 24 * 60 * 60 * 1000; // hours * minutes * seconds * milliseconds
            var diffDays = Math.round(Math.abs((checkOutDate - checkInDate) / oneDay)) || 1; // Minimum 1 day

            var totalPrice = 200 * roomCount * diffDays;
            priceElement.textContent = 'Rs ' + totalPrice;
        }

        function addGuestFields() {
            var numberOfGuests = document.getElementById('number_of_guests').value;
            var guestDetailsContainer = document.getElementById('guest_details');

            // Clear previous fields
            guestDetailsContainer.innerHTML = '';

            for (var i = 0; i < numberOfGuests; i++) {
                var guestNumber = i + 1;
                guestDetailsContainer.innerHTML += `
                    <h4>Guest ${guestNumber}</h4>
                    <div class="form-group">
                        <label for="guest_name_${guestNumber}">Name: <input type="text" id="guest_name_${guestNumber}" name="guest_name[]" required></label>
                        
                    </div>
                    <div class="form-group">
                        <label for="guest_id_proof_type_${guestNumber}">ID Proof Type: <select id="guest_id_proof_type_${guestNumber}" name="guest_id_proof_type[]">
                            <option value="Driving License">Driving License</option>
                            <option value="Aadhar Card">Aadhar Card</option>
                            <option value="Voter ID">Voter ID</option>
                        </select></label>
                    </div>
                    <div class="form-group">
                        <label for="guest_id_proof_number_${guestNumber}">ID Proof Number: <input type="text" id="guest_id_proof_number_${guestNumber}" name="guest_id_proof_number[]" required></label>
                        
                    </div>
                    <div class="form-group">
                        <label for="guest_id_proof_photo_${guestNumber}">ID Proof Photo: <input type="file" id="guest_id_proof_photo_${guestNumber}" name="guest_id_proof_photo[]" required></label>
                        
                    </div>
                    <div class="form-group">
                        <label for="guest_age_${guestNumber}">Age: <input type="number" id="guest_age_${guestNumber}" name="guest_age[]" required></label>
                        
                    </div>
                    <div class="form-group">
                        <label for="guest_state_${guestNumber}">State: <input type="text" id="guest_state_${guestNumber}" name="guest_state[]" required></label>
                       
                    </div>
                    <div class="form-group">
                        <label for="guest_city_${guestNumber}">City: <input type="text" id="guest_city_${guestNumber}" name="guest_city[]" required></label>
                       
                    </div>
                    <div class="form-group">
                        <label for="guest_address_${guestNumber}">Address: <textarea id="guest_address_${guestNumber}" name="guest_address[]" required></textarea></label>
                        
                    </div>
                    <div class="form-group">
                        <label for="guest_pincode_${guestNumber}">Pincode: <input type="text" id="guest_pincode_${guestNumber}" name="guest_pincode[]" required></label>
                        
                    </div>
                    <div class="form-group">
                        <label for="guest_gender_${guestNumber}">Gender: <select id="guest_gender_${guestNumber}" name="guest_gender[]">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select></label>
                        
                    </div>
                    <div class="form-group">
                        <label for="guest_phone_number_${guestNumber}">Phone Number: <input type="text" id="guest_phone_number_${guestNumber}" name="guest_phone_number[]" required></label>
                        
                    </div>
                    <div class="form-group">
                        <label for="guest_email_${guestNumber}">Email ID: <input type="email" id="guest_email_${guestNumber}" name="guest_email[]" required></label>
                        
                    </div>
                `;
            }
        }
    </script>
</head>
<body>
  <body>
    <div class="navbar">
        <div class="container">
            <div class="navbar-left">
                <img src="logo.jpg" alt="Company Logo" class="navbar-logo">
                <span class="navbar-title">Power Grid</span>
            </div>
            <div class="navbar-right employee-info">
                <p>Employee Name:<?php echo htmlspecialchars($employee_name); ?></p>
                <p>Employee ID:<?php echo htmlspecialchars($employee_id); ?></p>
            </div>
        </div>
    </div>
   

    <div class="container">
        <h2 style=" font-family: 'Times New Roman', Times, serif;">Guest House Booking Space</h2>
        <hr>
        <form action="booking_process.php" method="POST" enctype="multipart/form-data">
            <div class="employee-info">
                
                <p>Rooms Allotted to You: <?php echo $employee_rooms; ?></p>
            </div>
            <div class="form-group">
                <label for="available_rooms">Available Rooms: <?php echo $available_rooms; ?></label>
                
            </div>
            <div class="form-group">
                <label for="room_count">Number of Rooms: <input type="number" id="room_count" name="room_count" min="1" max="<?php echo $available_rooms; ?>" value="1" onchange="updatePrice()" required></label>
                
            </div>
            <div class="form-group">
                <label for="check_in_date">Check-in Date: <input type="date" id="check_in_date" name="check_in_date" onchange="updatePrice()" required></label>
                
            </div>
            <div class="form-group">
                <label for="check_out_date">Check-out Date: <input type="date" id="check_out_date" name="check_out_date" onchange="updatePrice()" required></label>
                
            </div>
            <div class="form-group">
                <label>Price: <span id="price">Rs 200</span></label>
            </div>
            <div class="form-group">
                <label for="number_of_guests">Number of Persons Staying: <input type="number" id="number_of_guests" name="number_of_guests" min="1" value="0" onchange="addGuestFields()" required></label>
                
            </div>
            <div id="guest_details"></div>
            <div class="form-group">
                <button type="submit">Book Room</button>
            </div>
        </form>
        
        <h3>Your Current Bookings</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Number of Rooms</th>
                        <th>Check-in Date</th>
                        <th>Check-out Date</th>
                        <th>Total Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $booking['id']; ?></td>
                        <td><?php echo $booking['room_count']; ?></td>
                        <td><?php echo $booking['check_in_date']; ?></td>
                        <td><?php echo $booking['check_out_date']; ?></td>
                        <td><?php echo 'Rs ' . (200 * $booking['room_count'] * (strtotime($booking['check_out_date']) - strtotime($booking['check_in_date'])) / (60 * 60 * 24)); ?></td>
                        <td>
                            <form action="cancel_booking.php" method="POST" style="display:inline;">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                <button type="submit" class="cancel-button">Cancel</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="nav-bar">
            <a href="logout.php">Logout</a>
            <p><a href="help.php">Help</a></p>
        </div>
    </div>
</body>
</html>

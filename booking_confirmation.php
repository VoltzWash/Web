<?php
session_start();
$conn = new mysqli("localhost", "root", "", "car_rental");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure booking ID is available in the session
if (!isset($_SESSION['booking_id'])) {
    die("Booking ID is missing.");
}

$booking_id = $_SESSION['booking_id'];  // Retrieve the booking ID from the session

// Fetch booking details
$sql = "SELECT Bookings.*, Cars.Make, Cars.Model, Cars.RegistrationNumber, Cars.DailyRate, Users.Name AS OwnerName 
        FROM Bookings 
        JOIN Cars ON Bookings.CarID = Cars.CarID
        JOIN Users ON Cars.OwnerID = Users.UserID
        WHERE Bookings.BookingID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking_result = $stmt->get_result();
$booking = $booking_result->fetch_assoc();

if (!$booking) {
    die("Booking not found.");
}

// Calculate the total rental days
$date1 = new DateTime($booking['StartDate']);
$date2 = new DateTime($booking['EndDate']);
$days = $date1->diff($date2)->days;

// Calculate the total price by multiplying the number of days by the daily rate
$total_price = $days * $booking['DailyRate'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #2c3e50;
            text-align: center;
        }

        p {
            font-size: 16px;
            line-height: 1.5;
        }

        .details {
            margin-top: 20px;
            border-top: 2px solid #ecf0f1;
            padding-top: 20px;
        }

        .details p {
            margin: 10px 0;
        }

        .details strong {
            color: #2980b9;
        }

        .price {
            font-size: 18px;
            color: #27ae60;
            font-weight: bold;
            margin-top: 10px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 20px;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .back-link {
            display: inline-block;
            margin-top: 10px;
            font-size: 14px;
            color: #3498db;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Booking Confirmed</h2>
    <p>Your booking has been successfully placed, but payment is pending.</p>

    <div class="details">
        <h3>Booking Details</h3>
        <p><strong>Car:</strong> <?php echo htmlspecialchars($booking['Make'] . " " . $booking['Model']); ?></p>
        <p><strong>Owner:</strong> <?php echo htmlspecialchars($booking['OwnerName']); ?></p>
        <p><strong>Start Date:</strong> <?php echo htmlspecialchars($booking['StartDate']); ?></p>
        <p><strong>End Date:</strong> <?php echo htmlspecialchars($booking['EndDate']); ?></p>
        <p class="price"><strong>Total Price:</strong> ZAR <?php echo number_format($total_price, 2); ?></p>

        <!-- Redirect to payment page with necessary details -->
        <form method="POST" action="payment.php">
            <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
            <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">
            <button type="submit" class="btn">Proceed to Payment</button>
        </form>
    </div>

    <a href="view_cars.php" class="back-link">Back to Available Cars</a>
</div>

</body>
</html>

<?php
$conn->close();
?>

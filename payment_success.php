<?php
session_start();
$conn = new mysqli("localhost", "root", "", "car_rental");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure booking ID and total price are available
if (!isset($_POST['booking_id']) || !isset($_POST['total_price'])) {
    die("Booking details are missing.");
}

$booking_id = $_POST['booking_id'];  // Retrieve the booking ID from the POST request
$total_price = $_POST['total_price']; // Retrieve the total price from the POST request

// Update the payment status and car availability
$sql = "UPDATE Bookings SET Status = 'Paid' WHERE BookingID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();

// Update car availability to 'Booked'
$sql = "UPDATE Cars SET Availability = 'Booked' WHERE CarID = (SELECT CarID FROM Bookings WHERE BookingID = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
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
    <h2>Payment Successful</h2>
    <p>Your payment has been successfully processed. Your booking is now confirmed and the car has been marked as booked.</p>

    <a href="view_cars.php" class="btn">Back to Available Cars</a>
</div>

</body>
</html>

<?php
$conn->close();
?>

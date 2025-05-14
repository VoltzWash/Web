<?php
session_start();
$conn = new mysqli("localhost", "root", "", "car_rental");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure a car is selected
if (!isset($_GET['car_id'])) {
    die("Car ID is required.");
}

$car_id = intval($_GET['car_id']);

// Fetch car details
$sql = "SELECT Cars.*, Users.Name AS OwnerName FROM Cars 
        JOIN Users ON Cars.OwnerID = Users.UserID 
        WHERE Cars.CarID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $car_id);
$stmt->execute();
$car_result = $stmt->get_result();
$car = $car_result->fetch_assoc();

if (!$car) {
    die("Car not found.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Validate input
    if (empty($name) || empty($email) || empty($phone) || empty($start_date) || empty($end_date)) {
        echo "All fields are required.";
        exit;
    }

    // Check if customer already exists
    $customer_sql = "SELECT UserID FROM Users WHERE Email = ?";
    $stmt = $conn->prepare($customer_sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $customer_result = $stmt->get_result();
    $customer = $customer_result->fetch_assoc();

    if ($customer) {
        $customer_id = $customer['UserID'];
    } else {
        // Insert new customer (no password required)
        $insert_customer_sql = "INSERT INTO Users (Name, Email, Phone, UserType) VALUES (?, ?, ?, 'Customer')";
        $stmt = $conn->prepare($insert_customer_sql);
        $stmt->bind_param("sss", $name, $email, $phone);
        if ($stmt->execute()) {
            $customer_id = $stmt->insert_id;
        } else {
            die("Error adding customer.");
        }
    }

    // Calculate total price
    $date1 = new DateTime($start_date);
    $date2 = new DateTime($end_date);
    $days = $date1->diff($date2)->days;
    $total_price = $days * $car['DailyRate'];

    // Insert booking
    $insert_booking_sql = "INSERT INTO Bookings (RenterID, CarID, StartDate, EndDate, TotalPrice) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_booking_sql);
    $stmt->bind_param("iissd", $customer_id, $car_id, $start_date, $end_date, $total_price);

    if ($stmt->execute()) {
        // Store the booking ID in the session
        $_SESSION['booking_id'] = $stmt->insert_id;  // Store the new booking ID

        header("Location: booking_confirmation.php");
        exit;
    } else {
        die("Error processing booking.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Car - Car Rental Platform</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            text-align: center;
            position: relative;
        }
        header .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header .header-container h1 {
            margin: 0;
        }
        header .header-container a button {
            background-color: #008CBA;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        header .header-container a button:hover {
            background-color: #006f8e;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 10px;
            position: relative;
            bottom: 0;
            width: 100%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<header>
    <div class="header-container">
        <h1>Book Car - <?php echo htmlspecialchars($car['Make'] . " " . $car['Model']); ?></h1>
        <a href="view_cars.php">
            <button>View Cars</button>
        </a>
    </div>
</header>

<div class="container">
    <h3>Car Details</h3>
    <table>
        <tr>
            <th>Owner</th>
            <td><?php echo htmlspecialchars($car['OwnerName']); ?></td>
        </tr>
        <tr>
            <th>Make</th>
            <td><?php echo htmlspecialchars($car['Make']); ?></td>
        </tr>
        <tr>
            <th>Model</th>
            <td><?php echo htmlspecialchars($car['Model']); ?></td>
        </tr>
        <tr>
            <th>Year</th>
            <td><?php echo htmlspecialchars($car['Year']); ?></td>
        </tr>
        <tr>
            <th>Registration</th>
            <td><?php echo htmlspecialchars($car['RegistrationNumber']); ?></td>
        </tr>
        <tr>
            <th>Daily Rate</th>
            <td>ZAR <?php echo number_format($car['DailyRate'], 2); ?></td>
        </tr>
        <tr>
            <th>Location</th>
            <td><?php echo htmlspecialchars($car['Location']); ?></td>
        </tr>
    </table>

    <h3>Enter Your Details</h3>
    <form method="POST">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" required>

        <label for="start_date">Start Date</label>
        <input type="date" id="start_date" name="start_date" required>

        <label for="end_date">End Date</label>
        <input type="date" id="end_date" name="end_date" required>

        <button type="submit">Confirm Booking</button>
    </form>
</div>

<footer>
    <p>&copy; 2025 Car Rental Platform. All rights reserved.</p>
</footer>

</body>
</html>

<?php
$conn->close();
?>

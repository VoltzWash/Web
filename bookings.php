<?php
// Start session to get user data
session_start();

// Check if the user is logged in, if not, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Regenerate session ID for security
session_regenerate_id(true);

// Get user data
$user_id = $_SESSION['user_id']; // Logged-in user's ID

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_rental";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle booking status update (if needed)
if (isset($_POST['confirm_booking'])) {
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;

    if ($booking_id > 0) {
        $sql_update_status = "UPDATE Bookings SET Status = 'Paid' WHERE BookingID = ?";
        $stmt = $conn->prepare($sql_update_status);
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $stmt->close();

        // Redirect to refresh the page and show the updated status
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Pagination setup
$limit = 5; // Number of bookings per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Get the total number of bookings for pagination
$sql_count = "SELECT COUNT(*) AS total FROM Bookings b JOIN Cars c ON b.CarID = c.CarID WHERE c.OwnerID = ?";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("i", $user_id);
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$total_bookings = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_bookings / $limit);

// Fetch bookings for cars owned by the logged-in owner
$sql_bookings = "
    SELECT b.*, 
           c.Make, c.Model, c.RegistrationNumber, 
           u.Name AS RenterName, u.Email AS RenterEmail, u.Phone AS RenterPhone
    FROM Bookings b
    JOIN Cars c ON b.CarID = c.CarID
    JOIN Users u ON b.RenterID = u.UserID
    WHERE c.OwnerID = ?
    LIMIT ? OFFSET ?";  // Apply limit and offset for pagination

$stmt = $conn->prepare($sql_bookings);
$stmt->bind_param("iii", $user_id, $limit, $offset);  // Bind owner user_id, limit, and offset
$stmt->execute();
$bookings_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings - Car Rental Platform</title>
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
        .header-buttons {
            position: absolute;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
        }
        .header-buttons form {
            display: inline-block;
        }
        .header-buttons button {
            padding: 10px 20px;
            background-color: #fff;
            color: #4CAF50;
            border: 1px solid #4CAF50;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }
        .header-buttons button:hover {
            background-color: #4CAF50;
            color: white;
        }
        .container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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
        .bookings-list {
            margin-top: 20px;
        }
        .bookings-list table {
            width: 100%;
            border-collapse: collapse;
        }
        .bookings-list th, .bookings-list td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .bookings-list th {
            background-color: #f2f2f2;
        }
        .dashboard-links a {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-right: 10px;
        }
        .dashboard-links a:hover {
            background-color: #45a049;
        }
        
        /* Styling for the Confirm button */
        .confirm-button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: 1px solid #4CAF50;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
        }

        .confirm-button:hover {
            background-color: #45a049;
            border-color: #45a049;
        }

        /* Pagination styling */
        .pagination {
            text-align: center;
            margin-top: 20px;
        }
        .pagination a {
            padding: 8px 16px;
            margin: 0 5px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .pagination a:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<header>
    <h1>Your Bookings</h1>
    <div class="header-buttons">
        <form method="POST" action="">
            <button type="submit" name="logout">Logout</button>
        </form>
    </div>
</header>

<div class="container">
    <?php if ($bookings_result->num_rows > 0): ?>
        <div class="bookings-list">
            <table>
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Car</th>
                        <th>Registration</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Total Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['RenterName']); ?></td>
                            <td><?php echo htmlspecialchars($booking['RenterEmail']); ?></td>
                            <td><?php echo htmlspecialchars($booking['RenterPhone']); ?></td>
                            <td><?php echo htmlspecialchars($booking['Make'] . " " . $booking['Model']); ?></td>
                            <td><?php echo htmlspecialchars($booking['RegistrationNumber']); ?></td>
                            <td><?php echo htmlspecialchars($booking['StartDate']); ?></td>
                            <td><?php echo htmlspecialchars($booking['EndDate']); ?></td>
                            <td><?php echo htmlspecialchars($booking['Status']); ?></td>
                            <td><?php echo htmlspecialchars($booking['TotalPrice']); ?> ZAR</td>
                            <td>
                                <?php if ($booking['Status'] != 'Paid'): ?>
                                    <form method="POST" action="">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['BookingID']; ?>">
                                        <button type="submit" name="confirm_booking" class="confirm-button">Confirm</button>
                                    </form>
                                <?php else: ?>
                                    <span>Confirmed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No bookings yet.</p>
    <?php endif; ?>

    <!-- Pagination Controls -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>">Prev</a>
        <?php endif; ?>
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>">Next</a>
        <?php endif; ?>
    </div>

    <div class="dashboard-links">
        <a href="dashboard.php">Back to Dashboard</a>
    </div>
</div>

<footer>
    <p>&copy; 2025 Car Rental Platform. All rights reserved.</p>
</footer>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>

<?php
session_start(); // Start the session to maintain user login state

// Check if the user is logged in, else redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the logged-in user ID from the session
$user_id = $_SESSION['user_id'];

// Handle the logout process
if (isset($_POST['logout'])) {
    session_destroy();  // Destroy session to log out the user
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "car_rental");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Pagination logic
$limit = 5;  // Number of cars per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;  // Current page (default: 1)
$offset = ($page - 1) * $limit;  // Calculate the offset

// Fetch the user details using prepared statements
$stmt = $conn->prepare("SELECT * FROM Users WHERE UserID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle updating car availability securely with prepared statements
if (isset($_POST['edit_availability'])) {
    $car_id = $_POST['car_id'];
    $availability = $_POST['availability'];

    $update_stmt = $conn->prepare("UPDATE Cars SET Availability = ? WHERE CarID = ? AND OwnerID = ?");
    $update_stmt->bind_param("sii", $availability, $car_id, $user_id);
    $update_stmt->execute();
}

// Handle removing a car securely with prepared statements
if (isset($_POST['remove_car'])) {
    $car_id = $_POST['car_id'];

    $delete_stmt = $conn->prepare("DELETE FROM Cars WHERE CarID = ? AND OwnerID = ?");
    $delete_stmt->bind_param("ii", $car_id, $user_id);
    $delete_stmt->execute();
}

// Fetch the cars registered by the logged-in user with pagination
$sql_cars = "SELECT * FROM Cars WHERE OwnerID = ? LIMIT ? OFFSET ?";
$cars_stmt = $conn->prepare($sql_cars);
$cars_stmt->bind_param("iii", $user_id, $limit, $offset);
$cars_stmt->execute();
$cars_result = $cars_stmt->get_result();

// Fetch total number of cars for pagination
$sql_count = "SELECT COUNT(*) FROM Cars WHERE OwnerID = ?";
$count_stmt = $conn->prepare($sql_count);
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_cars = $count_result->fetch_row()[0];

// Calculate total pages
$total_pages = ceil($total_cars / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Car Rental Platform</title>
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
        .header-buttons button {
            padding: 8px 15px;
            background-color: white;
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
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .dashboard-links {
            margin-top: 20px;
        }
        .dashboard-links a {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-right: 10px;
            display: inline-block;
        }
        .dashboard-links a:hover {
            background-color: #45a049;
        }
        .cars-list {
            margin-top: 20px;
        }
        .cars-list table {
            width: 100%;
            border-collapse: collapse;
        }
        .cars-list th, .cars-list td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .cars-list th {
            background-color: #f2f2f2;
        }
        .form-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-select {
            padding: 8px;
            font-size: 14px;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: white;
        }
        .form-submit {
            padding: 8px 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }
        .form-submit:hover {
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
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination a {
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 0 5px;
        }
        .pagination a:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<header>
    <h1>Welcome, <?php echo htmlspecialchars($user['Name']); ?></h1>
    <div class="header-buttons">
        <form method="POST" action="">
            <button type="submit" name="logout">Logout</button>
        </form>
    </div>
</header>

<div class="container">
    <h2>Your Cars</h2>

    <?php if ($cars_result->num_rows > 0): ?>
        <div class="cars-list">
            <table>
                <thead>
                    <tr>
                        <th>Make</th>
                        <th>Model</th>
                        <th>Year</th>
                        <th>Registration</th>
                        <th>Availability</th>
                        <th>Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($car = $cars_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($car['Make']); ?></td>
                            <td><?php echo htmlspecialchars($car['Model']); ?></td>
                            <td><?php echo htmlspecialchars($car['Year']); ?></td>
                            <td><?php echo htmlspecialchars($car['RegistrationNumber']); ?></td>
                            <td><?php echo htmlspecialchars($car['Availability']); ?></td>
                            <td><?php echo htmlspecialchars($car['Location']); ?></td>
                            <td>
                                <form method="POST" action="" class="form-group">
                                    <select name="availability" class="form-select">
                                        <option value="Available" <?php echo ($car['Availability'] == 'Available') ? 'selected' : ''; ?>>Available</option>
                                        <option value="Booked" <?php echo ($car['Availability'] == 'Booked') ? 'selected' : ''; ?>>Booked</option>
                                        <option value="Unavailable" <?php echo ($car['Availability'] == 'Unavailable') ? 'selected' : ''; ?>>Unavailable</option>
                                    </select>
                                    <input type="hidden" name="car_id" value="<?php echo $car['CarID']; ?>">
                                    <button type="submit" name="edit_availability" class="form-submit">Update</button>
                                    <button type="submit" name="remove_car" class="form-submit" onclick="return confirm('Are you sure?');">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>You have not added any cars yet.</p>
    <?php endif; ?>

    <!-- Pagination Links -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>">Previous</a>
        <?php endif; ?>
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>">Next</a>
        <?php endif; ?>
    </div>

    <div class="dashboard-links">
        <a href="add_car.php">Add a Car</a>
        <a href="bookings.php">Bookings</a>
    </div>
</div>

<footer>
    <p>&copy; 2025 Car Rental Platform. All rights reserved.</p>
</footer>

</body>
</html>

<?php
// Close the prepared statements and connection
$stmt->close();
$cars_stmt->close();
$count_stmt->close();
$conn->close();
?>

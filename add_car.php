<?php
// Start session to get user data
session_start();

// Check if the user is logged in, if not, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id']; // Logged-in user's ID

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost"; // Change if your DB server is different
$username = "root"; // Database username
$password = ""; // Database password
$dbname = "car_rental"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form values and sanitize input
    $make = mysqli_real_escape_string($conn, $_POST['make']);
    $model = mysqli_real_escape_string($conn, $_POST['model']);
    $year = (int) $_POST['year'];
    $reg_number = mysqli_real_escape_string($conn, $_POST['reg_number']);
    $availability = mysqli_real_escape_string($conn, $_POST['availability']);
    $daily_rate = (float) $_POST['daily_rate'];
    $location = mysqli_real_escape_string($conn, $_POST['location']);

    // Basic validation
    if ($year < 1900 || $year > date("Y")) {
        $error = "Invalid year. Please enter a valid car year.";
    } elseif ($daily_rate <= 0) {
        $error = "Daily rate must be a positive number.";
    } elseif (empty($make) || empty($model) || empty($reg_number) || empty($location)) {
        $error = "All fields are required.";
    } else {
        // Prepared statement to insert car details into the database
        $stmt = $conn->prepare("INSERT INTO Cars (OwnerID, Make, Model, Year, RegistrationNumber, Availability, DailyRate, Location) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssis", $user_id, $make, $model, $year, $reg_number, $availability, $daily_rate, $location);

        // Execute and check for success
        if ($stmt->execute()) {
            // Redirect to the dashboard after adding the car
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Error: " . $conn->error;
        }
        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Car - Car Rental Platform</title>
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
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
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
        .dashboard-links {
            margin-top: 20px;
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
        input, select {
            padding: 10px;
            width: 100%;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            font-size: 14px;
        }
    </style>
</head>
<body>

<header>
    <h1>Add a New Car</h1>
    <div class="header-buttons">
        <form method="POST" action="">
            <button type="submit" name="logout">Logout</button>
        </form>
    </div>
</header>

<div class="container">
    <h2>Enter Car Details</h2>

    <?php
    if (isset($error)) {
        echo "<p class='error'>$error</p>";
    }
    ?>

    <form method="POST" action="">
        <input type="text" name="make" placeholder="Car Make" required><br>
        <input type="text" name="model" placeholder="Car Model" required><br>
        <input type="number" name="year" placeholder="Car Year" required><br>
        <input type="text" name="reg_number" placeholder="Registration Number" required><br>
        <select name="availability" required>
            <option value="Available">Available</option>
            <option value="Booked">Booked</option>
            <option value="Unavailable">Unavailable</option>
        </select><br>
        <input type="number" step="0.01" name="daily_rate" placeholder="Daily Rate (ZAR)" required><br>
        <input type="text" name="location" placeholder="Car Location" required><br>

        <button type="submit">Add Car</button>
    </form>

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

<?php
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if user exists
    $sql = "SELECT * FROM Users WHERE Email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // User exists, now check password
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['Password'])) {
            // Start session and redirect to home page
            session_start();
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['name'] = $user['Name'];
            header("Location: dashboard.php"); // Redirect to home page after successful login
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "No user found with that email address";
    }

    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Car Rental Platform</title>
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
        .header-buttons a {
            margin-left: 10px;
            padding: 10px 20px;
            background-color: #fff;
            color: #4CAF50;
            text-decoration: none;
            border-radius: 5px;
            border: 1px solid #4CAF50;
            font-weight: bold;
        }
        .header-buttons a:hover {
            background-color: #4CAF50;
            color: white;
        }
        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 80px; /* Prevent the footer from overlapping */
        }
        h2 {
            text-align: center;
        }
        .form-group {
            margin: 15px 0;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
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
    </style>
</head>
<body>

<header>
    <h1>Car Rental Platform - Login</h1>
    <div class="header-buttons">
        <a href="index.html">Home</a>
        <a href="view_cars.php">View Cars</a>
        <a href="register.php">Register</a>
    </div>
</header>

<div class="container">
    <h2>Login</h2>
    <form method="POST" action="">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <input type="submit" value="Login">
    </form>
</div>

<footer>
    <p>&copy; 2025 Car Rental Platform. All rights reserved.</p>
</footer>

</body>
</html>

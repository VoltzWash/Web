<?php
// Database connection
$servername = "localhost"; // Change if needed
$username = "root";
$password = "";
$dbname = "car_rental";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $userType = $_POST['userType']; // Get user type from form

    // Validate password length
    if (strlen($password) < 6) {
        echo "<script>alert('Password must be at least 6 characters long.');</script>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash password

        // Check if email already exists
        $stmt = $conn->prepare("SELECT Email FROM Users WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "<script>alert('Error: This email is already registered.');</script>";
        } else {
            // Insert user into database
            $stmt = $conn->prepare("INSERT INTO Users (Name, Email, Phone, Password, UserType) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $phone, $hashed_password, $userType);

            if ($stmt->execute()) {
                header("Location: login.php"); // Redirect after successful registration
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Car Rental Platform</title>
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
            margin-bottom: 80px;
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
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
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
    </style>
    <script>
        function validateForm() {
            let password = document.getElementById("password").value;
            if (password.length < 6) {
                alert("Password must be at least 6 characters long.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

<header>
    <h1>Car Rental Platform - Registration</h1>
    <div class="header-buttons">
        <a href="index.html">Home</a>
        <a href="login.php">Login</a>
        <a href="view_cars.php">View Cars</a>
    </div>
</header>

<div class="container">
    <h2>Register</h2>
    <form method="POST" action="" onsubmit="return validateForm()">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="userType">Register As:</label>
            <select id="userType" name="userType">
                <option value="Customer">Customer</option>
                <option value="Owner">Car Owner</option>
            </select>
        </div>
        <input type="submit" value="Register">
    </form>
</div>

<footer>
    <p>&copy; <span id="year"></span> Car Rental Platform. All rights reserved.</p>
</footer>

<script>
    document.getElementById("year").textContent = new Date().getFullYear();
</script>

</body>
</html>
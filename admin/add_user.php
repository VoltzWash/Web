<?php
// Establish a database connection
$conn = new mysqli("localhost", "root", "", "car_rental");

// Check if the connection works
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $userType = $_POST['user_type'];

    // Store the user information in the Users table
    $sql = "INSERT INTO Users (Name, Email, Phone, Password, UserType) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $name, $email, $phone, $password, $userType);

    if ($stmt->execute()) {
        echo "<script>alert('User added successfully'); window.location.href = 'admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .container {
            width: 50%;
            margin: 50px auto;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .container h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            font-size: 16px;
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-group select {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            color: #333;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }
        .form-group select:focus {
            border-color: #007bff;
            outline: none;
            background-color: #fff;
        }
        .form-group select:hover {
            border-color: #0056b3;
        }
        .btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 18px;
            display: block;
            width: 100%;
            text-align: center;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .back-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 18px;
            display: block;
            text-align: center;
        }
        .back-btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<header>
    <h1>Add New User</h1>
</header>

<div class="container">
    <h2>User Information</h2>

    <form action="add_user.php" method="POST">
        <!-- Get the fullname of the user. -->
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" required>
        </div>

        <!-- Get the email address of the user -->
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>
        </div>

        <!-- Get the phone number of the user -->
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" name="phone" id="phone" required>
        </div>

        <!-- Get the password of the user -->
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
        </div>

        <!-- Is the user an Admin, Owner, or Customer -->
        <div class="form-group">
            <label for="user_type">User Type</label>
            <select name="user_type" id="user_type" required>
                <option value="Admin">Admin</option>
                <option value="Owner">Owner</option>
                <option value="Customer">Customer</option>
            </select>
        </div>

        <!-- After getting the user data sumbit the form -->
        <button type="submit" class="btn">Add User</button>
    </form>

    <!-- Return to the dashboard should you require to do using this button -->
    <a href="admin_dashboard.php" class="back-btn">Back to Dashboard</a>
</div>

</body>
</html>

<?php
$conn->close();
?>

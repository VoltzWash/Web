<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "car_rental");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the user details based on the ID passed in the URL
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $sql = "SELECT * FROM Users WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
} else {
    die("User ID not provided");
}

// Update user information if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $userType = $_POST['user_type'];

    // Update the database with new user information
    $sql = "UPDATE Users SET Name = ?, Email = ?, Phone = ?, UserType = ? WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $name, $email, $phone, $userType, $user_id);
	
	// Display a success message after the information has been updated
    if ($stmt->execute()) {
        echo "<script>alert('User updated successfully'); window.location.href = 'admin_dashboard.php';</script>";
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
    <title>Edit User - Admin Dashboard</title>
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
    <h1>Edit User</h1>
</header>

<div class="container">
    <h2>Edit User Information</h2>
    <form action="edit_user.php?user_id=<?php echo $user['UserID']; ?>" method="POST">
        <!-- Name -->
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['Name']); ?>" required>
        </div>

        <!-- Email -->
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
        </div>

        <!-- Phone Number -->
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($user['Phone']); ?>" required>
        </div>

        <!-- User Type -->
        <div class="form-group">
            <label for="user_type">User Type</label>
            <select name="user_type" id="user_type" required>
                <option value="Admin" <?php echo ($user['UserType'] == 'Admin') ? 'selected' : ''; ?>>Admin</option>
                <option value="Owner" <?php echo ($user['UserType'] == 'Owner') ? 'selected' : ''; ?>>Owner</option>
                <option value="Customer" <?php echo ($user['UserType'] == 'Customer') ? 'selected' : ''; ?>>Customer</option>
            </select>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn">Update User</button>
    </form>

    <!-- Back to Dashboard Button -->
    <a href="admin_dashboard.php" class="back-btn">Back to Dashboard</a>
</div>

</body>
</html>

<?php
$conn->close();
?>

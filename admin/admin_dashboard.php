<?php
// Establish database connection
$conn = new mysqli("localhost", "root", "", "car_rental");

// Check if the connection works
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Read the user table and retrieve them.
$sql = "SELECT * FROM Users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
            width: 80%;
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
        .user-table {
            width: 100%;
            border-collapse: collapse;
        }
        .user-table th, .user-table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .user-table th {
            background-color: #f2f2f2;
        }
        .user-table td {
            font-size: 16px;
        }
        .btn {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .add-user-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            text-align: center;
            padding: 12px 0;
            border-radius: 5px;
            font-size: 18px;
        }
        .add-user-btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<header>
    <h1>Admin Dashboard</h1>
</header>

<div class="container">
    <h2>User Management</h2>

    <!-- Button to allow us to add new users -->
    <a href="add_user.php" class="add-user-btn">Add New User</a>

    <table class="user-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>User Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($user = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($user['Name']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['Email']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['Phone']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['UserType']) . "</td>";
                    echo "<td>
                            <a href='edit_user.php?user_id=" . $user['UserID'] . "' class='btn'>Edit</a>
                            <a href='delete_user.php?user_id=" . $user['UserID'] . "' class='btn' style='background-color: red;'>Delete</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No users found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html>

<?php
$conn->close();		// Close the database connection after.
?>

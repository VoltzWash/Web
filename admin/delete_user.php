<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "car_rental");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete user from the Users table
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Delete the user from the database
    $sql = "DELETE FROM Users WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php"); // Redirect to dashboard after deletion
        exit();
    } else {
        die("Error deleting user.");
    }
} else {
    die("User ID not specified.");
}

$conn->close();
?>

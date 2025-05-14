<?php
session_start();
$conn = new mysqli("localhost", "root", "", "car_rental");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Pagination settings
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch only available cars with owner details using prepared statement
$sql = "SELECT Cars.CarID, Cars.Make, Cars.Model, Cars.Year, Cars.RegistrationNumber, Cars.DailyRate, Cars.Location, Users.Name AS OwnerName 
        FROM Cars 
        JOIN Users ON Cars.OwnerID = Users.UserID 
        WHERE Cars.Availability = 'Available' 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$cars_result = $stmt->get_result();

// Get total number of cars for pagination
$total_sql = "SELECT COUNT(*) AS total FROM Cars WHERE Availability = 'Available'";
$total_result = $conn->query($total_sql);
$total_cars = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_cars / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Cars - Car Rental Platform</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f0f0f0; margin: 0; padding: 0; }
        header { background-color: #4CAF50; color: white; padding: 15px; text-align: center; position: relative; }
        .header-buttons { position: absolute; top: 50%; right: 20px; transform: translateY(-50%); }
        .header-buttons a { padding: 8px 15px; background-color: white; color: #4CAF50; border: 1px solid #4CAF50; border-radius: 5px; text-decoration: none; font-weight: bold; margin-left: 10px; }
        .header-buttons a:hover { background-color: #4CAF50; color: white; }
        .container { max-width: 1200px; margin: 50px auto; padding: 20px; display: flex; flex-wrap: wrap; justify-content: space-around; }
        .car-card { width: 280px; background-color: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); margin: 15px; padding: 20px; text-align: center; transition: transform 0.3s ease-in-out; }
        .car-card:hover { transform: translateY(-10px); }
        .car-card img { width: 100%; border-radius: 5px; height: 180px; object-fit: cover; }
        .car-card h3 { margin: 10px 0; color: #2c3e50; }
        .car-card p { font-size: 14px; color: #7f8c8d; }
        .price { font-size: 18px; color: #27ae60; font-weight: bold; margin: 10px 0; }
        .book-btn { padding: 8px 20px; background-color: #28a745; color: white; border: none; border-radius: 5px; text-decoration: none; font-size: 14px; cursor: pointer; }
        .book-btn:hover { background-color: #218838; }
        .pagination { text-align: center; margin-top: 20px; }
        .pagination a { padding: 8px 12px; margin: 0 5px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; }
        .pagination a:hover { background-color: #45a049; }
        footer { background-color: #333; color: white; text-align: center; padding: 10px; margin-top: 20px; }
    </style>
</head>
<body>

<header>
    <h1>Available Cars</h1>
    <div class="header-buttons">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="register.php">Register</a>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </div>
</header>

<div class="container">
    <?php if ($cars_result->num_rows > 0): ?>
        <?php while ($car = $cars_result->fetch_assoc()): ?>
            <div class="car-card">
                <img src="path_to_placeholder_image.jpg" alt="Car Image">
                <h3><?php echo htmlspecialchars($car['Make'] . " " . $car['Model']); ?></h3>
                <p><strong>Owner:</strong> <?php echo htmlspecialchars($car['OwnerName']); ?></p>
                <p><strong>Year:</strong> <?php echo htmlspecialchars($car['Year']); ?></p>
                <p><strong>Registration:</strong> <?php echo htmlspecialchars($car['RegistrationNumber']); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($car['Location']); ?></p>
                <p class="price">R<?php echo number_format($car['DailyRate'], 2); ?>/day</p>
                <a href="book_car.php?car_id=<?php echo $car['CarID']; ?>" class="book-btn">Book</a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No available cars at the moment.</p>
    <?php endif; ?>
</div>

<div class="pagination">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="view_cars.php?page=<?php echo $i; ?>"> <?php echo $i; ?> </a>
    <?php endfor; ?>
</div>

<footer>
    <p>&copy; 2025 Car Rental Platform. All rights reserved.</p>
</footer>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

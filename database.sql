-- Creating Database
CREATE DATABASE car_rental;

-- Select the database
USE car_rental;

-- Creating the Users table
-- To store the users
CREATE TABLE Users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    Phone VARCHAR(15),
    Password VARCHAR(255) NOT NULL,
	UserType ENUM('Admin', 'Owner', 'Customer') NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Creating the Cars table
-- To store the user's cars
CREATE TABLE Cars (
    CarID INT AUTO_INCREMENT PRIMARY KEY,
    OwnerID INT NOT NULL,
    Make VARCHAR(50) NOT NULL,
    Model VARCHAR(50) NOT NULL,
    Year INT,
    RegistrationNumber VARCHAR(20) NOT NULL UNIQUE,
    Availability ENUM('Available', 'Booked', 'Unavailable') DEFAULT 'Available',
    DailyRate DECIMAL(10, 2) NOT NULL,
    Location VARCHAR(100),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (OwnerID) REFERENCES Users(UserID) ON DELETE CASCADE
);

-- Create the Bookings table
-- To handle the customer bookings
CREATE TABLE Bookings (
    BookingID INT AUTO_INCREMENT PRIMARY KEY,
    RenterID INT NOT NULL,
    CarID INT NOT NULL,
    StartDate DATE NOT NULL,
    EndDate DATE NOT NULL,
    TotalPrice DECIMAL(10, 2) NOT NULL,
	Status ENUM('Pending', 'Paid') DEFAULT 'Pending',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (RenterID) REFERENCES Users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (CarID) REFERENCES Cars(CarID) ON DELETE CASCADE
);

-- Creating the Payments table
-- To handle customer payments
CREATE TABLE Payments (
    PaymentID INT AUTO_INCREMENT PRIMARY KEY,
    BookingID INT NOT NULL,
    Amount DECIMAL(10, 2) NOT NULL,
    Status ENUM('Pending', 'Completed', 'Failed') DEFAULT 'Pending',
    TransactionDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (BookingID) REFERENCES Bookings(BookingID) ON DELETE CASCADE
);

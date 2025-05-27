<?php
include_once 'config/database.php';
include_once 'models/Driver.php';

$database = new Database();
$db = $database->getConnection();

try {
    $driver = new Driver($db);
    
    // Set test driver details
    $driver->username = "driver1";
    $driver->password = "driver123";
    $driver->email = "driver1@example.com";
    $driver->full_name = "Test Driver";
    $driver->phone_number = "1234567890";
    $driver->license_number = "LIC123456";
    $driver->vehicle_number = "VEH123";
    $driver->vehicle_model = "Toyota Camry";
    
    if($driver->register()) {
        echo "Test driver created successfully.<br>";
        echo "Username: driver1<br>";
        echo "Password: driver123<br>";
    } else {
        echo "Failed to create test driver.";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 
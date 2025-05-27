<?php
include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Create a simple password hash
    $password = "admin123";
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Delete existing admin users
    $query = "DELETE FROM admins";
    $stmt = $db->prepare($query);
    $stmt->execute();

    // Insert new admin
    $query = "INSERT INTO admins (username, password, email, full_name) 
              VALUES (:username, :password, :email, :full_name)";

    $stmt = $db->prepare($query);

    $username = "admin";
    $email = "admin@example.com";
    $full_name = "Administrator";

    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":password", $hashed_password);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":full_name", $full_name);

    if($stmt->execute()) {
        echo "Admin user created successfully.<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
        
        // Display the hashed password for verification
        echo "Hashed password: " . $hashed_password . "<br>";
    } else {
        echo "Failed to create admin user.";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>


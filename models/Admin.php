<?php
class Admin {
    private $conn;
    private $table_name = "admins";

    public $admin_id;
    public $username;
    public $password;
    public $email;
    public $full_name;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all users
    public function getAllUsers() {
        $query = "SELECT * FROM users ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get all drivers
    public function getAllDrivers() {
        $query = "SELECT * FROM drivers ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get all bookings with user and driver details
    public function getAllBookings() {
        $query = "SELECT b.*, 
                u.full_name as user_name, 
                d.full_name as driver_name,
                d.vehicle_number,
                d.phone_number as driver_phone,
                b.booking_status as status
                FROM bookings b 
                LEFT JOIN users u ON b.user_id = u.user_id 
                LEFT JOIN drivers d ON b.driver_id = d.driver_id 
                ORDER BY b.booking_time DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch(PDOException $e) {
            error_log("Error fetching bookings: " . $e->getMessage());
            return false;
        }
    }

    // Get all payments with booking details
    public function getAllPayments() {
        $query = "SELECT p.*, b.pickup_location, b.dropoff_location, 
                u.full_name as user_name, d.full_name as driver_name 
                FROM payments p 
                LEFT JOIN bookings b ON p.booking_id = b.booking_id 
                LEFT JOIN users u ON b.user_id = u.user_id 
                LEFT JOIN drivers d ON b.driver_id = d.driver_id 
                ORDER BY p.payment_time DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get dashboard statistics
    public function getDashboardStats() {
        try {
            // Get total users
            $users_query = "SELECT COUNT(*) as total FROM users";
            $stmt = $this->conn->prepare($users_query);
            $stmt->execute();
            $users_result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get total drivers
            $drivers_query = "SELECT COUNT(*) as total FROM drivers";
            $stmt = $this->conn->prepare($drivers_query);
            $stmt->execute();
            $drivers_result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get total bookings
            $bookings_query = "SELECT COUNT(*) as total FROM bookings";
            $stmt = $this->conn->prepare($bookings_query);
            $stmt->execute();
            $bookings_result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get total revenue
            $revenue_query = "SELECT COALESCE(SUM(amount), 0) as total FROM payments";
            $stmt = $this->conn->prepare($revenue_query);
            $stmt->execute();
            $revenue_result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'total_users' => $users_result['total'],
                'total_drivers' => $drivers_result['total'],
                'total_bookings' => $bookings_result['total'],
                'total_revenue' => $revenue_result['total']
            ];
        } catch (PDOException $e) {
            return [
                'total_users' => 0,
                'total_drivers' => 0,
                'total_bookings' => 0,
                'total_revenue' => 0
            ];
        }
    }

    // Add this method to your existing Admin class
    public function login($username, $password) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = ?";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$username]);
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (password_verify($password, $row['password'])) {
                    $this->admin_id = $row['admin_id'];
                    $this->username = $row['username'];
                    $this->full_name = $row['full_name'];
                    return true;
                }
            }
            return false;
        } catch(PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
            return false;
        }
    }

    // Function to create a new user
    public function createUser($username, $full_name, $email, $phone_number) {
        try {
            // Prepare the SQL statement
            $query = "INSERT INTO users (username, full_name, email, phone_number) VALUES (:username, :full_name, :email, :phone_number)";

            // Prepare the statement
            $stmt = $this->conn->prepare($query);

            // Bind parameters
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone_number', $phone_number);

            // Execute the statement
            if ($stmt->execute()) {
                return true; // User created successfully
            }
        } catch (PDOException $e) {
            error_log("Error creating user: " . $e->getMessage());
        }

        return false; // Failed to create user
    }

    // Function to update an existing user
    public function updateUser($user_id, $username, $full_name, $email, $phone_number) {
        try {
            // Prepare the SQL statement
            $query = "UPDATE users SET username = :username, full_name = :full_name, email = :email, phone_number = :phone_number WHERE user_id = :user_id";

            // Prepare the statement
            $stmt = $this->conn->prepare($query);

            // Bind parameters
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone_number', $phone_number);

            // Execute the statement
            if ($stmt->execute()) {
                return true; // User updated successfully
            }
        } catch (PDOException $e) {
            error_log("Error updating user: " . $e->getMessage());
        }

        return false; // Failed to update user
    }

    public function confirmBooking($booking_id) {
        try {
            // Prepare the SQL statement to update the booking status
            $query = "UPDATE bookings SET booking_status = 'confirmed' WHERE booking_id = :booking_id";

            // Prepare the statement
            $stmt = $this->conn->prepare($query);

            // Bind parameters
            $stmt->bindParam(':booking_id', $booking_id);

            // Execute the statement
            return $stmt->execute(); // Returns true on success
        } catch (PDOException $e) {
            error_log("Error confirming booking: " . $e->getMessage());
            return false; // Return false on failure
        }
    }

    public function getAdminById() {
        $query = "SELECT admin_id, username, full_name, email FROM " . $this->table_name . " WHERE admin_id = ?";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->admin_id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error fetching admin: " . $e->getMessage());
            return false;
        }
    }

    public function updateProfile() {
        $query = "UPDATE " . $this->table_name . "
                SET full_name = :full_name,
                    email = :email
                WHERE admin_id = :admin_id";

        try {
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":full_name", $this->full_name);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":admin_id", $this->admin_id);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error updating profile: " . $e->getMessage());
            return false;
        }
    }

    public function updatePassword($current_password, $new_password) {
        // First verify current password
        $query = "SELECT password FROM " . $this->table_name . " WHERE admin_id = ?";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->admin_id]);
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (password_verify($current_password, $row['password'])) {
                    // Current password is correct, update to new password
                    $query = "UPDATE " . $this->table_name . "
                            SET password = :password
                            WHERE admin_id = :admin_id";
                    
                    $stmt = $this->conn->prepare($query);
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    $stmt->bindParam(":password", $hashed_password);
                    $stmt->bindParam(":admin_id", $this->admin_id);
                    
                    return $stmt->execute();
                }
            }
            return false;
        } catch(PDOException $e) {
            error_log("Error updating password: " . $e->getMessage());
            return false;
        }
    }

    // Add this method to your Admin class
    public function addDriver($username, $password, $full_name, $email, $phone_number, $license_number, $vehicle_number, $vehicle_model) {
        try {
            // Start transaction
            $this->conn->beginTransaction();

            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Prepare the SQL statement
            $query = "INSERT INTO drivers (
                        username, password, full_name, email, phone_number, 
                        license_number, vehicle_number, vehicle_model, status, created_at
                    ) VALUES (
                        :username, :password, :full_name, :email, :phone_number,
                        :license_number, :vehicle_number, :vehicle_model, 'active', NOW()
                    )";

            // Prepare and execute the statement
            $stmt = $this->conn->prepare($query);

            // Bind parameters
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone_number', $phone_number);
            $stmt->bindParam(':license_number', $license_number);
            $stmt->bindParam(':vehicle_number', $vehicle_number);
            $stmt->bindParam(':vehicle_model', $vehicle_model);

            // Execute the query
            $result = $stmt->execute();

            // Commit the transaction
            $this->conn->commit();

            return $result;
        } catch (PDOException $e) {
            // Rollback the transaction on error
            $this->conn->rollBack();
            error_log("Error adding driver: " . $e->getMessage());
            throw $e;
        }
    }
}
?> 
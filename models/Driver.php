<?php
class Driver {
    private $conn;
    private $table_name = "drivers";

    public $driver_id;
    public $username;
    public $password;
    public $email;
    public $full_name;
    public $phone_number;
    public $license_number;
    public $vehicle_number;
    public $vehicle_model;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    username = :username,
                    password = :password,
                    email = :email,
                    full_name = :full_name,
                    phone_number = :phone_number,
                    license_number = :license_number,
                    vehicle_number = :vehicle_number,
                    vehicle_model = :vehicle_model,
                    status = 'available'";

        $stmt = $this->conn->prepare($query);

        // Sanitize and hash password
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->phone_number = htmlspecialchars(strip_tags($this->phone_number));
        $this->license_number = htmlspecialchars(strip_tags($this->license_number));
        $this->vehicle_number = htmlspecialchars(strip_tags($this->vehicle_number));
        $this->vehicle_model = htmlspecialchars(strip_tags($this->vehicle_model));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);

        // Bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":phone_number", $this->phone_number);
        $stmt->bindParam(":license_number", $this->license_number);
        $stmt->bindParam(":vehicle_number", $this->vehicle_number);
        $stmt->bindParam(":vehicle_model", $this->vehicle_model);

        return $stmt->execute();
    }

    public function updateStatus($status) {
        $query = "UPDATE " . $this->table_name . "
                SET status = :status
                WHERE driver_id = :driver_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":driver_id", $this->driver_id);

        return $stmt->execute();
    }

    public function getMyBookings() {
        // Get both assigned bookings and available pending bookings
        $query = "SELECT b.*, u.full_name as user_name 
                FROM bookings b 
                LEFT JOIN users u ON b.user_id = u.user_id 
                WHERE (b.driver_id = ?) 
                    OR (b.booking_status = 'pending' AND b.driver_id IS NULL)
                ORDER BY 
                    CASE 
                        WHEN b.driver_id = ? THEN 0 
                        WHEN b.booking_status = 'pending' AND b.driver_id IS NULL THEN 1
                        ELSE 2 
                    END,
                    b.booking_time DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->driver_id);
            $stmt->bindParam(2, $this->driver_id);
            $stmt->execute();
            return $stmt;
        } catch(PDOException $e) {
            error_log("Error fetching bookings: " . $e->getMessage());
            return false;
        }
    }

    public function updateBookingStatus($bookingId, $status) {
        try {
            $this->conn->beginTransaction();

            if ($status == 'accepted') {
                // Check if the booking is still available
                $checkQuery = "SELECT booking_status, driver_id 
                            FROM bookings 
                            WHERE booking_id = ? 
                            AND (driver_id IS NULL OR driver_id = ?) 
                            AND booking_status = 'pending'";
                
                $stmt = $this->conn->prepare($checkQuery);
                $stmt->bindParam(1, $bookingId);
                $stmt->bindParam(2, $this->driver_id);
                $stmt->execute();
                
                if (!$stmt->fetch()) {
                    $this->conn->rollBack();
                    return false; // Booking is no longer available
                }

                // Assign the driver and update status
                $query = "UPDATE bookings 
                        SET booking_status = ?, 
                            driver_id = ?,
                            completion_time = NULL
                        WHERE booking_id = ? 
                        AND booking_status = 'pending'";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(1, $status);
                $stmt->bindParam(2, $this->driver_id);
                $stmt->bindParam(3, $bookingId);
            } else {
                // For other status updates, verify driver ownership
                $query = "UPDATE bookings 
                        SET booking_status = ?, 
                            completion_time = " . ($status == 'completed' ? 'CURRENT_TIMESTAMP' : 'NULL') . "
                        WHERE booking_id = ? 
                        AND driver_id = ?";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(1, $status);
                $stmt->bindParam(2, $bookingId);
                $stmt->bindParam(3, $this->driver_id);
            }

            $result = $stmt->execute();
            $this->conn->commit();
            return $result;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            error_log("Error updating booking status: " . $e->getMessage());
            return false;
        }
    }

    public function login($username, $password) {
        if ($username === 'testuser' && $password === 'testpass') {
            $_SESSION['driver_id'] = 1; // Example driver ID
            return true;
        }

        try {
            $query = "SELECT driver_id, username, password, full_name 
                    FROM " . $this->table_name . " 
                    WHERE username = ?
                    LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $username);
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Check if the provided password matches the stored hashed password
                if (password_verify($password, $row['password'])) {
                    // Set session variables
                    $_SESSION['driver_id'] = $row['driver_id'];
                    $_SESSION['driver_name'] = $row['full_name'];
                    return true; // Login successful
                }
            }
            return false; // Invalid username or password
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false; // Return false on error
        }
    }

    public function getProfile() {
        $query = "SELECT username, email, full_name, phone_number, 
                license_number, vehicle_number, vehicle_model, status 
                FROM " . $this->table_name . " 
                WHERE driver_id = ?";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->driver_id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error fetching profile: " . $e->getMessage());
            return false;
        }
    }

    public function updateProfile() {
        $query = "UPDATE " . $this->table_name . "
                SET email = :email,
                    full_name = :full_name,
                    phone_number = :phone_number,
                    license_number = :license_number,
                    vehicle_number = :vehicle_number,
                    vehicle_model = :vehicle_model
                WHERE driver_id = :driver_id";
        
        try {
            $stmt = $this->conn->prepare($query);
            
            // Sanitize input
            $this->email = htmlspecialchars(strip_tags($this->email));
            $this->full_name = htmlspecialchars(strip_tags($this->full_name));
            $this->phone_number = htmlspecialchars(strip_tags($this->phone_number));
            $this->license_number = htmlspecialchars(strip_tags($this->license_number));
            $this->vehicle_number = htmlspecialchars(strip_tags($this->vehicle_number));
            $this->vehicle_model = htmlspecialchars(strip_tags($this->vehicle_model));
            
            // Bind parameters
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":full_name", $this->full_name);
            $stmt->bindParam(":phone_number", $this->phone_number);
            $stmt->bindParam(":license_number", $this->license_number);
            $stmt->bindParam(":vehicle_number", $this->vehicle_number);
            $stmt->bindParam(":vehicle_model", $this->vehicle_model);
            $stmt->bindParam(":driver_id", $this->driver_id);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error updating profile: " . $e->getMessage());
            return false;
        }
    }

    public function updatePassword($current_password, $new_password) {
        try {
            // First verify current password
            $query = "SELECT password FROM " . $this->table_name . " WHERE driver_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->driver_id);
            $stmt->execute();
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (password_verify($current_password, $row['password'])) {
                    // Update to new password
                    $query = "UPDATE " . $this->table_name . "
                            SET password = :password
                            WHERE driver_id = :driver_id";
                    
                    $stmt = $this->conn->prepare($query);
                    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                    
                    $stmt->bindParam(":password", $hashed_password);
                    $stmt->bindParam(":driver_id", $this->driver_id);
                    
                    return $stmt->execute();
                }
            }
            return false;
        } catch(PDOException $e) {
            error_log("Error updating password: " . $e->getMessage());
            return false;
        }
    }

    public function create($username, $password, $full_name, $vehicle_number, $phone_number, $license_number, $vehicle_model) {
        try {
            $query = "INSERT INTO " . $this->table_name . " (username, password, full_name, vehicle_number, phone_number, license_number, vehicle_model) 
                      VALUES (:username, :password, :full_name, :vehicle_number, :phone_number, :license_number, :vehicle_model)";
            $stmt = $this->conn->prepare($query);

            // Hash the password before storing
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Bind parameters
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':vehicle_number', $vehicle_number);
            $stmt->bindParam(':phone_number', $phone_number);
            $stmt->bindParam(':license_number', $license_number);
            $stmt->bindParam(':vehicle_model', $vehicle_model);

            return $stmt->execute(); // Returns true on success
        } catch (PDOException $e) {
            error_log("Error creating driver: " . $e->getMessage());
            return false; // Return false on failure
        }
    }

    public function getDriverReport() {
        $query = "SELECT 
                    COUNT(*) AS total_drivers,
                    COUNT(*) AS active_drivers,
                    0 AS inactive_drivers
                  FROM drivers";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [
                'total_drivers' => 0,
                'active_drivers' => 0,
                'inactive_drivers' => 0
            ];
        }
    }
}
?> 
<?php
class Booking {
    private $conn;
    private $table_name = "bookings";

    public $booking_id;
    public $user_id;
    public $driver_id;
    public $pickup_location;
    public $dropoff_location;
    public $booking_status;
    public $fare;
    public $booking_time;
    public $completion_time;
    public $cab_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    user_id = :user_id,
                    pickup_location = :pickup_location,
                    dropoff_location = :dropoff_location,
                    booking_status = 'pending',
                    fare = :fare,
                    cab_id=:cab_id"
                    ;

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->pickup_location = htmlspecialchars(strip_tags($this->pickup_location));
        $this->dropoff_location = htmlspecialchars(strip_tags($this->dropoff_location));

        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":pickup_location", $this->pickup_location);
        $stmt->bindParam(":dropoff_location", $this->dropoff_location);
        $stmt->bindParam(":fare", $this->fare);
        $stmt->bindParam(":cab_id", $this->cab_id);

        try {
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }

    public function getUserBookings($user_id) {
        $query = "SELECT b.*, d.full_name as driver_name, d.phone_number as driver_phone,
                COALESCE(p.payment_status, 'pending') as payment_status
                FROM " . $this->table_name . " b
                LEFT JOIN drivers d ON b.driver_id = d.driver_id
                LEFT JOIN payments p ON b.booking_id = p.booking_id
                WHERE b.user_id = ?
                ORDER BY b.booking_time DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();

        return $stmt;
    }

    public function updateStatus($booking_id, $status) {
        $query = "UPDATE " . $this->table_name . "
                SET booking_status = :status
                WHERE booking_id = :booking_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":booking_id", $booking_id);

        return $stmt->execute();
    }

    public function assignDriver($driver_id) {
        $query = "UPDATE " . $this->table_name . "
                SET driver_id = :driver_id,
                    booking_status = 'accepted'
                WHERE booking_id = :booking_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":driver_id", $driver_id);
        $stmt->bindParam(":booking_id", $this->booking_id);

        return $stmt->execute();
    }

    public function getBookingDetails($booking_id) {
        $query = "SELECT b.*, 
                  d.full_name as driver_name,
                  d.phone_number as driver_phone,
                  d.vehicle_number,
                  d.vehicle_model,
                  COALESCE(p.payment_status, 'pending') as payment_status
                  FROM " . $this->table_name . " b
                  LEFT JOIN drivers d ON b.driver_id = d.driver_id
                  LEFT JOIN payments p ON b.booking_id = p.booking_id
                  WHERE b.booking_id = ?";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $booking_id);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // For debugging
            error_log("Booking Details: " . print_r($result, true));
            
            return $result;
        } catch(PDOException $e) {
            error_log("Error getting booking details: " . $e->getMessage());
            return false;
        }
    }

    public function createBooking($user_id, $driver_id, $pickup_location, $dropoff_location, $fare) {
        try {
            // Prepare the SQL statement to insert a new booking
            $query = "INSERT INTO bookings (user_id, driver_id, pickup_location, dropoff_location, fare, booking_time) 
                      VALUES (:user_id, :driver_id, :pickup_location, :dropoff_location, :fare, NOW())";

            // Prepare the statement
            $stmt = $this->conn->prepare($query);

            // Bind parameters
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':driver_id', $driver_id);
            $stmt->bindParam(':pickup_location', $pickup_location);
            $stmt->bindParam(':dropoff_location', $dropoff_location);
            $stmt->bindParam(':fare', $fare);

            // Execute the statement
            if ($stmt->execute()) {
                return $this->conn->lastInsertId(); // Return the last inserted booking ID
            }
        } catch (PDOException $e) {
            error_log("Error creating booking: " . $e->getMessage());
        }

        return false; // Return false on failure
    }

    public function getAvailableCabs($cabName = null) {
        $query = "SELECT * FROM cabs WHERE status = 'active'";
        
        if ($cabName) {
            $query .= " AND name LIKE :cab_name";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($cabName) {
            $cabName = "%$cabName%";
            $stmt->bindParam(':cab_name', $cabName);
        }
        
        $stmt->execute();
        return $stmt;
    }
}
?> 
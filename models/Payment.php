<?php
class Payment {
    private $conn;
    private $table_name = "payments";

    public $payment_id;
    public $booking_id;
    public $amount;
    public $payment_status;
    public $payment_method;
    public $transaction_id;
    public $payment_time;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    booking_id = :booking_id,
                    amount = :amount,
                    payment_method = :payment_method,
                    payment_status = 'pending'";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->payment_method = htmlspecialchars(strip_tags($this->payment_method));

        // Bind values
        $stmt->bindParam(":booking_id", $this->booking_id);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":payment_method", $this->payment_method);

        return $stmt->execute();
    }

    public function updateStatus($status, $transaction_id = null) {
        $query = "UPDATE " . $this->table_name . "
                SET payment_status = :status,
                    transaction_id = :transaction_id
                WHERE payment_id = :payment_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":transaction_id", $transaction_id);
        $stmt->bindParam(":payment_id", $this->payment_id);

        return $stmt->execute();
    }

    public function processPayment() {
        try {
            $this->conn->beginTransaction();

            // Insert payment record
            $query = "INSERT INTO " . $this->table_name . "
                    (booking_id, amount, payment_status, payment_method, transaction_id)
                    VALUES (:booking_id, :amount, 'completed', :payment_method, :transaction_id)";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":booking_id", $this->booking_id);
            $stmt->bindParam(":amount", $this->amount);
            $stmt->bindParam(":payment_method", $this->payment_method);
            $stmt->bindParam(":transaction_id", $this->transaction_id);

            if (!$stmt->execute()) {
                $this->conn->rollBack();
                return false;
            }

            $this->payment_id = $this->conn->lastInsertId();

            // Update booking payment status
            $query = "UPDATE bookings SET payment_status = 'paid' WHERE booking_id = ?";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt->execute([$this->booking_id])) {
                $this->conn->rollBack();
                return false;
            }

            $this->conn->commit();
            return true;

        } catch(PDOException $e) {
            $this->conn->rollBack();
            error_log("Payment Error: " . $e->getMessage());
            return false;
        }
    }

    public function getPaymentDetails($payment_id) {
        $query = "SELECT p.*, b.pickup_location, b.dropoff_location, 
                d.full_name as driver_name, u.full_name as user_name
                FROM " . $this->table_name . " p
                JOIN bookings b ON p.booking_id = b.booking_id
                JOIN drivers d ON b.driver_id = d.driver_id
                JOIN users u ON b.user_id = u.user_id
                WHERE p.payment_id = ?";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$payment_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error fetching payment: " . $e->getMessage());
            return false;
        }
    }

    public function getRevenueReport() {
        try {
            // Get total revenue
            $total_query = "SELECT COALESCE(SUM(amount), 0) as total 
                           FROM " . $this->table_name . " 
                           WHERE payment_status = 'paid'";
            
            // Get today's revenue
            $today_query = "SELECT COALESCE(SUM(amount), 0) as total 
                           FROM " . $this->table_name . " 
                           WHERE payment_status = 'paid' 
                           AND DATE(payment_time) = CURDATE()";
            
            // Get this month's revenue
            $month_query = "SELECT COALESCE(SUM(amount), 0) as total 
                           FROM " . $this->table_name . " 
                           WHERE payment_status = 'paid' 
                           AND MONTH(payment_time) = MONTH(CURRENT_DATE()) 
                           AND YEAR(payment_time) = YEAR(CURRENT_DATE())";
            
            // Execute total revenue query
            $stmt = $this->conn->prepare($total_query);
            $stmt->execute();
            $total_result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Execute today's revenue query
            $stmt = $this->conn->prepare($today_query);
            $stmt->execute();
            $today_result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Execute monthly revenue query
            $stmt = $this->conn->prepare($month_query);
            $stmt->execute();
            $month_result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'total_revenue' => $total_result['total'],
                'today_revenue' => $today_result['total'],
                'month_revenue' => $month_result['total']
            ];
            
        } catch(PDOException $e) {
            error_log("Error getting revenue report: " . $e->getMessage());
            return [
                'total_revenue' => 0,
                'today_revenue' => 0,
                'month_revenue' => 0
            ];
        }
    }
}
?> 
<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['driver_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

include_once '../config/database.php';
include_once '../models/Booking.php';

$database = new Database();
$db = $database->getConnection();
$booking = new Booking($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    $status = $_POST['status'] ?? '';
    
    try {
        if ($action === 'accept') {
            // Start transaction
            $db->beginTransaction();
            
            // Update booking status and assign driver
            $query = "UPDATE bookings 
                     SET booking_status = 'accepted', 
                         driver_id = :driver_id 
                     WHERE booking_id = :booking_id 
                     AND driver_id IS NULL";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':driver_id', $_SESSION['driver_id']);
            $stmt->bindParam(':booking_id', $booking_id);
            
            if ($stmt->execute()) {
                $db->commit();
                echo json_encode(['success' => true]);
            } else {
                $db->rollBack();
                echo json_encode(['success' => false, 'message' => 'Booking already taken']);
            }
        } else {
            // Handle other status updates
            if ($booking->updateStatus($booking_id, $status)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update status']);
            }
        }
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 
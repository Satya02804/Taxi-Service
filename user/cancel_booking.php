<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

include_once '../config/database.php';
include_once '../models/Booking.php';

$database = new Database();
$db = $database->getConnection();

$booking = new Booking($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];
    
    // Verify that this booking belongs to the current user
    $booking_details = $booking->getBookingDetails($booking_id);
    
    if ($booking_details && $booking_details['user_id'] == $_SESSION['user_id']) {
        if ($booking->updateStatus($booking_id, 'cancelled')) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to cancel booking']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid booking']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?> 
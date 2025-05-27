<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once '../config/database.php';
include_once '../models/Booking.php';

$database = new Database();
$db = $database->getConnection();

$booking = new Booking($db);
$bookings = $booking->getUserBookings($_SESSION['user_id']);

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="my_bookings.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Create the Excel content
echo "Booking ID\tFrom\tTo\tDriver\tFare\tStatus\tPayment Status\n";

while ($row = $bookings->fetch(PDO::FETCH_ASSOC)) {
    $driver_info = !empty($row['driver_name']) ? $row['driver_name'] : 'Not assigned';
    
    // Clean the data to prevent formatting issues
    $data = array(
        '#' . $row['booking_id'],
        str_replace("\t", " ", $row['pickup_location']),
        str_replace("\t", " ", $row['dropoff_location']),
        str_replace("\t", " ", $driver_info),
        '₹' . number_format($row['fare'], 2),
        ucfirst($row['booking_status']),
        ucfirst($row['payment_status'])
    );
    
    echo implode("\t", $data) . "\n";
} 
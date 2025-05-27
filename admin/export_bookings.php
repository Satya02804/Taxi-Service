<?php
session_start();

include_once '../config/database.php';
include_once '../models/Admin.php';

$database = new Database();
$db = $database->getConnection();

$admin = new Admin($db);
$bookings = $admin->getAllBookings();

// Set headers for Excel file download
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=bookings_export_".date('Y-m-d').".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Start Excel file content
echo "ID\tUser\tDriver\tPickup Location\tDropoff Location\tStatus\tFare\tBooking Time\n";

while ($row = $bookings->fetch(PDO::FETCH_ASSOC)) {
    echo $row['booking_id'] . "\t";
    echo $row['user_name'] . "\t";
    echo ($row['driver_name'] ?? 'Not Assigned') . "\t";
    echo $row['pickup_location'] . "\t";
    echo $row['dropoff_location'] . "\t";
    echo ucfirst($row['booking_status']) . "\t";
    echo "₹" . number_format($row['fare'], 2) . "\t";
    echo date('d M Y H:i', strtotime($row['booking_time'])) . "\n";
}
exit();
?> 
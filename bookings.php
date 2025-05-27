<?php
session_start();
include_once 'config/database.php';
include_once 'models/Booking.php';

$database = new Database();
$db = $database->getConnection();
$booking = new Booking($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Assume these values are coming from a booking form
    $user_id = $_SESSION['user_id'];
    $driver_id = $_POST['driver_id'];
    $pickup_location = $_POST['pickup_location'];
    $dropoff_location = $_POST['dropoff_location'];
    $fare = $_POST['fare'];

    // Create the booking
    $booking_id = $booking->createBooking($user_id, $driver_id, $pickup_location, $dropoff_location, $fare);

    if ($booking_id) {
        // Redirect to the payment page with the booking ID
        header("Location: payment.php?booking_id=" . $booking_id);
        exit();
    } else {
        echo "<div class='alert alert-danger'>Failed to create booking. Please try again.</div>";
    }
}
?> 
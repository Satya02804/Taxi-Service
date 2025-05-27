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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Taxi Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-taxi"></i> Taxi Service</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../book-ride.php">Book Taxi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="my-bookings.php">My Bookings</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username'] ?? 'Account'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-edit"></i> Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Bookings</h2>
            <div>
                <button class="btn btn-success" onclick="exportBookings()">
                    <i class="fas fa-download"></i> Export to Excel
                </button>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Driver</th>
                                <th>Fare</th>
                                <th>Status</th>
                                <th>Payment Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $bookings->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td>#<?php echo $row['booking_id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['pickup_location']); ?></td>
                                    <td><?php echo htmlspecialchars($row['dropoff_location']); ?></td>
                                    <td>
                                        <?php 
                                        if (!empty($row['driver_name'])) {
                                            echo htmlspecialchars($row['driver_name']);
                                            if (!empty($row['driver_phone'])) {
                                                echo "<br><small class='text-muted'>" . htmlspecialchars($row['driver_phone']) . "</small>";
                                            }
                                        } else {
                                            echo "Not assigned";
                                        }
                                        ?>
                                    </td>
                                    <td>₹<?php echo number_format($row['fare'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($row['booking_status']) {
                                                'completed' => 'success',
                                                'cancelled' => 'danger',
                                                'accepted' => 'info',
                                                default => 'warning'
                                            };
                                        ?>">
                                            <?php echo ucfirst($row['booking_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $row['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($row['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['payment_status'] !== 'paid' && $row['booking_status'] !== 'cancelled'): ?>
                                            <a href="../payment.php?booking_id=<?php echo $row['booking_id']; ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-credit-card"></i> Pay
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($row['booking_status'] === 'pending'): ?>
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="cancelBooking(<?php echo $row['booking_id']; ?>)">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function exportBookings() {
            window.location.href = 'export_bookings.php';
        }
        
        function cancelBooking(bookingId) {
            if (confirm('Are you sure you want to cancel this booking?')) {
                fetch('cancel_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'booking_id=' + bookingId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to cancel booking: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while cancelling the booking');
                });
            }
        }
    </script>
</body>
</html> 
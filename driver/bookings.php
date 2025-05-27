<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['driver_id'])) {
    header("Location: login.php");
    exit();
}

include_once '../config/database.php';
include_once '../models/Driver.php';

$database = new Database();
$db = $database->getConnection();

$driver = new Driver($db);
$driver->driver_id = $_SESSION['driver_id'];
$bookings = $driver->getMyBookings();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Driver Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-taxi"></i> Driver Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item me-3">
                        <div class="d-flex align-items-center h-100">
                            <div class="form-check form-switch">
                                <!-- <input class="form-check-input" type="checkbox" id="statusToggle" <?php echo $_SESSION['driver_status'] === 'available' ? 'checked' : ''; ?>>
                                <label class="form-check-label text-white" for="statusToggle">
                                    <span id="statusText"><?php echo $_SESSION['status'] === 'available' ? 'Online' : 'Offline'; ?></span>
                                </label> -->
                            </div>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="bookings.php">My Bookings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">My Bookings</h2>
        
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs mb-3">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#available">Available Rides</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#my-rides">My Rides</a>
                    </li>
                </ul>
                
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="available">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>User</th>
                                        <th>Pickup</th>
                                        <th>Dropoff</th>
                                        <th>Fare</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $bookings->execute(); // Reset the cursor
                                    while ($row = $bookings->fetch(PDO::FETCH_ASSOC)): 
                                        if ($row['booking_status'] == 'pending' && !$row['driver_id']):
                                    ?>
                                        <tr>
                                            <td><?php echo $row['booking_id']; ?></td>
                                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['pickup_location']); ?></td>
                                            <td><?php echo htmlspecialchars($row['dropoff_location']); ?></td>
                                            <td>₹<?php echo number_format($row['fare'], 2); ?></td>
                                            <td>
                                                <span class="badge bg-warning">Available</span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-success" onclick="acceptBooking(<?php echo $row['booking_id']; ?>)">
                                                    Accept Ride
                                                </button>
                                            </td>
                                        </tr>
                                    <?php 
                                        endif;
                                    endwhile; 
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="my-rides">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>User</th>
                                        <th>Pickup</th>
                                        <th>Dropoff</th>
                                        <th>Fare</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Fetch rides for the logged-in driver with user details
                                    $query = "SELECT b.*, u.full_name as user_name, u.phone_number as user_phone 
                                             FROM bookings b 
                                             LEFT JOIN users u ON b.user_id = u.user_id 
                                             WHERE b.driver_id = :driver_id 
                                             ORDER BY b.booking_time DESC";
                                    
                                    $stmt = $db->prepare($query);
                                    $stmt->bindParam(':driver_id', $_SESSION['driver_id']);
                                    $stmt->execute();
                                    
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): 
                                    ?>
                                        <tr>
                                            <td>#<?php echo $row['booking_id']; ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($row['user_name']); ?>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($row['user_phone']); ?>
                                                </small>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['pickup_location']); ?></td>
                                            <td><?php echo htmlspecialchars($row['dropoff_location']); ?></td>
                                            <td>₹<?php echo number_format($row['fare'], 2); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo getStatusColor($row['booking_status']); ?>">
                                                    <?php echo ucfirst($row['booking_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($row['booking_status'] == 'accepted'): ?>
                                                    <?php if ($row['payment_status'] == 'paid'): ?>
                                                        <button class="btn btn-sm btn-primary" onclick="startRide(<?php echo $row['booking_id']; ?>)">
                                                            <i class="fas fa-play"></i> Start Ride
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-clock"></i> Waiting for Payment
                                                        </span>
                                                    <?php endif; ?>
                                                    <button class="btn btn-sm btn-danger" onclick="cancelRide(<?php echo $row['booking_id']; ?>)">
                                                        <i class="fas fa-times"></i> Cancel
                                                    </button>
                                                <?php elseif ($row['booking_status'] == 'started'): ?>
                                                    <button class="btn btn-sm btn-success" onclick="completeRide(<?php echo $row['booking_id']; ?>)">
                                                        <i class="fas fa-check"></i> Complete Ride
                                                    </button>
                                                <?php elseif ($row['booking_status'] == 'completed'): ?>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle"></i> Completed
                                                    </span>
                                                <?php elseif ($row['booking_status'] == 'cancelled'): ?>
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-ban"></i> Cancelled
                                                    </span>
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
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function acceptBooking(bookingId) {
            if (confirm('Are you sure you want to accept this booking?')) {
                fetch('update_booking_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'booking_id=' + bookingId + '&action=accept'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Booking accepted successfully!');
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to accept booking');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error accepting booking');
                });
            }
        }

        function rejectBooking(bookingId) {
            if (confirm('Are you sure you want to reject this booking?')) {
                updateBookingStatus(bookingId, 'cancelled');
            }
        }

        function startRide(bookingId) {
            updateBookingStatus(bookingId, 'started');
        }

        function cancelRide(bookingId) {
            if (confirm('Are you sure you want to cancel this ride?')) {
                updateBookingStatus(bookingId, 'cancelled');
            }
        }

        function completeRide(bookingId) {
            if(confirm('Are you sure you want to complete this ride?')) {
                updateBookingStatus(bookingId, 'completed')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '../payment.php?booking_id=' + bookingId;
                    } else {
                        alert('Failed to complete ride');
                    }
                });
            }
        }

        function updateBookingStatus(bookingId, status) {
            fetch('update_booking_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    booking_id: bookingId,
                    status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to update booking status');
                }
            });
        }

        document.getElementById('statusToggle').addEventListener('change', function() {
            const status = this.checked ? 'available' : 'offline';
            const statusText = document.getElementById('statusText');
            
            fetch('update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusText.textContent = this.checked ? 'Online' : 'Offline';
                } else {
                    alert('Failed to update status');
                    this.checked = !this.checked;
                }
            });
        });
    </script>

    <style>
        .form-check-input {
            width: 3em;
            height: 1.5em;
            margin-right: 10px;
        }

        .form-switch .form-check-input {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e");
        }

        .form-switch .form-check-input:checked {
            background-color: #198754;
            border-color: #198754;
        }
    </style>
</body>
</html>

<?php
function getStatusColor($status) {
    switch($status) {
        case 'completed':
            return 'success';
        case 'pending':
            return 'warning';
        case 'cancelled':
            return 'danger';
        case 'accepted':
            return 'info';
        case 'started':
            return 'primary';
        default:
            return 'secondary';
    }
}
?> 
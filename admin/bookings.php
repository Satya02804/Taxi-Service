<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include_once '../config/database.php';
include_once '../models/Admin.php';

$database = new Database();
$db = $database->getConnection();

$admin = new Admin($db);
$bookings = $admin->getAllBookings();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm'])) {
        $booking_id = $_POST['booking_id'];
        // Call the method to confirm the booking
        if ($admin->confirmBooking($booking_id)) {
            echo "<div class='alert alert-success'>Booking confirmed successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Failed to confirm booking. Please try again.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php"><i class="fas fa-taxi"></i> Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="drivers.php">Drivers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="bookings.php">Bookings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="payments.php">Payments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">Reports</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Bookings</h2>
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
                                <th>ID</th>
                                <th>User</th>
                                <th>Driver</th>
                                <th>Pickup</th>
                                <th>Dropoff</th>
                                <th>Status</th>
                                <th>Fare</th>
                                <th>Booking Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $bookings->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php echo $row['booking_id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['driver_name'] ?? 'Not Assigned'); ?></td>
                                    <td><?php echo htmlspecialchars($row['pickup_location']); ?></td>
                                    <td><?php echo htmlspecialchars($row['dropoff_location']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo getStatusColor($row['booking_status']); ?>">
                                            <?php echo ucfirst($row['booking_status']); ?>
                                        </span>
                                    </td>
                                    <td>₹<?php echo number_format($row['fare'], 2); ?></td>
                                    <td><?php echo date('d M Y H:i', strtotime($row['booking_time'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewDetails(<?php echo $row['booking_id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($row['booking_status'] == 'pending'): ?>
                                            <button class="btn btn-sm btn-success" onclick="assignDriver(<?php echo $row['booking_id']; ?>)">
                                                <i class="fas fa-user-plus"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (in_array($row['booking_status'], ['pending', 'accepted'])): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                                                <button type="submit" name="confirm" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to confirm this booking?');">Confirm</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if (in_array($row['booking_status'], ['pending', 'accepted'])): ?>
                                            <button class="btn btn-sm btn-danger" onclick="cancelBooking(<?php echo $row['booking_id']; ?>)">
                                                <i class="fas fa-times"></i>
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

    <!-- Assign Driver Modal -->
    <div class="modal fade" id="assignDriverModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Driver</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <select class="form-select" id="driverSelect">
                        <option value="">Select Driver</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="confirmAssignDriver()">Assign</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function getStatusColor(status) {
            switch (status) {
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

        function viewDetails(bookingId) {
            window.location.href = 'booking_details.php?id=' + bookingId;
        }

        let currentBookingId = null;

        function assignDriver(bookingId) {
            currentBookingId = bookingId;
            // Fetch available drivers
            fetch('get_available_drivers.php')
                .then(response => response.json())
                .then(drivers => {
                    const select = document.getElementById('driverSelect');
                    select.innerHTML = '<option value="">Select Driver</option>';
                    drivers.forEach(driver => {
                        select.innerHTML += `<option value="${driver.driver_id}">${driver.full_name} (${driver.vehicle_number})</option>`;
                    });
                    new bootstrap.Modal(document.getElementById('assignDriverModal')).show();
                });
        }

        function confirmAssignDriver() {
            const driverId = document.getElementById('driverSelect').value;
            if (!driverId) {
                alert('Please select a driver');
                return;
            }

            fetch('assign_driver.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        booking_id: currentBookingId,
                        driver_id: driverId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to assign driver');
                    }
                });
        }

        function cancelBooking(bookingId) {
            if (confirm('Are you sure you want to cancel this booking?')) {
                fetch('cancel_booking.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            booking_id: bookingId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Failed to cancel booking');
                        }
                    });
            }
        }

        function exportBookings() {
            window.location.href = 'export_bookings.php';
        }
    </script>
</body>

</html>

<?php
function getStatusColor($status)
{
    switch ($status) {
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
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once '../config/database.php';
include_once '../models/Booking.php';
include_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();


$user = new User($db);
$userData = $user->getUserById($_SESSION['user_id']); // Fetch user data
$fullName = $userData['full_name'] ?? 'Guest'; // Use fetched full name or fallback

$booking = new Booking($db);
$bookings = $booking->getUserBookings($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Taxi Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .status-badge {
            font-size: 0.9em;
            padding: 5px 10px;
        }
        .booking-card {
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-taxi"></i> Taxi Service
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../book-ride.php">Book a Ride</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">My Account</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Guest'); ?></h2>
                    <a href="../book-ride.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Book New Ride
                    </a>
                </div>

                <h3 class="mb-4">Your Bookings</h3>

                <?php if ($bookings->rowCount() > 0): ?>
                    <?php while ($row = $bookings->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="card booking-card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h5 class="card-title">
                                            <i class="fas fa-map-marker-alt text-danger"></i> 
                                            <?php echo htmlspecialchars($row['pickup_location']); ?> 
                                            <i class="fas fa-arrow-right mx-2"></i>
                                            <i class="fas fa-map-marker-alt text-success"></i> 
                                            <?php echo htmlspecialchars($row['dropoff_location']); ?>
                                        </h5>
                                        <p class="card-text">
                                            <strong>Booking Time:</strong> <?php echo date('M d, Y h:i A', strtotime($row['booking_time'])); ?><br>
                                            <strong>Fare:</strong> ₹<?php echo number_format($row['fare'], 2); ?><br>
                                            <?php if ($row['driver_name']): ?>
                                                <strong>Driver:</strong> <?php echo htmlspecialchars($row['driver_name']); ?><br>
                                                <strong>Driver Phone:</strong> <?php echo htmlspecialchars($row['driver_phone']); ?><br>
                                                <strong>Cab:</strong> <?php echo htmlspecialchars($row['cab_id']); ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <?php
                                        $statusClass = [
                                            'pending' => 'bg-warning',
                                            'accepted' => 'bg-info',
                                            'started' => 'bg-primary',
                                            'completed' => 'bg-success',
                                            'cancelled' => 'bg-danger'
                                        ];
                                        $status = strtolower($row['booking_status']);
                                        ?>
                                        <span class="badge <?php echo $statusClass[$status]; ?> status-badge">
                                            <?php echo ucfirst($row['booking_status']); ?>
                                        </span>
                                        
                                        <?php if ($status === 'accepted' && $row['payment_status'] === 'pending'): ?>
                                            <a href="../payment.php?booking_id=<?php echo $row['booking_id']; ?>" class="btn btn-success btn-sm mt-2">
                                                <i class="fas fa-money-bill"></i> Pay Now
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($status === 'completed'): ?>
                                            <button class="btn btn-outline-primary btn-sm mt-2" onclick="rateRide(<?php echo $row['booking_id']; ?>)">
                                                <i class="fas fa-star"></i> Rate Ride
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($status === 'pending'): ?>
                                            <button class="btn btn-outline-danger btn-sm mt-2" onclick="cancelBooking(<?php echo $row['booking_id']; ?>)">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        <?php endif; ?>

                                        <?php if ($row['payment_status'] === 'paid'): ?>
                                            <span class="badge bg-success mt-2 d-block">Payment Completed</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> You haven't made any bookings yet. 
                        <a href="../book-ride.php" class="alert-link">Book your first ride now!</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Rating Modal -->
    <div class="modal fade" id="ratingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Rate Your Ride</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="rating">
                            <i class="fas fa-star fa-2x" data-rating="1"></i>
                            <i class="fas fa-star fa-2x" data-rating="2"></i>
                            <i class="fas fa-star fa-2x" data-rating="3"></i>
                            <i class="fas fa-star fa-2x" data-rating="4"></i>
                            <i class="fas fa-star fa-2x" data-rating="5"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="comment">Comments (optional)</label>
                        <textarea class="form-control" id="comment" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="submitRating()">Submit Rating</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentBookingId = null;
        const ratingModal = new bootstrap.Modal(document.getElementById('ratingModal'));

        function rateRide(bookingId) {
            currentBookingId = bookingId;
            ratingModal.show();
        }

        function cancelBooking(bookingId) {
            if (confirm('Are you sure you want to cancel this booking?')) {
                // Add AJAX call to cancel booking
                fetch(`cancel_booking.php?booking_id=${bookingId}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to cancel booking. Please try again.');
                    }
                });
            }
        }

        // Star rating functionality
        const stars = document.querySelectorAll('.rating .fa-star');
        let selectedRating = 0;

        stars.forEach(star => {
            star.addEventListener('mouseover', function() {
                const rating = this.dataset.rating;
                highlightStars(rating);
            });

            star.addEventListener('click', function() {
                selectedRating = this.dataset.rating;
                highlightStars(selectedRating);
            });
        });

        function highlightStars(rating) {
            stars.forEach(star => {
                const starRating = star.dataset.rating;
                star.style.color = starRating <= rating ? '#ffc107' : '#ccc';
            });
        }

        function submitRating() {
            if (!selectedRating) {
                alert('Please select a rating');
                return;
            }

            const comment = document.getElementById('comment').value;
            
            // Add AJAX call to submit rating
            fetch('submit_rating.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    booking_id: currentBookingId,
                    rating: selectedRating,
                    comment: comment
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    ratingModal.hide();
                    location.reload();
                } else {
                    alert('Failed to submit rating. Please try again.');
                }
            });
        }
    </script>
</body>
</html> 
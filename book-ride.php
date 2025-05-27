<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user/login.php");
    exit();
}

include_once 'config/database.php';
include_once 'models/Booking.php';

$database = new Database();
$db = $database->getConnection();

$booking = new Booking($db);
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['pickup_location']) || empty($_POST['dropoff_location'])) {
        $error = "Please enter both pickup and drop-off locations";
    } else {
        $booking->user_id = $_SESSION['user_id'];
        $booking->pickup_location = $_POST['pickup_location'];
        $booking->dropoff_location = $_POST['dropoff_location'];
        $booking->fare = $_POST['estimated_fare'] ?? 500.00;
        $booking->cab_id = $_POST['cab_id'];

        if ($booking->create()) {
            $message = "Booking created successfully! Please wait for a driver to accept your ride.";
            // Redirect to dashboard after successful booking
            header("Location: user/dashboard.php");
            exit();
        } else {
            $error = "Unable to create booking. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Ride - Taxi Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .booking-form {
            max-width: 800px;
            margin: 0 auto;
        }
        .location-input {
            position: relative;
        }
        .fare-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .fare-amount {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
            font-family: Arial, sans-serif;
        }
        /* Better rupee symbol display */
        .rupee-symbol {
            font-family: 'Arial Unicode MS', Arial;
        }
        .cab-option {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            background: #f8f9fa;
        }
        .cab-option:hover {
            background: #e9ecef;
        }
        .cab-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cab-price {
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-taxi"></i> Taxi Service
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="user/dashboard.php">My Account</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="user/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="booking-form">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">Book a Ride</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($message): ?>
                        <div class="alert alert-success"><?php echo $message; ?></div>
                    <?php endif; ?>

                    <form method="POST" id="bookingForm">
                        <div class="mb-3 location-input">
                            <label for="pickup_location" class="form-label">
                                <i class="fas fa-map-marker-alt text-danger"></i> Pickup Location
                            </label>
                            <input type="text" class="form-control" id="pickup_location" name="pickup_location" required>
                        </div>

                        <div class="mb-3 location-input">
                            <label for="dropoff_location" class="form-label">
                                <i class="fas fa-map-marker-alt text-success"></i> Drop-off Location
                            </label>
                            <input type="text" class="form-control" id="dropoff_location" name="dropoff_location" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="cab">Select Cab</label>
                            <select class="form-select" id="cab" name="cab_id" required>
                                <option value="">Choose a cab</option>
                                <?php
                                // Fetch available cabs
                                $cabs = $booking->getAvailableCabs();
                                while ($cab = $cabs->fetch(PDO::FETCH_ASSOC)): ?>
                                    <option value="<?php echo $cab['id']; ?>">
                                        <?php echo htmlspecialchars($cab['name']); ?> - 
                                        <?php echo htmlspecialchars($cab['type']); ?> - 
                                        ₹<?php echo number_format($cab['price_per_km'], 2); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="fare-box">
                            <label class="form-label">Estimated Fare</label>
                            <div class="fare-amount">
                                <span class="rupee-symbol">₹</span>
                                <span id="estimated_fare">500.00</span>
                            </div>
                            <input type="hidden" name="estimated_fare" id="estimated_fare_input" value="500.00">
                            <small class="text-muted">Base fare (₹200) + ₹15 per km</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-car"></i> Book Now
                            </button>
                            <a href="user/dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const pickupInput = document.getElementById('pickup_location');
        const dropoffInput = document.getElementById('dropoff_location');
        const fareDisplay = document.getElementById('estimated_fare');
        const fareInput = document.getElementById('estimated_fare_input');

        function calculateSimpleFare() {
            // Indian Rupee fare calculation
            const baseFare = 200;  // Base fare in rupees
            const randomDistance = Math.random() * 10; // Simulated distance in km
            const farePerKm = 15;  // ₹15 per kilometer
            const estimatedFare = (baseFare + (randomDistance * farePerKm)).toFixed(2);
            
            fareDisplay.textContent = estimatedFare;
            fareInput.value = estimatedFare;
        }

        pickupInput.addEventListener('change', calculateSimpleFare);
        dropoffInput.addEventListener('change', calculateSimpleFare);

        // Form validation
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            if (!pickupInput.value || !dropoffInput.value) {
                e.preventDefault();
                alert('Please enter both pickup and drop-off locations');
            }
        });
    </script>
</body>
</html> 
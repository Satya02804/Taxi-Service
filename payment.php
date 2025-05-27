<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: user/login.php");
    exit();
}

include_once 'config/database.php';
include_once 'models/Booking.php';
include_once 'models/Payment.php';

// Create qrcodes directory if it doesn't exist
$qrcodes_dir = 'qrcodes';
if (!file_exists($qrcodes_dir)) {
    mkdir($qrcodes_dir, 0777, true);
}

$database = new Database();
$db = $database->getConnection();

$booking_id = $_GET['booking_id'] ?? 0;
$error = '';
$success = '';
$upi_id = 'yourupiid@bank'; // Replace with your UPI ID

// Generate unique QR code filename
$qr_code_path = 'qrcodes/qr_' . uniqid() . '.png';

$booking = new Booking($db);
$booking_details = $booking->getBookingDetails($booking_id);

// Add this after getting booking details
if ($booking_details) {
    error_log("Booking Details in payment.php: " . print_r($booking_details, true));
} else {
    error_log("No booking details found for ID: " . $booking_id);
}

// Generate UPI Payment Link
$upi_link = "upi://pay?pa=" . $upi_id . 
            "&pn=Taxi%20Service" . 
            "&am=" . $booking_details['fare'] . 
            "&cu=INR" . 
            "&tn=Booking%20ID%20" . $booking_id;

// Generate QR Code using GoQR.me API
$goqr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($upi_link);

// Use cURL to fetch the QR code
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $goqr_api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$qr_code_data = curl_exec($ch);
curl_close($ch);

if ($qr_code_data) {
    file_put_contents($qr_code_path, $qr_code_data);
} else {
    $error = "Failed to generate QR code";
}

$payment = new Payment($db);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $payment->booking_id = $booking_id;
        $payment->amount = $booking_details['fare'];
        $payment->payment_method = $_POST['payment_method'];
        $payment->transaction_id = uniqid('PAY');
        
        if ($payment->processPayment()) {
            $success = "Payment processed successfully!";
            // Redirect to receipt page after 2 seconds
            header("refresh:2;url=receipt.php?payment_id=" . $payment->payment_id);
        } else {
            $error = "Payment processing failed. Please try again.";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Taxi Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        #upiDetails {
            display: none;
            transition: all 0.3s ease;
        }
        .qr-code-container {
            text-align: center;
            margin: 20px 0;
        }
        .qr-code-container img {
            max-width: 300px;
            margin: 0 auto;
        }
        #cardDetails {
            display: none;
            transition: all 0.3s ease;
        }
        
        #cardDetails input {
            padding: 0.5rem;
            font-size: 1rem;
        }
        
        #cardDetails .card {
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        #cardNumber {
            letter-spacing: 1px;
        }
        
        #cvv {
            width: 100px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Payment Details</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Ride Details</h5>
                                <p><strong>From:</strong> <?php echo htmlspecialchars($booking_details['pickup_location']); ?></p>
                                <p><strong>To:</strong> <?php echo htmlspecialchars($booking_details['dropoff_location']); ?></p>
                                <p><strong>Driver:</strong> 
                                    <?php 
                                    if (!empty($booking_details['driver_name'])) {
                                        echo htmlspecialchars($booking_details['driver_name']);
                                    } else {
                                        echo "Not assigned yet";
                                    }
                                    ?>
                                </p>
                                <?php if (!empty($booking_details['vehicle_number'])): ?>
                                <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($booking_details['vehicle_number']); ?> 
                                    (<?php echo htmlspecialchars($booking_details['vehicle_model']); ?>)</p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h5>Amount to Pay</h5>
                                <h2 class="text-primary">₹<?php echo number_format($booking_details['fare'], 2); ?></h2>
                            </div>
                        </div>

                        <form method="POST" id="paymentForm">
                            <div class="mb-3">
                                <label class="form-label">Select Payment Method</label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check payment-option">
                                            <input class="form-check-input" type="radio" name="payment_method" value="cash" id="cashPayment" checked>
                                            <label class="form-check-label" for="cashPayment">
                                                <i class="fas fa-money-bill-wave"></i> Cash
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check payment-option">
                                            <input class="form-check-input" type="radio" name="payment_method" value="upi" id="upiPayment">
                                            <label class="form-check-label" for="upiPayment">
                                                <i class="fas fa-mobile-alt"></i> UPI
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check payment-option">
                                            <input class="form-check-input" type="radio" name="payment_method" value="card" id="cardPayment">
                                            <label class="form-check-label" for="cardPayment">
                                                <i class="fas fa-credit-card"></i> Card
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="upiDetails" class="mt-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">UPI Payment Details</h5>
                                        <p class="card-text">UPI ID: <?php echo htmlspecialchars($upi_id); ?></p>
                                        <p class="card-text">Amount: ₹<?php echo number_format($booking_details['fare'], 2); ?></p>
                                        <div class="qr-code-container">
                                            <img src="<?php echo htmlspecialchars($qr_code_path); ?>" alt="UPI QR Code" class="img-fluid">
                                        </div>
                                        <p class="text-muted text-center">Scan this QR code with any UPI app to pay</p>
                                    </div>
                                </div>
                            </div>

                            <div id="cardDetails" class="mt-4" style="display: none;">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Card Payment Details</h5>
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Card Number</label>
                                                <input type="text" class="form-control" id="cardNumber" name="card_number" 
                                                       pattern="[0-9]{4} [0-9]{4} [0-9]{4} [0-9]{4}" 
                                                       maxlength="19" 
                                                       placeholder="1234 5678 9012 3456">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Expiry Date</label>
                                                <input type="text" class="form-control" id="expiryDate" name="expiry_date" 
                                                       pattern="(0[1-9]|1[0-2])\/[0-9]{2}" placeholder="MM/YY" maxlength="5">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">CVV</label>
                                                <input type="text" class="form-control" id="cvv" name="cvv" 
                                                       pattern="[0-9]{3,4}" maxlength="4" placeholder="123">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Card Holder Name</label>
                                            <input type="text" class="form-control" id="cardHolderName" name="card_holder_name" 
                                                   placeholder="Name on card">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Process Payment</button>
                                <a href="user/dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show/hide payment details when payment method changes
        document.querySelectorAll('input[name="payment_method"]').forEach((elem) => {
            elem.addEventListener("change", function(event) {
                const upiDetails = document.getElementById('upiDetails');
                const cardDetails = document.getElementById('cardDetails');
                
                // Hide both initially
                upiDetails.style.display = 'none';
                cardDetails.style.display = 'none';
                
                // Show the selected payment method details
                if (event.target.value === 'upi') {
                    upiDetails.style.display = 'block';
                } else if (event.target.value === 'card') {
                    cardDetails.style.display = 'block';
                }
            });
        });

        // Update the card number formatting
        document.getElementById('cardNumber').addEventListener('input', function(e) {
            // Remove any non-digit characters except spaces
            let value = e.target.value.replace(/[^\d\s]/g, '');
            // Remove extra spaces
            value = value.replace(/\s+/g, ' ').trim();
            // Split into groups of 4 and join with spaces
            let formatted = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            // Limit to 19 characters (16 digits + 3 spaces)
            formatted = formatted.substring(0, 19);
            e.target.value = formatted;
        });

        // Expiry date formatting
        document.getElementById('expiryDate').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0,2) + '/' + value.slice(2);
            }
            e.target.value = value;
        });

        // Update the form validation for card number
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            
            if (paymentMethod === 'card') {
                const cardNumber = document.getElementById('cardNumber').value.replace(/\s/g, '');
                const expiryDate = document.getElementById('expiryDate').value;
                const cvv = document.getElementById('cvv').value;
                const cardHolderName = document.getElementById('cardHolderName').value;

                if (!/^\d{16}$/.test(cardNumber)) {
                    e.preventDefault();
                    alert('Please enter a valid 16-digit card number');
                    return;
                }

                if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiryDate)) {
                    e.preventDefault();
                    alert('Please enter a valid expiry date (MM/YY)');
                    return;
                }

                if (!/^\d{3,4}$/.test(cvv)) {
                    e.preventDefault();
                    alert('Please enter a valid CVV');
                    return;
                }

                if (!cardHolderName.trim()) {
                    e.preventDefault();
                    alert('Please enter the card holder name');
                    return;
                }
            }
        });
    </script>
</body>
</html> 
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: user/login.php");
    exit();
}

include_once 'config/database.php';
include_once 'models/Payment.php';

$database = new Database();
$db = $database->getConnection();

$payment_id = $_GET['payment_id'] ?? 0;
$payment = new Payment($db);
$payment_details = $payment->getPaymentDetails($payment_id);

// Verify payment belongs to current user
if (!$payment_details) {
    header("Location: user/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - Taxi Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .receipt {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .receipt-header {
            background-color: #28a745;
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 20px;
        }
        .receipt-body {
            padding: 30px;
        }
        .receipt-footer {
            background-color: #f8f9fa;
            border-radius: 0 0 10px 10px;
            padding: 20px;
        }
        .payment-status {
            font-size: 1.2rem;
            padding: 8px 15px;
            border-radius: 20px;
        }
        .divider {
            border-top: 2px dashed #dee2e6;
            margin: 20px 0;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="receipt">
                    <div class="receipt-header text-center">
                        <i class="fas fa-check-circle fa-3x mb-3"></i>
                        <h2 class="mb-0">Payment Successful</h2>
                    </div>
                    
                    <div class="receipt-body">
                        <div class="text-center mb-4">
                            <span class="badge bg-success payment-status">
                                Payment Completed
                            </span>
                            <p class="text-muted mt-2">
                                Transaction ID: <?php echo htmlspecialchars($payment_details['transaction_id']); ?>
                            </p>
                        </div>

                        <div class="divider"></div>

                        <div class="row">
                            <div class="col-md-6">
                                <h5>Ride Details</h5>
                                <p><strong>From:</strong> <?php echo htmlspecialchars($payment_details['pickup_location']); ?></p>
                                <p><strong>To:</strong> <?php echo htmlspecialchars($payment_details['dropoff_location']); ?></p>
                                <p><strong>Driver:</strong> <?php echo htmlspecialchars($payment_details['driver_name']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h5>Payment Information</h5>
                                <p><strong>Amount Paid:</strong> ₹<?php echo number_format($payment_details['amount'], 2); ?></p>
                                <p><strong>Payment Method:</strong> <?php echo ucfirst(htmlspecialchars($payment_details['payment_method'])); ?></p>
                                <p><strong>Date:</strong> <?php echo date('d M Y, h:i A', strtotime($payment_details['payment_time'])); ?></p>
                            </div>
                        </div>

                        <div class="divider"></div>

                        <div class="text-center">
                            <p class="mb-0">Thank you for choosing our service!</p>
                            <small class="text-muted">A copy of this receipt has been sent to your email.</small>
                        </div>
                    </div>

                    <div class="receipt-footer text-center">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <button onclick="window.print()" class="btn btn-outline-dark me-md-2">
                                <i class="fas fa-print"></i> Print Receipt
                            </button>
                            <a href="user/dashboard.php" class="btn btn-primary">
                                <i class="fas fa-home"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
<?php
session_start();
// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include_once '../config/database.php';
include_once '../models/User.php';
include_once '../models/Driver.php';
include_once '../models/Payment.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$driver = new Driver($db);
$payment = new Payment($db);

// Fetch reports
$userReport = $user->getUserReport();
$driverReport = $driver->getDriverReport();
$revenueReport = $payment->getRevenueReport();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Reports - Taxi Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <!-- <h2 class="mb-4">Admin Reports</h2> -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php"><i class="fas fa-taxi"></i> Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="drivers.php">Drivers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bookings.php">Bookings</a>
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

        <!-- User Report -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">User Report</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Total Users</th>
                            <th>Active Users</th>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo $userReport['total_users']; ?></td>
                            <td><?php echo $userReport['active_users']; ?></td>
                            
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Driver Report -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Driver Report</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Total Drivers</th>
                            <th>Active Drivers</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo $driverReport['total_drivers']; ?></td>
                            <td><?php echo $driverReport['active_drivers']; ?></td>
                           
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Revenue Report -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">Revenue Report</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Total Revenue</th>
                            <th>Today's Revenue</th>
                            <th>This Month's Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>₹<?php echo number_format($revenueReport['total_revenue'], 2); ?></td>
                            <td>₹<?php echo number_format($revenueReport['today_revenue'], 2); ?></td>
                            <td>₹<?php echo number_format($revenueReport['month_revenue'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
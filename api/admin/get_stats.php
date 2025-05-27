<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/Admin.php';

$database = new Database();
$db = $database->getConnection();

$admin = new Admin($db);
$stats = $admin->getDashboardStats();

// Add rupee symbol information to the API response
$stats['currency_symbol'] = '₹';
$stats['currency_code'] = 'INR';

http_response_code(200);
echo json_encode($stats);
?> 
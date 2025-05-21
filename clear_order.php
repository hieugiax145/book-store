<?php
include 'config.php';
session_start();

// Get the order ID from POST data
$data = json_decode(file_get_contents('php://input'), true);
$donHangId = isset($data['donHangId']) ? $data['donHangId'] : '';

if(!empty($donHangId)) {
   // Delete the pending order from database
   mysqli_query($conn, "DELETE FROM don_hang WHERE donHangId = '$donHangId' AND trangThai = 'Pending'") or die('query failed');
   mysqli_query($conn, "DELETE FROM chi_tiet_don_hang WHERE donHangId = '$donHangId'") or die('query failed');
}

// Clear order data from session
unset($_SESSION['order_data']);
unset($_SESSION['cart_total']);
unset($_SESSION['order_timeout']);

// Return success response
header('Content-Type: application/json');
echo json_encode(['success' => true]);
?> 
<?php
include 'config.php';
session_start();
$user_id = $_SESSION['user_id'];
if(!isset($user_id) || !isset($_SESSION['order_data'])){
   header('location:checkout.php');
   exit;
}
$order_data = $_SESSION['order_data'];
$cart_total = $_SESSION['cart_total'] ?? 0;

// Xử lý xác nhận đã quét QR
if(isset($_POST['qr_confirm'])){
   // Lưu đơn hàng vào database
   $name = $order_data['name'];
   $number = $order_data['number'];
   $email = $order_data['email'];
   $method = $order_data['method'];
   $address = $order_data['address'];
   $placed_on = $order_data['placed_on'];
   $total_products = $order_data['total_products'];
   $total_price = $order_data['total_price'];

   mysqli_query($conn, "INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, placed_on) VALUES('$user_id', '$name', '$number', '$email', '$method', '$address', '$total_products', '$total_price', '$placed_on')") or die('query failed');
   mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
   unset($_SESSION['order_data']);
   unset($_SESSION['cart_total']);
   echo "<script>alert('order placed successfully!');window.location='shop.php';</script>";
   exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Xác nhận thanh toán QR</title>
   <link rel="stylesheet" href="css/style.css">
   <style>
      .qr-container { text-align: center; margin-top: 40px; }
      .grand-total { font-size: 1.5em; margin-bottom: 20px; }
      #countdown { font-size: 2em; color: red; margin-bottom: 20px; }
      .qr-img { width: 200px; height: 200px; margin-bottom: 20px; }
      .btn { padding: 10px 30px; font-size: 1.2em; }
   </style>
   <script>
      let timeLeft = 30;
      function countdown() {
         if(timeLeft <= 0){
            alert('order placed failly');
            window.location = 'checkout.php';
         } else {
            document.getElementById('countdown').innerText = timeLeft + 's';
            timeLeft--;
            setTimeout(countdown, 1000);
         }
      }
      window.onload = countdown;
   </script>
</head>
<body>
   <div class="qr-container">
      <div class="grand-total">Grand Total: <span>$<?php echo $cart_total; ?>/-</span></div>
      <div id="countdown">30s</div>
      <div>
         <img src="images/qr_code.png" alt="QR Code" class="qr-img">
         <!-- Thay src bằng đường dẫn ảnh QR thực tế -->
      </div>
      <form method="post">
         <button type="submit" name="qr_confirm" class="btn">Đã quét QR</button>
      </form>
   </div>
</body>
</html>
<?php
include 'config.php';
session_start();

if(!isset($_SESSION['user_id'])){
   header('location:login.php');
   exit;
}

$status = isset($_GET['status']) ? $_GET['status'] : '';
$order_data = isset($_SESSION['order_data']) ? $_SESSION['order_data'] : null;

if($status === 'pending' && $order_data) {
   // Set timeout in session
   $_SESSION['order_timeout'] = time() + 30; // 30 seconds from now
   
   // Check if time has expired
   if(time() >= $_SESSION['order_timeout']) {
      // Delete the pending order from database
      if(isset($order_data['donHangId'])) {
         mysqli_query($conn, "DELETE FROM don_hang WHERE donHangId = '{$order_data['donHangId']}' AND trangThai = 'Pending'") or die('query failed');
         mysqli_query($conn, "DELETE FROM chi_tiet_don_hang WHERE donHangId = '{$order_data['donHangId']}'") or die('query failed');
      }
      
      // Clear order data
      unset($_SESSION['order_data']);
      unset($_SESSION['cart_total']);
      unset($_SESSION['order_timeout']);
      
      // Redirect back to checkout with error
      header('Location: checkout.php?error=timeout');
      exit;
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Trạng thái đơn hàng</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
   .order-status {
      max-width: 800px;
      margin: 2rem auto;
      padding: 2rem;
      background-color: var(--white);
      border-radius: .5rem;
      box-shadow: var(--box-shadow);
      border: var(--border);
   }

   .qr-container {
      position: relative;
      margin: 20px auto;
      width: 200px;
      height: 200px;
   }

   .qr-container img {
      width: 100%;
      height: 100%;
      object-fit: contain;
   }

   .countdown {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      font-size: 24px;
      font-weight: bold;
      color: #333;
      background: rgba(255,255,255,0.8);
      padding: 10px 20px;
      border-radius: 20px;
   }

   .order-info {
      background-color: var(--light-bg);
      padding: 2rem;
      border-radius: .5rem;
      border: var(--border);
      margin-top: 2rem;
   }

   .order-info p {
      font-size: 1.8rem;
      color: var(--black);
      margin-bottom: 1rem;
   }

   .order-info p span {
      color: var(--red);
      font-weight: 500;
   }
   </style>
   <script>
   // Handle page unload/close
   window.addEventListener('beforeunload', function(e) {
      if(document.querySelector('.qr-container')) {
         // Send cleanup request
         fetch('clear_order.php', {
            method: 'POST',
            headers: {
               'Content-Type': 'application/json',
            },
            body: JSON.stringify({
               donHangId: '<?php echo $order_data['donHangId'] ?? ''; ?>'
            })
         });
      }
   });
   </script>
</head>
<body>

<?php include 'header.php'; ?>

<div class="heading">
   <h3>Trạng thái đơn hàng</h3>
   <p> <a href="home.php">Trang chủ</a> / Trạng thái đơn hàng </p>
</div>

<div class="order-status">
   <?php if($status === 'pending' && $order_data): ?>
      <h2>Quét mã QR để thanh toán</h2>
      <div class="qr-container">
         <img src="images/qrcode-default.png" alt="QR Code">
         <div class="countdown">
            <?php
            $time_left = $_SESSION['order_timeout'] - time();
            echo $time_left > 0 ? $time_left : 0;
            ?>
         </div>
      </div>
      <div class="order-info">
         <p>Số tiền: <span><?php echo number_format($order_data['total_price']); ?>đ</span></p>
         <p>Mã đơn hàng: <span><?php echo $order_data['donHangId']; ?></span></p>
      </div>

      <meta http-equiv="refresh" content="1">
   <?php elseif($status === 'success'): ?>
      <h2>Đặt hàng thành công!</h2>
      <p>Đơn hàng của bạn đã được xác nhận.</p>
      <a href="orders.php" class="btn">Xem đơn hàng</a>
   <?php else: ?>
      <h2>Lỗi</h2>
      <p>Có lỗi xảy ra khi xử lý đơn hàng.</p>
      <a href="checkout.php" class="btn">Quay lại trang thanh toán</a>
   <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

</body>
</html> 
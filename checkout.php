<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
   exit;
}

// Display error message if QR payment timed out
if(isset($_GET['error']) && $_GET['error'] == 'timeout') {
   echo '<div class="message"><span>Thanh toán QR đã hết hạn. Vui lòng thử lại.</span><i class="fas fa-times" onclick="this.parentElement.style.display=\'none\'"></i></div>';
}

// Check if cart is empty and redirect to home
$check_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE khachHangId = '$user_id'") or die('query failed');
if(mysqli_num_rows($check_cart) == 0) {
   header('location:home.php');
   exit();
}

// Handle form submission
if(isset($_POST['order_btn'])){
   // Create the main order record
   $diaChiNhanHang = mysqli_real_escape_string($conn, $_POST['address']);
   $hinhThucThanhToan = mysqli_real_escape_string($conn, $_POST['method']);
   $tenKhachHang = mysqli_real_escape_string($conn, $_POST['name']);
   $sdt = mysqli_real_escape_string($conn, $_POST['number']);
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   
   $insert_order = mysqli_query($conn, "INSERT INTO don_hang (
      khachHangId, 
      tongTien,
      diaChiNhanHang,
      ngayBan,
      trangThai,
      hinhThucThanhToan,
      tenKhachHang,
      sdt,
      email,
      phanLoai
   ) VALUES (
      '$user_id',
      0,
      '$diaChiNhanHang',
      CURDATE(),
      'Pending',
      '$hinhThucThanhToan',
      '$tenKhachHang',
      '$sdt',
      '$email',
      'Online'
   )") or die('query failed');

   if($insert_order){
      $donHangId = mysqli_insert_id($conn);
      $cart_total = 0;
      
      // Get cart items
      $cart_query = mysqli_query($conn, "SELECT c.*, s.ten as name, s.donGia as price 
         FROM `cart` c 
         JOIN sach s ON c.sachId = s.sachId 
         WHERE c.khachHangId = '$user_id'") or die('query failed');
      
      if(mysqli_num_rows($cart_query) > 0){
         while($cart_item = mysqli_fetch_assoc($cart_query)){
            $sachId = $cart_item['sachId'];
            $donGia = $cart_item['price'];
            $soLuong = $cart_item['soLuong'];
            $sub_total = ($donGia * $soLuong);
            $cart_total += $sub_total;

            // Insert order details
            mysqli_query($conn, "INSERT INTO chi_tiet_don_hang (
               donHangId,
               sachId,
               donGia,
               soLuong
            ) VALUES (
               '$donHangId',
               '$sachId',
               '$donGia',
               '$soLuong'
            )") or die('query failed');
         }

         // Update total amount in don_hang
         mysqli_query($conn, "UPDATE don_hang SET tongTien = '$cart_total' WHERE donHangId = '$donHangId'") or die('query failed');

         // Store order data in session
         $order_data = [
            'donHangId' => $donHangId,
            'name' => $tenKhachHang,
            'number' => $sdt,
            'email' => $email,
            'method' => $hinhThucThanhToan,
            'address' => $diaChiNhanHang,
            'total_price' => $cart_total
         ];
         $_SESSION['order_data'] = $order_data;
         $_SESSION['cart_total'] = $cart_total;

         // Clear the cart after successful order
         mysqli_query($conn, "DELETE FROM cart WHERE khachHangId = '$user_id'") or die('query failed');

         // Redirect based on payment method
         if($hinhThucThanhToan == 'QR'){
            header('Location: order_status.php?status=pending');
         } else {
            header('Location: order_status.php?status=success');
         }
         exit;
      }
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>checkout</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<div class="heading">
   <h3>Thanh toán</h3>
   <p> <a href="home.php">Trang chủ</a> / Thanh toán </p>
</div>

<div class="checkout-container">
   <div class="checkout-wrapper">
      <!-- QR Payment Modal -->
      <div id="qrModal" class="modal">
         <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Quét mã QR để thanh toán</h2>
            <div class="qr-code">
               <img src="./uploaded_img/qrcode-default.png" alt="QR Code">
            </div>
            <div class="countdown">
               <p>Mã QR sẽ hết hạn sau: <span id="timer">30</span> giây</p>
            </div>
            <button id="successBtn" class="btn">Thanh toán thành công</button>
         </div>
      </div>

      <div class="display-order">
         <h3>Danh sách sách</h3>
         <?php  
            $grand_total = 0;
            $select_cart = mysqli_query($conn, "SELECT c.*, s.ten as name, s.donGia as price 
               FROM `cart` c 
               JOIN sach s ON c.sachId = s.sachId 
               WHERE c.khachHangId = '$user_id'") or die('query failed');
            if(mysqli_num_rows($select_cart) > 0){
               while($fetch_cart = mysqli_fetch_assoc($select_cart)){
                  $total_price = ($fetch_cart['price'] * $fetch_cart['soLuong']);
                  $grand_total += $total_price;
         ?>
             <div class="cart-item">
                <div class="item-name"><?php echo $fetch_cart['name']; ?></div>
                <div class="item-details">
                   <span class="item-price"><?php echo number_format($fetch_cart['price']); ?>đ</span>
                   <span class="item-quantity">x <?php echo $fetch_cart['soLuong']; ?></span>
                   <span class="item-total"><?php echo number_format($total_price); ?>đ</span>
                </div>
             </div>
         <?php
               }
            }else{
               echo '<p class="empty">Giỏ hàng trống</p>';
            }
         ?>
         <div class="grand-total"> 
            Tổng tiền: <span><?php echo number_format($grand_total); ?>đ</span>
         </div>
      </div>

      <section class="checkout">
         <form action="" method="post">
            <h3>Thông tin đơn hàng</h3>
            <div class="flex">
               <div class="inputBox">
                  <span>Tên :</span>
                  <input type="text" name="name" required placeholder="Nhập tên của bạn">
               </div>
               <div class="inputBox">
                  <span>Số điện thoại :</span>
                  <input type="number" name="number" required placeholder="Nhập số điện thoại">
               </div>
               <div class="inputBox">
                  <span>Email :</span>
                  <input type="email" name="email" required placeholder="Nhập email">
               </div>
               <div class="inputBox">
                  <span>Địa chỉ :</span>
                  <input type="text" name="address" required placeholder="Nhập địa chỉ nhận hàng">
               </div>
               <div class="inputBox">
                  <span>Phương thức thanh toán :</span>
                  <select name="method">
                     <option value="COD">Thanh toán khi nhận hàng (COD)</option>
                     <option value="QR">Thanh toán qua mã QR</option>
                  </select>
               </div>
            </div>
            <input type="submit" value="Đặt hàng" class="btn" name="order_btn">
         </form>
      </section>
   </div>
</div>

<style>
.checkout-container {
   padding: 2rem 9%;
}

.checkout-wrapper {
   display: flex;
   gap: 2rem;
   align-items: flex-start;
}

.display-order {
   flex: 1;
   background: #fff;
   padding: 2rem;
   border-radius: .5rem;
   box-shadow: 0 .5rem 1rem rgba(0,0,0,.1);
}

.display-order h3 {
   color: #333;
   font-size: 2.8rem;
   margin-bottom: 2.5rem;
   padding-bottom: 1rem;
   border-bottom: 3px solid var(--main-color);
}

.cart-item {
   padding: 2rem;
   border-bottom: 1px solid #eee;
   display: flex;
   justify-content: space-between;
   align-items: center;
}

.cart-item:last-child {
   border-bottom: none;
}

.item-name {
   font-size: 2rem;
   color: #333;
   flex: 1;
}

.item-details {
   display: flex;
   align-items: center;
   gap: 2.5rem;
}

.item-price {
   color: #666;
   font-size: 1.8rem;
}

.item-quantity {
   color: #888;
   font-size: 1.8rem;
}

.item-total {
   color: var(--main-color);
   font-weight: bold;
   min-width: 200px;
   text-align: right;
   font-size: 2rem;
}

.checkout {
   flex: 1;
   background: #fff;
   padding: 2rem;
   border-radius: .5rem;
   box-shadow: 0 .5rem 1rem rgba(0,0,0,.1);
}

.checkout .flex {
   display: grid;
   grid-template-columns: repeat(2, 1fr);
   gap: 1.5rem;
}

.checkout .inputBox {
   width: 100%;
}

.checkout .inputBox:nth-child(3),
.checkout .inputBox:nth-child(4),
.checkout .inputBox:nth-child(5) {
   grid-column: span 2;
}

.checkout .inputBox input,
.checkout .inputBox select {
   width: 100%;
   padding: 1rem;
   margin-top: .5rem;
   border: 1px solid #eee;
   border-radius: .5rem;
}

.grand-total {
   margin-top: 3rem;
   padding-top: 2rem;
   border-top: 3px solid #eee;
   font-size: 2.2rem;
   font-weight: bold;
   display: flex;
   justify-content: space-between;
   align-items: center;
}

.grand-total span {
   color: var(--main-color);
   font-size: 2.5rem;
}

@media (max-width: 768px) {
   .checkout-wrapper {
      flex-direction: column;
   }
   
   .checkout .flex {
      grid-template-columns: 1fr;
   }
   
   .checkout .inputBox:nth-child(3),
   .checkout .inputBox:nth-child(4),
   .checkout .inputBox:nth-child(5) {
      grid-column: span 1;
   }
}

/* Modal styles */
.modal {
   display: none;
   position: fixed;
   z-index: 1000;
   left: 0;
   top: 0;
   width: 100%;
   height: 100%;
   background-color: rgba(0,0,0,0.5);
}

.modal-content {
   background-color: #fefefe;
   margin: 15% auto;
   padding: 20px;
   border: 1px solid #888;
   width: 80%;
   max-width: 400px;
   border-radius: 8px;
   text-align: center;
}

.close {
   color: #aaa;
   float: right;
   font-size: 28px;
   font-weight: bold;
   cursor: pointer;
}

.close:hover,
.close:focus {
   color: black;
   text-decoration: none;
   cursor: pointer;
}

.qr-code {
   margin: 20px 0;
}

.qr-code img {
   max-width: 200px;
   height: auto;
}

.countdown {
   margin-top: 20px;
   font-size: 1.2rem;
}

#timer {
   color: var(--main-color);
   font-weight: bold;
}


</style>

<?php include 'footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
   const form = document.querySelector('form');
   const modal = document.getElementById('qrModal');
   const closeBtn = document.querySelector('.close');
   const successBtn = document.getElementById('successBtn');
   let countdownInterval;

   // Function to clear countdown and hide modal
   function closeModal() {
      if (countdownInterval) {
         clearInterval(countdownInterval);
         countdownInterval = null;
      }
      modal.style.display = 'none';
   }

   form.addEventListener('submit', function(e) {
      const paymentMethod = document.querySelector('select[name="method"]').value;
      
      if (paymentMethod === 'QR') {
         e.preventDefault();
         closeModal(); // Clear any existing interval
         modal.style.display = 'block';
         startCountdown();
      }
   });

   successBtn.addEventListener('click', function() {
      closeModal();
      form.submit();
   });

   closeBtn.addEventListener('click', closeModal);

   function startCountdown() {
      let timeLeft = 30;
      const timerElement = document.getElementById('timer');
      timerElement.textContent = timeLeft;
      
      countdownInterval = setInterval(function() {
         timeLeft--;
         timerElement.textContent = timeLeft;
         
         if (timeLeft <= 0) {
            closeModal();
            window.location.href = 'checkout.php?error=timeout';
         }
      }, 1000);
   }

   // Close modal when clicking outside
   window.addEventListener('click', function(e) {
      if (e.target === modal) {
         closeModal();
      }
   });
});
</script>

</body>
</html>
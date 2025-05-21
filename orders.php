<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Đơn hàng</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<div class="heading">
   <h3>Đơn hàng</h3>
   <p> <a href="home.php">home</a> / orders </p>
</div>

<section class="placed-orders">

   <h1 class="title">Đơn hàng đã đặt</h1>

   <div class="box-container">

      <?php
         $order_query = mysqli_query($conn, "SELECT dh.*, nd.hoTen, nd.email, nd.sdt 
            FROM don_hang dh 
            JOIN nguoi_dung nd ON dh.khachHangId = nd.nguoiDungId 
            WHERE dh.khachHangId = '$user_id'") or die('query failed');
         if(mysqli_num_rows($order_query) > 0){
            while($fetch_orders = mysqli_fetch_assoc($order_query)){
      ?>
      <div class="box">
         <p> Ngày đặt hàng : <span><?php echo $fetch_orders['ngayBan']; ?></span> </p>
         <p> Tên khách hàng : <span><?php echo $fetch_orders['hoTen']; ?></span> </p>
         <p> Số điện thoại : <span><?php echo $fetch_orders['sdt']; ?></span> </p>
         <p> Email : <span><?php echo $fetch_orders['email']; ?></span> </p>
         <p> Địa chỉ nhận hàng : <span><?php echo $fetch_orders['diaChiNhanHang']; ?></span> </p>
         <p> Hình thức thanh toán : <span><?php echo $fetch_orders['hinhThucThanhToan']; ?></span> </p>
         <p> Phân loại : <span><?php echo $fetch_orders['phanLoai']; ?></span> </p>
         <p> Tổng tiền : <span>$<?php echo $fetch_orders['tongTien']; ?>/-</span> </p>
         <p> Trạng thái : <span style="color:<?php if($fetch_orders['trangThai'] == 'Pending'){ echo 'red'; }else{ echo 'green'; } ?>;"><?php echo $fetch_orders['trangThai']; ?></span> </p>
         <?php if(!empty($fetch_orders['ghiChu'])): ?>
         <p> Ghi chú : <span><?php echo $fetch_orders['ghiChu']; ?></span> </p>
         <?php endif; ?>
      </div>
      <?php
       }
      }else{
         echo '<p class="empty">Không có đơn hàng nào!</p>';
      }
      ?>
   </div>

</section>

<?php include 'footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>
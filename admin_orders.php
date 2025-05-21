<?php

include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:login.php');
}

if (isset($_POST['update_order'])) {

   $order_update_id = $_POST['order_id'];
   $update_status = $_POST['update_status'];
   mysqli_query($conn, "UPDATE `don_hang` SET trangThai = '$update_status' WHERE donHangId = '$order_update_id'") or die('query failed');
   $message[] = 'Trạng thái đơn hàng đã được cập nhật!';
}

if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   mysqli_query($conn, "DELETE FROM `don_hang` WHERE donHangId = '$delete_id'") or die('query failed');
   header('location:admin_orders.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Quản lý đơn hàng</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>

<body>

   <?php include 'admin_header.php'; ?>

   <section class="orders">

      <h1 class="title">Quản lý đơn hàng</h1>

      <div class="box-container">
         <?php
         $select_orders = mysqli_query($conn, "SELECT dh.*, 
                                           CASE 
                                              WHEN dh.phanLoai != 'In-store' THEN nd.hoTen 
                                              ELSE NULL 
                                           END as tenKhachHang,
                                           CASE 
                                              WHEN dh.phanLoai != 'In-store' THEN nd.sdt 
                                              ELSE NULL 
                                           END as sdt,
                                           CASE 
                                              WHEN dh.phanLoai != 'In-store' THEN nd.email 
                                              ELSE NULL 
                                           END as email
                                           FROM `don_hang` dh 
                                           LEFT JOIN `nguoi_dung` nd ON dh.khachHangId = nd.nguoiDungId AND dh.phanLoai != 'In-store'") or die('query failed');
         if (mysqli_num_rows($select_orders) > 0) {
            while ($fetch_orders = mysqli_fetch_assoc($select_orders)) {
         ?>
               <div class="box">
                  <p> Mã đơn hàng : <span><?php echo $fetch_orders['donHangId']; ?></span> </p>
                  <?php if ($fetch_orders['phanLoai'] != 'In-store'): ?>
                     <p> Mã khách hàng : <span><?php echo $fetch_orders['khachHangId']; ?></span> </p>
                     <p> Tên khách hàng : <span><?php echo $fetch_orders['tenKhachHang']; ?></span> </p>
                     <p> Số điện thoại : <span><?php echo $fetch_orders['sdt']; ?></span> </p>
                     <p> Email : <span><?php echo $fetch_orders['email']; ?></span> </p>
                  <?php endif; ?>
                  <p> Ngày đặt hàng : <span><?php echo $fetch_orders['ngayBan']; ?></span> </p>
                  <p> Địa chỉ : <span><?php echo $fetch_orders['diaChiNhanHang']; ?></span> </p>
                  <p> Tổng tiền : <span><?php echo number_format($fetch_orders['tongTien'], 0, ',', '.'); ?> VNĐ</span> </p>
                  <p> Phân loại : <span><?php echo $fetch_orders['phanLoai']; ?></span> </p>
                  <p> Hình thức thanh toán : <span><?php echo $fetch_orders['hinhThucThanhToan']; ?></span> </p>
                  <p> Ghi chú : <span><?php echo $fetch_orders['ghiChu']; ?></span> </p>
                  <form action="" method="post">
                     <input type="hidden" name="order_id" value="<?php echo $fetch_orders['donHangId']; ?>">
                     <select name="update_status" <?php echo ($fetch_orders['trangThai'] == 'Completed') ? 'disabled' : ''; ?>>
                        <option value="" selected disabled><?php echo $fetch_orders['trangThai']; ?></option>
                        <?php if ($fetch_orders['trangThai'] != 'Delivering'): ?>
                           <option value="Pending">Pending</option>
                        <?php endif; ?>
                        <option value="Delivering">Delivering</option>
                        <option value="Completed">Completed</option>
                     </select>
                     <input type="submit" value="Cập nhật" name="update_order" class="option-btn" <?php echo ($fetch_orders['trangThai'] == 'Completed') ? 'disabled' : ''; ?>>
                     <a href="admin_orders.php?delete=<?php echo $fetch_orders['donHangId']; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa đơn hàng này?');" class="delete-btn">Xóa</a>
                  </form>
               </div>
         <?php
            }
         } else {
            echo '<p class="empty">Chưa có đơn hàng nào!</p>';
         }
         ?>
      </div>

   </section>

   <!-- custom admin js file link  -->
   <script src="js/admin_script.js"></script>

</body>

</html>
<?php

include 'config.php';

session_start();

if(isset($_POST['add_to_cart'])){
   if(!isset($_SESSION['user_id'])){
      header('location:login.php');
      exit();
   }

   $khachHangId = $_SESSION['user_id'];
   $sachId = $_POST['sach_id'];
   $soLuong = $_POST['soLuong'];

   $check_cart_numbers = mysqli_query($conn, "SELECT * FROM `cart` WHERE sachId = '$sachId' AND khachHangId = '$khachHangId'") or die('query failed');

   if(mysqli_num_rows($check_cart_numbers) > 0){
      $message[] = 'Sách đã có trong giỏ hàng!';
   }else{
      mysqli_query($conn, "INSERT INTO `cart`(khachHangId, sachId, soLuong) VALUES('$khachHangId', '$sachId', '$soLuong')") or die('query failed');
      $message[] = 'Đã thêm sách vào giỏ hàng!';
   }
};

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Tìm kiếm sách</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<div class="heading">
   <h3>Tìm kiếm sách</h3>
   <p> <a href="home.php">Trang chủ</a> / Tìm kiếm </p>
</div>

<section class="search-form">
   <form action="" method="post">
      <input type="text" name="search" placeholder="Tìm kiếm sách..." class="box">
      <input type="submit" name="submit" value="Tìm kiếm" class="btn">
   </form>
</section>

<section class="products" style="padding-top: 0;">

   <div class="box-container">
   <?php
      if(isset($_POST['submit'])){
         $search_item = $_POST['search'];
         $select_products = mysqli_query($conn, "SELECT * FROM `sach` WHERE ten LIKE '%{$search_item}%' OR tacGia LIKE '%{$search_item}%' OR nhaXuatBan LIKE '%{$search_item}%'") or die('query failed');
         if(mysqli_num_rows($select_products) > 0){
         while($fetch_product = mysqli_fetch_assoc($select_products)){
   ?>
   <form action="" method="post" class="box">
      <img src="uploaded_img/<?php echo $fetch_product['image']; ?>" alt="" class="image" style="object-fit: cover; height: 30rem; width: 100%;">
      <div class="name"><?php echo $fetch_product['ten']; ?></div>
      <div class="author">Tác giả: <?php echo $fetch_product['tacGia']; ?></div>
      <div class="publisher">NXB: <?php echo $fetch_product['nhaXuatBan']; ?></div>
      <div class="price"><?php echo number_format($fetch_product['donGia'], 0, ',', '.'); ?> VNĐ</div>
      <input type="number" class="qty" name="soLuong" min="1" max="<?php echo $fetch_product['soLuong']; ?>" value="1">
      <input type="hidden" name="sach_id" value="<?php echo $fetch_product['sachId']; ?>">
      <input type="hidden" name="ten" value="<?php echo $fetch_product['ten']; ?>">
      <input type="hidden" name="donGia" value="<?php echo $fetch_product['donGia']; ?>">
      <input type="hidden" name="image" value="<?php echo $fetch_product['image']; ?>">
      <input type="submit" class="btn" value="Thêm vào giỏ" name="add_to_cart">
   </form>
   <?php
            }
         }else{
            echo '<p class="empty">Không tìm thấy sách phù hợp!</p>';
         }
      }else{
         echo '<p class="empty">Vui lòng nhập từ khóa tìm kiếm!</p>';
      }
   ?>
   </div>

</section>

<?php include 'footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>
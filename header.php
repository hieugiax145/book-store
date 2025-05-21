<?php
if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<header class="header">

   <!-- <div class="header-1">
      <div class="flex">
         <div class="share">
            <a href="#" class="fab fa-facebook-f"></a>
            <a href="#" class="fab fa-twitter"></a>
            <a href="#" class="fab fa-instagram"></a>
            <a href="#" class="fab fa-linkedin"></a>
         </div>
         <?php if(isset($_SESSION['user_id'])): ?>
            <p><a href="profile.php">Tài khoản</a> | <a href="logout.php">Đăng xuất</a></p>
         <?php else: ?>
            <p><a href="login.php">Đăng nhập</a> | <a href="register.php">Đăng ký</a></p>
         <?php endif; ?>
      </div>
   </div> -->

   <div class="header-2">
      <div class="flex">
         <a href="home.php" class="logo">Book Shop Nhóm 7</a>

         <nav class="navbar">
            <a href="home.php">Home</a>
            <a href="about.php">Giới thiệu</a>
            <a href="shop.php">Cửa hàng</a>
            <a href="contact.php">Liên lạc</a>
            <a href="orders.php">Đơn hàng</a>
         </nav>

         <div class="icons">
            <div id="menu-btn" class="fas fa-bars"></div>
            <a href="search_page.php" class="fas fa-search"></a>
            <?php if(isset($_SESSION['user_id'])): ?>
               <div id="user-btn" class="fas fa-user"></div>
            <?php else: ?>
               <a href="login.php" class="fas fa-user"></a>
            <?php endif; ?>
            <?php
               if(isset($_SESSION['user_id'])) {
                  $select_cart_number = mysqli_query($conn, "SELECT * FROM `cart` WHERE khachHangId = '{$_SESSION['user_id']}'") or die('query failed');
                  $cart_rows_number = mysqli_num_rows($select_cart_number); 
               } else {
                  $cart_rows_number = 0;
               }
            ?>
            <a href="<?php echo isset($_SESSION['user_id']) ? 'cart.php' : 'login.php'; ?>"> 
               <i class="fas fa-shopping-cart"></i> 
               <span>(<?php echo $cart_rows_number; ?>)</span> 
            </a>
         </div>

         <?php if(isset($_SESSION['user_id'])): ?>
         <div class="user-box">
            <p>username : <span><?php echo $_SESSION['user_name']; ?></span></p>
            <p>email : <span><?php echo $_SESSION['user_email']; ?></span></p>
            <a href="logout.php" class="delete-btn">Đăng xuất</a>
         </div>
         <?php endif; ?>
      </div>
   </div>

</header>
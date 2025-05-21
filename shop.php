<?php

include 'config.php';

session_start();

if(isset($_POST['add_to_cart'])){
   if(!isset($_SESSION['user_id'])){
      header('location:login.php');
      exit();
   }

   $sach_id = $_POST['sach_id'];
   $sach_quantity = $_POST['sach_quantity'];

   // Check if book exists in cart
   $check_cart_numbers = mysqli_query($conn, "SELECT * FROM `cart` WHERE sachId = '$sach_id' AND khachHangId = '{$_SESSION['user_id']}'") or die('query failed');

   if(mysqli_num_rows($check_cart_numbers) > 0){
      $message[] = 'already added to cart!';
   }else{
      mysqli_query($conn, "INSERT INTO `cart`(khachHangId, sachId, soLuong) VALUES('{$_SESSION['user_id']}', '$sach_id', '$sach_quantity')") or die('query failed');
      $message[] = 'book added to cart!';
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Book Shop</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<div class="heading">
   <h3>Book Store</h3>
   <p> <a href="home.php">home</a> / shop </p>
</div>

<section class="products">

   <h1 class="title">Latest Books</h1>

   <div class="box-container">

      <?php  
         $select_books = mysqli_query($conn, "SELECT * FROM `sach`") or die('query failed');
         if(mysqli_num_rows($select_books) > 0){
            while($fetch_book = mysqli_fetch_assoc($select_books)){
      ?>
     <form action="" method="post" class="box">
      <img class="image" src="uploaded_img/<?php echo $fetch_book['image']; ?>" alt="" style="object-fit: cover; height: 30rem; width: 100%;">
      <div class="name"><?php echo $fetch_book['ten']; ?></div>
      <div class="author">Author: <?php echo $fetch_book['tacGia']; ?></div>
      <div class="publisher">Publisher: <?php echo $fetch_book['nhaXuatBan']; ?></div>
      <div class="year">Year: <?php echo $fetch_book['namXuatBan']; ?></div>
      <div class="price">$<?php echo $fetch_book['donGia']; ?></div>
      <input type="number" min="1" name="sach_quantity" value="1" class="qty">
      <input type="hidden" name="sach_id" value="<?php echo $fetch_book['sachId']; ?>">
      <input type="submit" value="Add to Cart" name="add_to_cart" class="btn">
     </form>
      <?php
         }
      }else{
         echo '<p class="empty">No books available!</p>';
      }
      ?>
   </div>

</section>

<?php include 'footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>
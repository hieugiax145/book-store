<?php

include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
};

if(isset($_POST['add_product'])){

   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $price = $_POST['price'];
   $author = mysqli_real_escape_string($conn, $_POST['author']);
   $publish_date = mysqli_real_escape_string($conn, $_POST['publish_date']);
   $publisher = mysqli_real_escape_string($conn, $_POST['publisher']);
   $quantity = $_POST['quantity'];
   $des = mysqli_real_escape_string($conn, $_POST['des']);
   $image = $_FILES['image']['name'];
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;

   $select_product_name = mysqli_query($conn, "SELECT name FROM `products` WHERE name = '$name'") or die('query failed');

   if(mysqli_num_rows($select_product_name) > 0){
      $message[] = 'Sản phẩm đã tồn tại!';
   }else{
      $add_product_query = mysqli_query($conn, "INSERT INTO `products`(name, price, image, author, publish_date, publisher, quantity, des) VALUES('$name', '$price', '$image', '$author', '$publish_date', '$publisher', '$quantity', '$des')") or die('query failed');

      if($add_product_query){
         if($image_size > 2000000){
            $message[] = 'Kích thước ảnh quá lớn';
         }else{
            move_uploaded_file($image_tmp_name, $image_folder);
            $message[] = 'Thêm sản phẩm thành công!';
         }
      }else{
         $message[] = 'Không thể thêm!';
      }
   }
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_image_query = mysqli_query($conn, "SELECT image FROM `products` WHERE id = '$delete_id'") or die('query failed');
   $fetch_delete_image = mysqli_fetch_assoc($delete_image_query);
   unlink('uploaded_img/'.$fetch_delete_image['image']);
   mysqli_query($conn, "DELETE FROM `products` WHERE id = '$delete_id'") or die('query failed');
   header('location:admin_products.php');
}

if(isset($_POST['update_product'])){

   $update_p_id = $_POST['update_p_id'];
   $update_name = mysqli_real_escape_string($conn, $_POST['update_name']);
   $update_price = $_POST['update_price'];
   $update_author = mysqli_real_escape_string($conn, $_POST['update_author']);
   $update_publish_date = mysqli_real_escape_string($conn, $_POST['update_publish_date']);
   $update_publisher = mysqli_real_escape_string($conn, $_POST['update_publisher']);
   $update_quantity = $_POST['update_quantity'];
   $update_des = mysqli_real_escape_string($conn, $_POST['update_des']);

   // Kiểm tra tên sản phẩm đã tồn tại chưa (trừ sản phẩm đang cập nhật)
   $check_name_query = mysqli_query($conn, "SELECT id FROM `products` WHERE name = '$update_name' AND id != '$update_p_id'") or die('query failed');
   if(mysqli_num_rows($check_name_query) > 0){
      $message[] = 'Sản phẩm đã tồn tại';
   } else {
      mysqli_query($conn, "UPDATE `products` SET name = '$update_name', price = '$update_price', author = '$update_author', publish_date = '$update_publish_date', publisher = '$update_publisher', quantity = '$update_quantity', des = '$update_des' WHERE id = '$update_p_id'") or die('query failed');

      $update_image = $_FILES['update_image']['name'];
      $update_image_tmp_name = $_FILES['update_image']['tmp_name'];
      $update_image_size = $_FILES['update_image']['size'];
      $update_folder = 'uploaded_img/'.$update_image;
      $update_old_image = $_POST['update_old_image'];

      if(!empty($update_image)){
         if($update_image_size > 2000000){
            $message[] = 'Kich thước ảnh quá lớn';
         }else{
            mysqli_query($conn, "UPDATE `products` SET image = '$update_image' WHERE id = '$update_p_id'") or die('query failed');
            move_uploaded_file($update_image_tmp_name, $update_folder);
            unlink('uploaded_img/'.$update_old_image);
         }
      }

      header('location:admin_products.php');
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Sản phẩm</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
   
<?php include 'admin_header.php'; ?>

<!-- product CRUD section starts  -->

<section class="add-products">

   <h1 class="title">Sản phẩm</h1>

   <form action="" method="post" enctype="multipart/form-data">
      <h3>Thêm sản phẩm</h3>
      <input type="text" name="name" class="box" placeholder="Tên sản phẩm" required>
      <input type="number" min="0" name="price" class="box" placeholder="Giá" required>
      <input type="text" name="author" class="box" placeholder="Tác giả">
      <input type="date" name="publish_date" class="box" placeholder="Ngày xuất bản">
      <input type="text" name="publisher" class="box" placeholder="Nhà xuất bản">
      <input type="number" min="0" name="quantity" class="box" placeholder="Số lượng">
      <input type="text" name="des" class="box" placeholder="Mô tả">
      <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box" required>
      <input type="submit" value="Thêm" name="add_product" class="btn">
   </form>

</section>

<!-- product CRUD section ends -->

<!-- show products  -->

<section class="show-products">

   <div class="box-container">

      <?php
         $select_products = mysqli_query($conn, "SELECT * FROM `products`") or die('query failed');
         if(mysqli_num_rows($select_products) > 0){
            while($fetch_products = mysqli_fetch_assoc($select_products)){
      ?>
      <div class="box">
         <img src="uploaded_img/<?php echo $fetch_products['image']; ?>" alt="">
         <div class="name"><?php echo $fetch_products['name']; ?></div>
         <div class="price">$<?php echo $fetch_products['price']; ?>/-</div>
         <a href="admin_products.php?update=<?php echo $fetch_products['id']; ?>" class="option-btn">Cập nhật</a>
         <a href="admin_products.php?delete=<?php echo $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('delete this product?');">Xóa</a>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">no products added yet!</p>';
      }
      ?>
   </div>

</section>

<section class="edit-product-form">

   <?php
      if(isset($_GET['update'])){
         $update_id = $_GET['update'];
         $update_query = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '$update_id'") or die('query failed');
         if(mysqli_num_rows($update_query) > 0){
            while($fetch_update = mysqli_fetch_assoc($update_query)){
   ?>
   <form action="" method="post" enctype="multipart/form-data">
      <input type="hidden" name="update_p_id" value="<?php echo $fetch_update['id']; ?>">
      <input type="hidden" name="update_old_image" value="<?php echo $fetch_update['image']; ?>">
      <img src="uploaded_img/<?php echo $fetch_update['image']; ?>" alt="">
      <input type="text" name="update_name" value="<?php echo $fetch_update['name']; ?>" class="box" required placeholder="Tên sản phẩm">
      <input type="number" name="update_price" value="<?php echo $fetch_update['price']; ?>" min="0" class="box" required placeholder="Giá">
      <input type="text" name="update_author" value="<?php echo $fetch_update['author']; ?>" class="box" placeholder="Tác giả">
      <input type="date" name="update_publish_date" value="<?php echo $fetch_update['publish_date']; ?>" class="box" placeholder="Ngày xuất bản">
      <input type="text" name="update_publisher" value="<?php echo $fetch_update['publisher']; ?>" class="box" placeholder="Nhà xuất bản">
      <input type="number" name="update_quantity" value="<?php echo $fetch_update['quantity']; ?>" min="0" class="box" placeholder="Số lượng">
      <input type="text" name="update_des" value="<?php echo $fetch_update['des']; ?>" class="box" placeholder="Mô tả">
      <input type="file" class="box" name="update_image" accept="image/jpg, image/jpeg, image/png">
      <input type="submit" value="update" name="update_product" class="btn">
      <input type="reset" value="cancel" id="close-update" class="option-btn">
   </form>
   <?php
         }
      }
      }else{
         echo '<script>document.querySelector(".edit-product-form").style.display = "none";</script>';
      }
   ?>

</section>







<!-- custom admin js file link  -->
<script src="js/admin_script.js"></script>

</body>
</html>
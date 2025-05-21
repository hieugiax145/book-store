<?php

include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:login.php');
};

if (isset($_POST['add_product'])) {

   $ten = mysqli_real_escape_string($conn, $_POST['ten']);
   $tacGia = mysqli_real_escape_string($conn, $_POST['tacGia']);
   $namXuatBan = $_POST['namXuatBan'];
   $nhaXuatBan = mysqli_real_escape_string($conn, $_POST['nhaXuatBan']);
   $soLuong = $_POST['soLuong'];
   $donGia = $_POST['donGia'];
   $moTa = mysqli_real_escape_string($conn, $_POST['moTa']);
   $image = $_FILES['image']['name'];
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/' . $image;

   $select_product_name = mysqli_query($conn, "SELECT ten FROM `sach` WHERE ten = '$ten'") or die('query failed');

   if (mysqli_num_rows($select_product_name) > 0) {
      $message[] = 'Sách đã tồn tại';
   } else {
      $add_product_query = mysqli_query($conn, "INSERT INTO `sach`(ten, tacGia, namXuatBan, nhaXuatBan, soLuong, donGia, moTa, image) VALUES('$ten', '$tacGia', '$namXuatBan', '$nhaXuatBan', '$soLuong', '$donGia', '$moTa', '$image')") or die('query failed');

      if ($add_product_query) {
         if ($image_size > 2000000) {
            $message[] = 'Kích thước ảnh quá lớn';
         } else {
            move_uploaded_file($image_tmp_name, $image_folder);
            $message[] = 'Thêm sách thành công!';
         }
      } else {
         $message[] = 'Không thể thêm sách!';
      }
   }
   header('location:admin_products.php');
}

if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   $delete_image_query = mysqli_query($conn, "SELECT image FROM `sach` WHERE sachId = '$delete_id'") or die('query failed');
   $fetch_delete_image = mysqli_fetch_assoc($delete_image_query);
   unlink('uploaded_img/' . $fetch_delete_image['image']);
   mysqli_query($conn, "DELETE FROM `sach` WHERE sachId = '$delete_id'") or die('query failed');
   header('location:admin_products.php');
}

if (isset($_POST['update_product'])) {
   $update_p_id = $_POST['update_p_id'];
   $update_ten = $_POST['update_ten'];
   $update_tacGia = $_POST['update_tacGia'];
   $update_namXuatBan = $_POST['update_namXuatBan'];
   $update_nhaXuatBan = $_POST['update_nhaXuatBan'];
   $update_soLuong = $_POST['update_soLuong'];
   $update_donGia = $_POST['update_donGia'];
   $update_moTa = $_POST['update_moTa'];

   mysqli_query($conn, "UPDATE `sach` SET ten = '$update_ten', tacGia = '$update_tacGia', namXuatBan = '$update_namXuatBan', nhaXuatBan = '$update_nhaXuatBan', soLuong = '$update_soLuong', donGia = '$update_donGia', moTa = '$update_moTa' WHERE sachId = '$update_p_id'") or die('query failed');

   $update_image = $_FILES['update_image']['name'];
   $update_image_tmp_name = $_FILES['update_image']['tmp_name'];
   $update_image_size = $_FILES['update_image']['size'];
   $update_folder = 'uploaded_img/' . $update_image;
   $update_old_image = $_POST['update_old_image'];

   if (!empty($update_image)) {
      if ($update_image_size > 2000000) {
         $message[] = 'Kích thước ảnh quá lớn';
      } else {
         mysqli_query($conn, "UPDATE `sach` SET image = '$update_image' WHERE sachId = '$update_p_id'") or die('query failed');
         move_uploaded_file($update_image_tmp_name, $update_folder);
         unlink('uploaded_img/' . $update_old_image);
      }
   }

   header('location:admin_products.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Quản lý sách</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>

<body>

   <?php include 'admin_header.php'; ?>

   <!-- product CRUD section starts  -->

   <section class="add-products">

      <!-- <h1 class="title">shop products</h1>
   <input type="submit" value="add product" name="add_product" class="btn"> -->
      <div class="title-button" style="display: flex;flex-direction: row; align-items: center;">
         <span style="flex: 1;"></span>
         <h1 class="title" style=" flex: 1;">Quản lý sách</h1>
         <div style="flex: 1; text-align:right;"><a href="admin_products.php?add" class="btn" id="add-product-btn">Thêm sách</a></div>

      </div>

      <!-- <form action="" method="post" enctype="multipart/form-data">
         <h3>add product</h3>
         <input type="text" name="name" class="box" placeholder="enter product name" required>
         <input type="number" min="0" name="price" class="box" placeholder="enter product price" required>
         <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box" required>
         <input type="submit" value="add product" name="add_product" class="btn">
      </form> -->

   </section>

   <!-- product CRUD section ends -->

   <!-- search box section starts -->
   <section class="search-box">
      <form action="" method="GET">
         <input type="text" name="search" placeholder="Tìm kiếm sách..." class="box" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
         <input type="submit" value="Tìm kiếm" class="btn">
         <?php if (isset($_GET['search'])): ?>
            <a href="admin_products.php" class="option-btn">Xóa</a>
         <?php endif; ?>
      </form>
   </section>
   <!-- search box section ends -->

   <!-- show products  -->
   <section class="show-products">
      <div class="box-container">
         <?php
         $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
         $select_products = mysqli_query($conn, "SELECT * FROM `sach` WHERE ten LIKE '%$search%' OR tacGia LIKE '%$search%' OR nhaXuatBan LIKE '%$search%'") or die('query failed');
         if (mysqli_num_rows($select_products) > 0) {
            while ($fetch_products = mysqli_fetch_assoc($select_products)) {
         ?>
               <div class="box">
                  <img src="uploaded_img/<?php echo $fetch_products['image']; ?>" alt="">
                  <div class="name"><?php echo $fetch_products['ten']; ?></div>
                  <div class="price"><?php echo number_format($fetch_products['donGia'], 0, ',', '.'); ?> VNĐ</div>
                  <a href="admin_products.php?update=<?php echo $fetch_products['sachId']; ?>" class="option-btn">Cập nhật</a>
                  <a href="admin_products.php?delete=<?php echo $fetch_products['sachId']; ?>" class="delete-btn" onclick="return confirm('Xóa sách này?');">Xóa</a>
               </div>
         <?php
            }
         } else {
            echo '<p class="empty">Không tìm thấy sách!</p>';
         }
         ?>
      </div>
   </section>

   <section class="add-product-form">

      <?php
      if (isset($_GET['add'])) {
      ?>
         <form action="" method="post" enctype="multipart/form-data">
            <h3>Thêm sách mới</h3>
            <input type="text" name="ten" class="box" placeholder="Nhập tên sách" required>
            <input type="text" name="tacGia" class="box" placeholder="Nhập tác giả" required>
            <input type="number" name="namXuatBan" class="box" placeholder="Năm xuất bản" required>
            <input type="text" name="nhaXuatBan" class="box" placeholder="Nhà xuất bản" required>
            <input type="number" name="soLuong" class="box" placeholder="Số lượng" required>
            <input type="number" step="0.01" name="donGia" class="box" placeholder="Đơn giá" required>
            <textarea name="moTa" class="box" placeholder="Mô tả sách"></textarea>
            <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box" required>
            <input type="submit" value="Thêm" name="add_product" class="btn">
            <input type="reset" value="Hủy" id="close-update" class="option-btn">
         </form>
      <?php
      } else {
         echo '<script>document.querySelector(".add-product-form").style.display = "none";</script>';
      }
      ?>

   </section>

   <section class="edit-product-form">

      <?php
      if (isset($_GET['update'])) {
         $update_id = $_GET['update'];
         $update_query = mysqli_query($conn, "SELECT * FROM `sach` WHERE sachId = '$update_id'") or die('query failed');
         if (mysqli_num_rows($update_query) > 0) {
            while ($fetch_update = mysqli_fetch_assoc($update_query)) {
      ?>
               <form action="" method="post" enctype="multipart/form-data">
                  <h3>Cập nhật sách</h3>
                  <div style="display: flex; flex-direction: row;align-items:top;">
                     <div>
                        <input type="hidden" name="update_p_id" value="<?php echo $fetch_update['sachId']; ?>">
                        <input type="hidden" name="update_old_image" value="<?php echo $fetch_update['image']; ?>">

                        <input type="text" name="update_ten" value="<?php echo $fetch_update['ten']; ?>" class="box" required placeholder="Nhập tên sách">
                        <input type="text" name="update_tacGia" value="<?php echo $fetch_update['tacGia']; ?>" class="box" required placeholder="Nhập tác giả">
                        <input type="number" name="update_namXuatBan" value="<?php echo $fetch_update['namXuatBan']; ?>" class="box" required placeholder="Năm xuất bản">
                        <input type="text" name="update_nhaXuatBan" value="<?php echo $fetch_update['nhaXuatBan']; ?>" class="box" required placeholder="Nhà xuất bản">
                        <input type="number" name="update_soLuong" value="<?php echo $fetch_update['soLuong']; ?>" class="box" required placeholder="Số lượng">
                        <input type="number" step="0.01" name="update_donGia" value="<?php echo $fetch_update['donGia']; ?>" class="box" required placeholder="Đơn giá">
                        <textarea name="update_moTa" class="box" placeholder="Mô tả sách"><?php echo $fetch_update['moTa']; ?></textarea>
                     </div>
                     <div style="margin-left: 2rem;">
                        <img src="uploaded_img/<?php echo $fetch_update['image']; ?>" alt="">
                        <input type="file" class="box" name="update_image" accept="image/jpg, image/jpeg, image/png">
                     </div>
                  </div>

                  <input type="submit" value="Cập nhật" name="update_product" class="btn">
                  <input type="reset" value="Hủy" id="close-update" class="option-btn">
               </form>
      <?php
            }
         }
      } else {
         echo '<script>document.querySelector(".edit-product-form").style.display = "none";</script>';
      }
      ?>

   </section>


   <!-- custom admin js file link  -->
   <script src="js/admin_script.js"></script>
   <style>
      .search-box {
         position: relative;
         display: flex;
         /* gap: 1rem; */
         /* margin-bottom: 1.5rem; */
         /* align-items: center; */
         justify-content: center;
      }

      .search-box form .box {
         /* flex: 1; */
         width: 500px;
         ;
         background-color: var(--light-bg);
         border-radius: .5rem;
         /* margin: 1rem 0; */
         padding: 1.2rem 1.4rem;
         color: var(--black);
         font-size: 1.8rem;
         border: var(--border);
      }
   </style>

</body>

</html>
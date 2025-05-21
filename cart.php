<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
   header('location:login.php');
}



if (isset($_POST['update_cart'])) {
   $cart_id = $_POST['cart_id'];
   $cart_quantity = $_POST['cart_quantity'];
   
   // Get the book's available quantity
   $check_stock = mysqli_query($conn, "SELECT s.soLuong as available_qty 
      FROM cart c 
      JOIN sach s ON c.sachId = s.sachId 
      WHERE c.id = '$cart_id'") or die('query failed');
   $stock = mysqli_fetch_assoc($check_stock);
   
   if($cart_quantity > $stock['available_qty']) {
      $message[] = 'Số lượng vượt quá số lượng sách có sẵn!';
   } else {
      mysqli_query($conn, "UPDATE `cart` SET soLuong = '$cart_quantity' WHERE id = '$cart_id'") or die('query failed');
      header('location:cart.php');
      exit();
   }
}

if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   mysqli_query($conn, "DELETE FROM `cart` WHERE id = '$delete_id'") or die('query failed');
   header('location:cart.php');
}

if (isset($_GET['delete_all'])) {
   mysqli_query($conn, "DELETE FROM `cart` WHERE khachHangId = '$user_id'") or die('query failed');
   header('location:cart.php');
}

if(isset($_POST['remove_from_cart'])){
   $cart_id = $_POST['cart_id'];
   mysqli_query($conn, "DELETE FROM `cart` WHERE id = '$cart_id'") or die('query failed');
   header('location:cart.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>cart</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>

<body>

   <?php include 'header.php'; ?>

   <div class="heading">
      <h3>Giỏ hàng</h3>
      <p> <a href="home.php">home</a> / cart </p>
   </div>

   <section class="shopping-cart">

      <h1 class="title">Sản phẩn đã thêm</h1>

      
      <div class="book-items">
         <?php
         $grand_total = 0;
         $select_cart = mysqli_query($conn, "SELECT c.*, s.ten as name, s.donGia as price, s.image as image, s.soLuong as available_qty 
            FROM `cart` c 
            JOIN `sach` s ON c.sachId = s.sachId 
            WHERE c.khachHangId = '$user_id'") or die('query failed');
         if (mysqli_num_rows($select_cart) > 0) {
            while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
         ?>
               <div class="books-sell-item">
                  <img src="uploaded_img/<?php echo $fetch_cart['image']; ?>" alt="">
                  <div class="book-details">
                     <h3><?php echo $fetch_cart['name']; ?></h3>
                     <div class="price"><?php echo number_format($fetch_cart['price']); ?>đ</div>
                  </div>
                  <div class="quantity-box">
                     <form action="" method="post" class="quantity-form">
                        <input type="hidden" name="cart_id" value="<?php echo $fetch_cart['id']; ?>">
                        <button type="button" class="qty-btn minus" onclick="updateCartQuantity(<?php echo $fetch_cart['id']; ?>, -1)">-</button>
                        <input type="number" name="cart_quantity" class="qty-input" 
                           value="<?php echo $fetch_cart['soLuong']; ?>" 
                           min="1" 
                           max="<?php echo $fetch_cart['available_qty']; ?>"
                           data-max-qty="<?php echo $fetch_cart['available_qty']; ?>"
                           onchange="this.form.submit()">
                        <button type="button" class="qty-btn plus" onclick="updateCartQuantity(<?php echo $fetch_cart['id']; ?>, 1)">+</button>
                        <input type="hidden" name="update_cart" value="update">
                     </form>
                  </div>
                  <div class="total-price"><?php echo number_format($fetch_cart['soLuong'] * $fetch_cart['price']); ?>đ</div>
                  <form action="" method="post" class="remove-form">
                     <input type="hidden" name="cart_id" value="<?php echo $fetch_cart['id']; ?>">
                     <button type="submit" name="remove_from_cart" class="remove-btn" onclick="return confirm('delete this from cart?');"><i class="fas fa-trash"></i></button>
                  </form>
               </div>
         <?php
               $grand_total += ($fetch_cart['soLuong'] * $fetch_cart['price']);
            }
         } else {
            echo '<p class="empty">Giỏ hàng trống</p>';
         }
         ?>
      </div>

      <!-- <div style="margin-top: 2rem; text-align:center;">
      <a href="cart.php?delete_all" class="delete-btn <?php echo ($grand_total > 1) ? '' : 'disabled'; ?>" onclick="return confirm('delete all from cart?');">delete all</a>
   </div> -->

      <div class="cart-total">
         <p>Tổng tiền : <span><?php echo number_format($grand_total); ?>đ</span></p>
         <div class="flex">
            <a href="search_page.php" class="option-btn">Tiếp tục mua hàng</a>
            <a href="checkout.php" class="btn <?php echo ($grand_total > 1) ? '' : 'disabled'; ?>">Xác nhận</a>
         </div>
      </div>

   </section>

   <script>
   function updateCartQuantity(cartId, change) {
      const form = document.querySelector(`form input[name="cart_id"][value="${cartId}"]`).closest('form');
      const input = form.querySelector('input[name="cart_quantity"]');
      let newValue = parseInt(input.value) + change;
      if (newValue < 1) newValue = 1;
      
      // Get the max available quantity from the data attribute
      const maxQty = parseInt(input.getAttribute('data-max-qty'));
      if (newValue > maxQty) {
         alert('Số lượng vượt quá số lượng sách có sẵn!');
         return;
      }
      
      // Create FormData object
      const formData = new FormData(form);
      formData.set('cart_quantity', newValue);
      
      // Send AJAX request
      fetch('cart.php', {
         method: 'POST',
         body: formData
      })
      .then(response => response.text())
      .then(data => {
         // Update the input value
         input.value = newValue;
         
         // Update the total price for this item
         const price = parseFloat(form.closest('.books-sell-item').querySelector('.price').textContent.replace(/[^0-9.-]+/g, ''));
         const totalPrice = newValue * price;
         form.closest('.books-sell-item').querySelector('.total-price').textContent = totalPrice.toLocaleString() + 'đ';
         
         // Update grand total
         updateGrandTotal();
      })
      .catch(error => {
         console.error('Error:', error);
         alert('Có lỗi xảy ra khi cập nhật số lượng!');
      });
   }

   function updateGrandTotal() {
      let grandTotal = 0;
      document.querySelectorAll('.books-sell-item').forEach(item => {
         const totalPrice = parseFloat(item.querySelector('.total-price').textContent.replace(/[^0-9.-]+/g, ''));
         grandTotal += totalPrice;
      });
      document.querySelector('.cart-total span').textContent = grandTotal.toLocaleString() + 'đ';
   }
   </script>

   <?php include 'footer.php'; ?>

   <!-- custom js file link  -->
   <script src="js/script.js"></script>
   <style>
      .book-list {
         height: calc(100vh - 250px);
         overflow-y: auto;
      }

      .book-list h2 {
         font-size: 2rem;
         color: var(--black);
         margin-bottom: 1.5rem;
      }

      .books-sell-item {
         display: flex;
         align-items: center;
         gap: 1rem;
         padding: 1rem;
         background: #fff;
         border-radius: 8px;
         margin-bottom: 1rem;
         box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }

      .books-sell-item img {
         width: 80px;
         height: 80px;
         object-fit: cover;
         border-radius: 4px;
      }

      .book-details {
         flex: 1;
      }

      .book-details h3 {
         font-size: 1.4rem;
         margin-bottom: 0.5rem;
      }

      .price {
         color: #2980b9;
         font-weight: bold;
         font-size: 1.2rem;
      }

      .quantity-box {
         display: flex;
         align-items: center;
      }

      .quantity-form {
         display: flex;
         align-items: center;
         gap: 0.5rem;
         flex-direction: row;
      }

      .qty-btn {
         width: 35px;
         height: 35px;
         border: 1px solid #ddd;
         background: #f8f8f8;
         border-radius: 4px;
         cursor: pointer;
         display: flex;
         align-items: center;
         justify-content: center;
         padding: 0;
         font-size: 1.2rem;
         flex-shrink: 0;
      }

      .qty-btn:hover {
         background: #e8e8e8;
      }

      .qty-input {
         width: 60px;
         height: 35px;
         text-align: center;
         border: 1px solid #ddd;
         border-radius: 4px;
         padding: 0;
         margin: 0;
         font-size: 1.2rem;
         -moz-appearance: textfield;
         flex-shrink: 0;
      }

      .qty-input::-webkit-outer-spin-button,
      .qty-input::-webkit-inner-spin-button {
         -webkit-appearance: none;
         margin: 0;
      }

      .total-price {
         font-weight: bold;
         color: #27ae60;
         min-width: 100px;
         text-align: right;
         font-size: 1.2rem;
      }

      .remove-btn {
         background: #e74c3c;
         color: white;
         border: none;
         width: 35px;
         height: 35px;
         border-radius: 4px;
         cursor: pointer;
         display: flex;
         align-items: center;
         justify-content: center;
         font-size: 1.2rem;
      }

      .remove-btn:hover {
         background: #c0392b;
      }
   </style>

</body>

</html>
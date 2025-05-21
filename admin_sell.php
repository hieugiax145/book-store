<?php

include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:login.php');
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
   $_SESSION['cart'] = array();
}

// Handle adding books to cart
if (isset($_POST['add_to_cart'])) {
   $book_id = $_POST['book_id'];
   $select_book = mysqli_query($conn, "SELECT * FROM `sach` WHERE sachId = '$book_id'") or die('query failed');
   if (mysqli_num_rows($select_book) > 0) {
      $book = mysqli_fetch_assoc($select_book);
      $current_quantity = isset($_SESSION['cart'][$book_id]) ? $_SESSION['cart'][$book_id]['quantity'] : 0;

      if ($current_quantity + 1 <= $book['soLuong']) {
         if (!isset($_SESSION['cart'][$book_id])) {
            $_SESSION['cart'][$book_id] = array(
               'id' => $book['sachId'],
               'name' => $book['ten'],
               'price' => $book['donGia'],
               'image' => $book['image'],
               'quantity' => 1
            );
         } else {
            $_SESSION['cart'][$book_id]['quantity']++;
         }
      } else {
         $message[] = 'Số lượng sách trong kho không đủ!';
      }
   }
}

// Handle removing books from cart
if (isset($_POST['remove_from_cart'])) {
   $book_id = $_POST['book_id'];
   if (isset($_SESSION['cart'][$book_id])) {
      unset($_SESSION['cart'][$book_id]);
   }
}

// Handle updating quantity
if (isset($_POST['update_quantity'])) {
   $book_id = $_POST['book_id'];
   $quantity = $_POST['quantity'];

   // Get current inventory from sach table
   $select_book = mysqli_query($conn, "SELECT soLuong FROM `sach` WHERE sachId = '$book_id'") or die('query failed');
   $book = mysqli_fetch_assoc($select_book);

   if (isset($_SESSION['cart'][$book_id])) {
      if ($quantity <= 0) {
         $message[] = 'Số lượng không thể nhỏ hơn 1!';
      }
      // Check if requested quantity is less than or equal to soLuong
      else if ($quantity <= $book['soLuong']) {
         $_SESSION['cart'][$book_id]['quantity'] = $quantity;
      } else {
         $message[] = 'Số lượng sách trong kho không đủ!';
      }
   }
}

// Handle placing order
if (isset($_POST['place_order'])) {
   // Check if cart is empty
   if (empty($_SESSION['cart'])) {
      $message[] = 'Giỏ hàng trống! Vui lòng thêm sách vào giỏ hàng.';
   } else {
      $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
      $customer_phone = mysqli_real_escape_string($conn, $_POST['customer_phone']);
      $customer_address = mysqli_real_escape_string($conn, $_POST['customer_address']);
      $payment = mysqli_real_escape_string($conn, $_POST['payment']);
      $ghiChu = mysqli_real_escape_string($conn, $_POST['ghiChu']);

      // Set default customer name if empty
      if (empty($customer_name)) {
         $customer_name = "Khách lẻ";
      }

      // Calculate total amount
      $total_amount = 0;
      foreach ($_SESSION['cart'] as $book) {
         $total_amount += $book['price'] * $book['quantity'];
      }

      // Insert into don_hang table with fixed values for trangThai and phanLoai
      $insert_order = mysqli_query($conn, "INSERT INTO don_hang (
         khachHangId, 
         nhanVienId, 
         tongTien, 
         diaChiNhanHang, 
         ngayBan, 
         trangThai, 
         phanLoai, 
         hinhThucThanhToan,
         ghiChu,
         tenKhachHang,
         sdt
      ) VALUES (
         NULL, 
         '$admin_id', 
         '$total_amount', 
         '$customer_address', 
         CURDATE(), 
         'Completed', 
         'In-store', 
         '$payment',
         '$ghiChu',
         '$customer_name',
         '$customer_phone'
      )") or die('query failed');

      if ($insert_order) {
         $don_hang_id = mysqli_insert_id($conn);

         // Insert into chi_tiet_don_hang table
         foreach ($_SESSION['cart'] as $book) {
            $book_id = $book['id'];
            $price = $book['price'];
            $quantity = $book['quantity'];

            mysqli_query($conn, "INSERT INTO chi_tiet_don_hang (donHangId, sachId, donGia, soLuong) 
               VALUES ('$don_hang_id', '$book_id', '$price', '$quantity')") or die('query failed');
         }

         // Clear the cart after successful order
         $_SESSION['cart'] = array();

         $message[] = 'Đặt hàng thành công!';
      } else {
         $message[] = 'Đặt hàng thất bại!';
      }
   }
}

// Handle QR payment success
if (isset($_POST['qr_success'])) {
   $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
   $customer_phone = mysqli_real_escape_string($conn, $_POST['customer_phone']);
   $customer_address = mysqli_real_escape_string($conn, $_POST['customer_address']);
   $ghiChu = mysqli_real_escape_string($conn, $_POST['ghiChu']);

   // Set default customer name if empty
   if (empty($customer_name)) {
      $customer_name = "Khách lẻ";
   }

   // Calculate total amount
   $total_amount = 0;
   foreach ($_SESSION['cart'] as $book) {
      $total_amount += $book['price'] * $book['quantity'];
   }

   // Insert into don_hang table
   $insert_order = mysqli_query($conn, "INSERT INTO don_hang (
      khachHangId, 
      nhanVienId, 
      tongTien, 
      diaChiNhanHang, 
      ngayBan, 
      trangThai, 
      phanLoai, 
      hinhThucThanhToan,
      ghiChu,
      tenKhachHang,
      sdt
   ) VALUES (
      NULL, 
      '$admin_id', 
      '$total_amount', 
      '$customer_address', 
      CURDATE(), 
      'Completed', 
      'In-store', 
      'QR',
      '$ghiChu',
      '$customer_name',
      '$customer_phone'
   )") or die('query failed');

   if ($insert_order) {
      $don_hang_id = mysqli_insert_id($conn);

      // Insert into chi_tiet_don_hang table
      foreach ($_SESSION['cart'] as $book) {
         $book_id = $book['id'];
         $price = $book['price'];
         $quantity = $book['quantity'];

         mysqli_query($conn, "INSERT INTO chi_tiet_don_hang (donHangId, sachId, donGia, soLuong) 
            VALUES ('$don_hang_id', '$book_id', '$price', '$quantity')") or die('query failed');
      }

      // Clear the cart after successful order
      $_SESSION['cart'] = array();

      echo "success";
   } else {
      echo "error";
   }
   exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Bán hàng</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>

<body>

   <?php include 'admin_header.php'; ?>

   <div class="sell-container">
      <div class="book-list-section">
         <div class="search-box">
            <input type="text" id="bookSearch" placeholder="Tìm kiếm sách..." autocomplete="off">
            <button type="button" class="btn" style="height:100%"><i class="fas fa-search"></i></button>
            <div class="search-results" id="searchResults">
               <?php
               if (isset($_GET['search'])) {
                  $search = mysqli_real_escape_string($conn, $_GET['search']);
                  $select_products = mysqli_query($conn, "SELECT * FROM `sach` WHERE ten LIKE '%$search%'") or die('query failed');
                  if (mysqli_num_rows($select_products) > 0) {
                     while ($fetch_products = mysqli_fetch_assoc($select_products)) {
               ?>
                        <div class="search-result-item" onclick="addBookToCart(<?php echo $fetch_products['sachId']; ?>)">
                           <img src="uploaded_img/<?php echo $fetch_products['image']; ?>" alt="<?php echo $fetch_products['ten']; ?>">
                           <div class="search-result-info">
                              <h4><?php echo $fetch_products['ten']; ?></h4>
                              <div class="price"><?php echo number_format($fetch_products['donGia']); ?>đ</div>
                           </div>
                           <button class="add-btn">
                              <i class="fas fa-plus"></i> Thêm
                           </button>
                        </div>
               <?php
                     }
                  } else {
                     echo '<div class="search-result-item">Không tìm thấy sách</div>';
                  }
               }
               ?>
            </div>
         </div>
         <div class="book-list">
            <h2>Sách đã chọn</h2>
            <div class="book-items">
               <?php
               if (!empty($_SESSION['cart'])) {
                  foreach ($_SESSION['cart'] as $book) {
               ?>
                     <div class="books-sell-item">
                        <img src="uploaded_img/<?php echo $book['image']; ?>" alt="">
                        <div class="book-details">
                           <h3><?php echo $book['name']; ?></h3>
                           <div class="price"><?php echo number_format($book['price']); ?>đ</div>
                        </div>
                        <div class="quantity-box">
                           <div class="quantity-form">
                              <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                              <button type="button" class="qty-btn minus" onclick="updateQuantity(<?php echo $book['id']; ?>, -1)">-</button>
                              <input type="number" name="quantity" class="qty-input" value="<?php echo $book['quantity']; ?>" min="1" onchange="updateQuantity(<?php echo $book['id']; ?>, 0)">
                              <button type="button" class="qty-btn plus" onclick="updateQuantity(<?php echo $book['id']; ?>, 1)">+</button>
                           </div>
                        </div>
                        <div class="total-price"><?php echo number_format($book['price'] * $book['quantity']); ?>đ</div>
                        <form action="" method="post" class="remove-form">
                           <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                           <button type="submit" name="remove_from_cart" class="remove-btn"><i class="fas fa-trash"></i></button>
                        </form>
                     </div>
               <?php
                  }
               } else {
                  echo '<p class="empty">Chưa có sách nào được chọn!</p>';
               }
               ?>
            </div>
            <?php if (!empty($_SESSION['cart'])) { ?>
            <div class="cart-total">
               <div class="total-label">Tổng tiền hàng:</div>
               <div class="total-amount">
                  <?php
                  $total = 0;
                  foreach ($_SESSION['cart'] as $book) {
                     $total += $book['price'] * $book['quantity'];
                  }
                  echo number_format($total) . 'đ';
                  ?>
               </div>
            </div>
            <?php } ?>
         </div>
      </div>

      <div class="customer-details-section">
         <h2>Thông tin đơn hàng</h2>
         <form action="" method="post" class="customer-form">
            <div class="input-group">
               <label for="customer_name">Tên khách hàng</label>
               <input type="text" name="customer_name" placeholder="Nhập tên khách hàng">
            </div>
            <div class="input-group">
               <label for="customer_phone">Số điện thoại</label>
               <input type="tel" name="customer_phone" placeholder="Nhập số điện thoại">
            </div>
            <!-- <div class="input-group">
               <label for="customer_address">Địa chỉ giao hàng</label>
               <textarea name="customer_address" placeholder="Nhập địa chỉ giao hàng"></textarea>
            </div> -->
            <div class="input-group">
               <label for="ghiChu">Ghi chú</label>
               <textarea name="ghiChu" placeholder="Nhập ghi chú thêm về đơn hàng"></textarea>
            </div>

            <div class="payment-options">
               <h3>Phương thức thanh toán</h3>
               <div class="option-group">
                  <select name="payment" id="paymentMethod" required>
                     <option value="Cash">Tiền mặt</option>
                     <option value="QR">Thanh toán qua mã QR</option>
                  </select>
               </div>
            </div>

            <div id="qrCodeModal" class="modal">
               <div class="modal-content">
                  <span class="close">&times;</span>
                  <h2>Quét mã QR để thanh toán</h2>
                  <div class="qr-container">
                     <img src="./uploaded_img/qrcode-default.png" alt="QR Code" id="qrCode">

                  </div>
                  <div class="countdown">
                     <p>Mã QR sẽ hết hạn sau: <span id="timer">30</span> giây</p>
                  </div>
                  <button id="successBtn" class="btn" style="margin-top: 20px;">Thành công</button>
               </div>
            </div>

            

            <button type="submit" name="place_order" class="btn" id="placeOrderBtn">Đặt hàng</button>
         </form>
      </div>
   </div>

   <style>
      .sell-container {
         display: flex;
         gap: 2rem;
         padding: 2rem;
         min-height: calc(100vh - 100px);
      }

      .book-list-section {
         flex: 1;
         background: #fff;
         padding: 1.5rem;
         border-radius: 8px;
         box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      }

      .search-box {
         position: relative;
         display: flex;
         gap: 0.5rem;
         margin-bottom: 1.5rem;
         align-items: top;
      }

      .search-box input {
         flex: 1;
         width: 100%;
         background-color: var(--light-bg);
         border-radius: .5rem;
         margin: 1rem 0;
         padding: 1.2rem 1.4rem;
         color: var(--black);
         font-size: 1.8rem;
         border: var(--border);
      }

      .search-btn {
         padding: 0.8rem 1.5rem;
         background: #2980b9;
         color: #fff;
         border: none;
         border-radius: 4px;
         cursor: pointer;
      }

      .book-list {
         height: calc(100vh - 250px);
         overflow-y: auto;
      }

      .book-list h2 {
         font-size: 2rem;
         color: var(--black);
         margin-bottom: 1.5rem;
      }

      .customer-details-section {
         flex: 1;
         background: #fff;
         padding: 1.5rem;
         border-radius: 8px;
         box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      }

      .customer-details-section h2 {
         font-size: 2rem;
         color: var(--black);
         margin-bottom: 1.5rem;
      }

      .customer-details-section .input-group input {
         /* margin-bottom: 1.5rem; */
         padding: 1.2rem 1.4rem;
         background-color: var(--light-bg);
         border-radius: .5rem;
         border: var(--border);
      }

      .customer-details-section h3 {
         font-size: 2rem;
         color: var(--black);
         margin-bottom: 1.5rem;
      }

      .customer-form {
         display: flex;
         flex-direction: column;
         gap: 1rem;
      }

      .input-group {
         display: flex;
         flex-direction: column;
         gap: 0.5rem;
      }

      .input-group input,
      .input-group textarea {
         padding: 0.8rem;
         border: 1px solid #ddd;
         border-radius: 4px;
      }

      .input-group textarea {
         height: 100px;
         resize: vertical;
      }

      .delivery-options,
      .payment-options {
         margin-top: 1.5rem;
      }

      .option-group {
         display: flex;
         align-items: center;
         gap: 0.5rem;
         margin: 0.5rem 0;
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
         font-size: 1.6rem;
         margin-bottom: 0.5rem;
      }

      .price {
         color: #2980b9;
         font-weight: bold;
         font-size: 1.4rem;
      }

      .quantity-box {
         display: flex;
         align-items: center;
         gap: 0.5rem;
      }

      .quantity-form {
         display: flex;
         align-items: center;
         gap: 0.5rem;
      }

      .qty-btn {
         width: 30px;
         height: 30px;
         border: 1px solid #ddd;
         background: #f8f8f8;
         border-radius: 4px;
         cursor: pointer;
         display: flex;
         align-items: center;
         justify-content: center;
         padding: 0;
         font-size: 1rem;
      }

      .qty-btn:hover {
         background: #e8e8e8;
      }

      .qty-input {
         width: 50px;
         height: 30px;
         text-align: center;
         border: 1px solid #ddd;
         border-radius: 4px;
         padding: 0;
         margin: 0;
         -moz-appearance: textfield;
      }

      .qty-input::-webkit-outer-spin-button,
      .qty-input::-webkit-inner-spin-button {
         -webkit-appearance: none;
         margin: 0;
      }

      .total-price {
         font-weight: bold;
         color: #27ae60;
         min-width: 80px;
         text-align: right;
         font-size: 1.4rem;
      }

      .remove-btn {
         background: #e74c3c;
         color: white;
         border: none;
         width: 30px;
         height: 30px;
         border-radius: 4px;
         cursor: pointer;
         display: flex;
         align-items: center;
         justify-content: center;
      }

      .remove-btn:hover {
         background: #c0392b;
      }

      .search-results {
         position: absolute;
         top: 100%;
         left: 0;
         right: 0;
         background: white;
         border: 1px solid #ddd;
         border-radius: 4px;
         margin-top: 5px;
         max-height: 300px;
         overflow-y: auto;
         display: none;
         z-index: 1000;
         box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      }

      .search-result-item {
         display: flex;
         align-items: center;
         gap: 1rem;
         padding: 10px;
         border-bottom: 1px solid #eee;
         cursor: pointer;
      }

      .search-result-item:hover {
         background: #f5f5f5;
      }

      .search-result-item img {
         width: 50px;
         height: 50px;
         object-fit: cover;
         border-radius: 4px;
      }

      .search-result-info {
         flex: 1;
      }

      .search-result-info h4 {
         margin: 0;
         font-size: 1rem;
         color: #333;
      }

      .search-result-info .price {
         color: #2980b9;
         font-weight: bold;
         margin-top: 3px;
      }

      .search-result-item .add-btn {
         padding: 5px 10px;
         background: #2980b9;
         color: white;
         border: none;
         border-radius: 4px;
         cursor: pointer;
      }

      .search-result-item .add-btn:hover {
         background: #3498db;
      }

      .order-status select,
      .order-type select,
      .payment-options select {
         width: 100%;
         padding: 0.8rem;
         border: 1px solid #ddd;
         border-radius: 4px;
         background-color: var(--light-bg);
         font-size: 1.6rem;
         color: var(--black);
      }

      .input-group textarea[name="ghiChu"] {
         height: 80px;
         resize: vertical;
         font-size: 1.6rem;
         padding: 1rem;
      }

      .option-group {
         margin-bottom: 1rem;
      }

      .option-group select {
         width: 100%;
         padding: 0.8rem;
         border: 1px solid #ddd;
         border-radius: 4px;
         background-color: var(--light-bg);
         font-size: 1.6rem;
         color: var(--black);
      }

      .option-group select:focus {
         border-color: #2980b9;
         outline: none;
      }

      .modal {
         display: none;
         position: fixed;
         z-index: 1000;
         left: 0;
         top: 0;
         width: 100%;
         height: 100%;
         background-color: rgba(0, 0, 0, 0.5);
      }

      .modal-content {
         background-color: #fefefe;
         margin: 15% auto;
         padding: 20px;
         border: 1px solid #888;
         width: 80%;
         max-width: 500px;
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

      .close:hover {
         color: black;
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
         background: rgba(255, 255, 255, 0.8);
         padding: 10px 20px;
         border-radius: 20px;
      }

      #successBtn {
         background-color: #27ae60;
         color: white;
         padding: 10px 20px;
         border: none;
         border-radius: 4px;
         cursor: pointer;
         font-size: 16px;
      }

      #successBtn:hover {
         background-color: #219a52;
      }

      .cart-total {
         margin-top: 2rem;
         padding: 1.5rem;
         background: #f8f9fa;
         border-radius: 8px;
         border: 1px solid #e9ecef;
         display: flex;
         justify-content: space-between;
         align-items: center;
      }

      .cart-total .total-label {
         font-size: 1.8rem;
         font-weight: bold;
         color: var(--black);
      }

      .cart-total .total-amount {
         font-size: 2.4rem;
         font-weight: bold;
         color: #27ae60;
      }
   </style>

   <script>
      function updateQuantity(bookId, change) {
         const input = event.target.parentElement.querySelector('.qty-input');
         let newValue;

         if (change === 0) {
            // Direct input change
            newValue = parseInt(input.value);
         } else {
            // Button click
            newValue = parseInt(input.value) + change;
         }

         // Create and submit form
         const form = document.createElement('form');
         form.method = 'POST';
         form.innerHTML = `
            <input type="hidden" name="book_id" value="${bookId}">
            <input type="hidden" name="quantity" value="${newValue}">
            <input type="hidden" name="update_quantity" value="1">
         `;
         document.body.appendChild(form);
         form.submit();
      }

      function number_format(number) {
         return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
      }

      function removeItem(button) {
         const item = button.closest('.books-sell-item');
         item.remove();
      }

      document.addEventListener('DOMContentLoaded', function() {
         const searchInput = document.getElementById('bookSearch');
         const searchResults = document.getElementById('searchResults');
         let searchTimeout;

         searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            if (query.length < 2) {
               searchResults.style.display = 'none';
               return;
            }

            searchTimeout = setTimeout(() => {
               // Update URL with search parameter
               const url = new URL(window.location.href);
               url.searchParams.set('search', query);
               window.history.pushState({}, '', url);

               // Reload the search results section
               fetch(window.location.href)
                  .then(response => response.text())
                  .then(html => {
                     const parser = new DOMParser();
                     const doc = parser.parseFromString(html, 'text/html');
                     const newResults = doc.getElementById('searchResults');
                     searchResults.innerHTML = newResults.innerHTML;
                     searchResults.style.display = 'block';
                  })
                  .catch(error => {
                     console.error('Error:', error);
                  });
            }, 300);
         });

         // Close search results when clicking outside
         document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
               searchResults.style.display = 'none';
            }
         });
      });

      function addBookToCart(bookId) {
         // Create and submit a form to add the book
         const form = document.createElement('form');
         form.method = 'POST';
         form.innerHTML = `
      <input type="hidden" name="book_id" value="${bookId}">
      <input type="hidden" name="add_to_cart" value="1">
   `;
         document.body.appendChild(form);
         form.submit();
      }

      document.addEventListener('DOMContentLoaded', function() {
         const placeOrderBtn = document.getElementById('placeOrderBtn');
         const paymentMethod = document.getElementById('paymentMethod');
         const qrCodeModal = document.getElementById('qrCodeModal');
         const closeBtn = document.querySelector('.close');
         const successBtn = document.getElementById('successBtn');
         let countdownInterval;
         let orderForm;

         placeOrderBtn.addEventListener('click', function(e) {
            if (paymentMethod.value === 'QR') {
               e.preventDefault();
               // Check if cart is empty
               const cartItems = document.querySelectorAll('.books-sell-item');
               if (cartItems.length === 0) {
                  const form = document.createElement('form');
                  form.method = 'POST';
                  form.innerHTML = '<input type="hidden" name="message" value="Giỏ hàng trống! Vui lòng thêm sách vào giỏ hàng.">';
                  document.body.appendChild(form);
                  form.submit();
                  return;
               }
               orderForm = this.closest('form');
               qrCodeModal.style.display = 'block';
               startCountdown();
            }
         });

         successBtn.addEventListener('click', function() {
            clearInterval(countdownInterval);
            const formData = new FormData(orderForm);
            formData.append('qr_success', '1');

            fetch(window.location.href, {
                  method: 'POST',
                  body: formData
               })
               .then(response => response.text())
               .then(result => {
                  if (result === "success") {
                     qrCodeModal.style.display = 'none';
                     window.location.reload();
                  } else {
                     const form = document.createElement('form');
                     form.method = 'POST';
                     form.innerHTML = '<input type="hidden" name="message" value="Đặt hàng thất bại!">';
                     document.body.appendChild(form);
                     form.submit();
                  }
               })
               .catch(error => {
                  console.error('Error:', error);
                  const form = document.createElement('form');
                  form.method = 'POST';
                  form.innerHTML = '<input type="hidden" name="message" value="Đặt hàng thất bại!">';
                  document.body.appendChild(form);
                  form.submit();
               });
         });

         closeBtn.addEventListener('click', function() {
            qrCodeModal.style.display = 'none';
            clearInterval(countdownInterval);
         });

         window.addEventListener('click', function(e) {
            if (e.target === qrCodeModal) {
               qrCodeModal.style.display = 'none';
               clearInterval(countdownInterval);
            }
         });

         function startCountdown() {
            let timeLeft = 30;
            const countdownElement = document.querySelector('.countdown');

            countdownInterval = setInterval(function() {
               timeLeft--;
               countdownElement.textContent = timeLeft;

               if (timeLeft <= 0) {
                  clearInterval(countdownInterval);
                  qrCodeModal.style.display = 'none';
                  const form = document.createElement('form');
                  form.method = 'POST';
                  form.innerHTML = '<input type="hidden" name="message" value="Thanh toán thất bại, vui lòng thử lại">';
                  document.body.appendChild(form);
                  form.submit();
               }
            }, 1000);
         }
      });
   </script>

   <!-- custom admin js file link  -->
   <script src="js/admin_script.js"></script>

</body>

</html>
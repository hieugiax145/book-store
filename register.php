<?php

include 'config.php';

if(isset($_POST['submit'])){

   $username = mysqli_real_escape_string($conn, $_POST['username']);
   $hoTen = mysqli_real_escape_string($conn, $_POST['hoTen']);
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $pass = mysqli_real_escape_string($conn, password_hash($_POST['password'], PASSWORD_DEFAULT));
   $cpass = $_POST['cpassword'];
   $vaiTro = $_POST['vaiTro'];
   $ngaySinh = !empty($_POST['ngaySinh']) ? mysqli_real_escape_string($conn, $_POST['ngaySinh']) : NULL;
   $diaChi = mysqli_real_escape_string($conn, $_POST['diaChi']);
   $sdt = mysqli_real_escape_string($conn, $_POST['sdt']);
   $moTa = mysqli_real_escape_string($conn, $_POST['moTa']);

   $select_users = mysqli_query($conn, "SELECT * FROM `nguoi_dung` WHERE email = '$email' OR username = '$username'") or die('query failed');

   if(mysqli_num_rows($select_users) > 0){
      $message[] = 'Tài khoản hoặc email đã tồn tại!';
   }else{
      if(!password_verify($cpass, $pass)){
         $message[] = 'Mật khẩu xác nhận không khớp!';
      }else{
         mysqli_query($conn, "INSERT INTO `nguoi_dung`(username, password, hoTen, ngaySinh, diaChi, sdt, email, vaiTro, moTa) 
         VALUES('$username', '$pass', '$hoTen', " . ($ngaySinh ? "'$ngaySinh'" : "NULL") . ", '$diaChi', '$sdt', '$email', '$vaiTro', '$moTa')") or die('query failed');
         $message[] = 'Đăng ký thành công!';
         header('location:login.php');
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
   <title>Đăng ký</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>

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
   
<div class="form-container">

   <form action="" method="post">
   <h3>Đăng ký ngay</h3>
   <input type="text" name="username" placeholder="Tên đăng nhập" required class="box">
   <input type="text" name="hoTen" placeholder="Họ và tên" required class="box">
   <input type="email" name="email" placeholder="Email" required class="box">
   <input type="password" name="password" placeholder="Mật khẩu" required class="box">
   <input type="password" name="cpassword" placeholder="Xác nhận mật khẩu" required class="box">
   <input type="date" name="ngaySinh" placeholder="Ngày sinh" class="box">
   <input type="text" name="diaChi" placeholder="Địa chỉ" class="box">
   <input type="text" name="sdt" placeholder="Số điện thoại" class="box">
   <input type="text" name="moTa" placeholder="Mô tả" class="box">
   <select name="vaiTro" class="box">
      <option value="customer">Khách hàng</option>
      <option value="admin">Quản trị viên</option>
   </select>
   <input type="submit" name="submit" value="Đăng ký" class="btn">
   <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
</form>

</div>

</body>
</html>
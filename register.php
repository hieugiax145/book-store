<?php

include 'config.php';

if(isset($_POST['submit'])){

   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $pass = mysqli_real_escape_string($conn, md5($_POST['password']));
   $cpass = mysqli_real_escape_string($conn, md5($_POST['cpassword']));
   $user_type = $_POST['user_type'];
   $birthday = mysqli_real_escape_string($conn, $_POST['birthday']);
   $address = mysqli_real_escape_string($conn, $_POST['address']);
   $phone = mysqli_real_escape_string($conn, $_POST['phone']);
   $des = mysqli_real_escape_string($conn, $_POST['des']);

   $select_users = mysqli_query($conn, "SELECT * FROM `users` WHERE email = '$email' AND password = '$pass'") or die('query failed');

   if(mysqli_num_rows($select_users) > 0){
      $message[] = 'user already exist!';
   }else{
      if($pass != $cpass){
         $message[] = 'confirm password not matched!';
      }else{
         mysqli_query($conn, "INSERT INTO `users`(name, email, password, user_type, birthday, address, phone, des) VALUES('$name', '$email', '$cpass', '$user_type', '$birthday', '$address', '$phone', '$des')") or die('query failed');
         $message[] = 'registered successfully!';
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
   <title>register</title>

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
   <input type="text" name="name" placeholder="Tên" required class="box">
   <input type="email" name="email" placeholder="Email" required class="box">
   <input type="password" name="password" placeholder="Mật khẩu" required class="box">
   <input type="password" name="cpassword" placeholder="Xác nhận mật khẩu" required class="box">
   <input type="date" name="birthday" placeholder="Ngày sinh" class="box">
   <input type="text" name="address" placeholder="Địa chỉ" class="box">
   <input type="text" name="phone" placeholder="Số điện thoại" class="box">
   <input type="text" name="des" placeholder="Mô tả" class="box">
   <select name="user_type" class="box">
      <option value="user">user</option>
      <option value="admin">admin</option>
   </select>
   <input type="submit" name="submit" value="Đăng ký" class="btn">
   <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
</form>

</div>

</body>
</html>
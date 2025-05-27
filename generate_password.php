<?php
$password = "admin123"; // This will be your new password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);
echo $hashed_password;
?> 
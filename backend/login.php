<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXO - Login Backend</title>
</head>
<body>
     <?php

     session_start();

     if ($_SERVER["REQUEST_METHOD"] == "POST") {
         $username = $_POST["username"];
         $password = $_POST["password"];

         if ($username == "admin" && $password == "password") {
             $_SESSION["loggedin"] = true;
             header("Location: index.php");
             exit;
         } else {
             $error = "Invalid username or password.";
         }
     }

     ?>
</body>
</html>
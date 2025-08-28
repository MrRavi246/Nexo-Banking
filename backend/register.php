<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexo - Register</title>
</head>
<body>
    <?php
        session_start();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $account_number = $_POST["account_number"];
            $password = $_POST["password"];
        
            $_SESSION["user"] = [
                "account_number" => $account_number,
                "logged_in" => true
            ];

            


            header("Location: ../../dashboard.html");
            exit();
        }
    ?>
</body>
</html>
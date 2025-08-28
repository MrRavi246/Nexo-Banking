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
        include 'db.php';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $account_number = $_POST['account_number'];
            $password = $_POST['password'];

            
            $stmt = $conn->prepare("SELECT * FROM users WHERE account_number = ? AND password = ?");
            $stmt->bind_param("ss", $account_number, $password); 
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
            
                $_SESSION['user'] = $account_number;
                header("Location: ../Pages/dashboard.html");
                exit();
            } else {
            
                echo "<script>alert('Invalid account number or password.');</script>";
            }
        }
    ?>
</body>
</html>
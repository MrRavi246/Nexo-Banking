<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style/signup.css">
    

</head>

<body>
    <?php

    $host = 'localhost';
    $db   = 'miniproject';
    $user = 'root';
    $pass = '';
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

    $message = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $pdo = new PDO($dsn, $user, $pass, $options);


            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $phone_number = trim($_POST['phone_number']);
            $date_of_birth = $_POST['date_of_birth'];
            $address = trim($_POST['address']);
            $member_type = $_POST['member_type'];


            $profile_image = null;
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $target_dir = "uploads/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                $filename = uniqid() . '_' . basename($_FILES['profile_image']['name']);
                $target_file = $target_dir . $filename;
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                    $profile_image = $target_file;
                }
            }


            $password_hash = password_hash($password, PASSWORD_DEFAULT);


            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name, phone_number, date_of_birth, address, profile_image, member_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $username,
                $email,
                $password_hash,
                $first_name,
                $last_name,
                $phone_number,
                $date_of_birth,
                $address,
                $profile_image,
                $member_type
            ]);
            $message = "Signup successful!";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = "Username or email already exists.";
            } else {
                $message = "Error: " . htmlspecialchars($e->getMessage());
            }
        }
    }
    ?>

    <h2>Signup</h2>
    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form method="post" class="signup-form" enctype="multipart/form-data">
        <label>Username: <input type="text" class="form-control" name="username" required maxlength="50"></label><br>
        <label>Email: <input type="email" class="form-control" name="email" required maxlength="100"></label><br>
        <label>Password: <input type="password" class="form-control" name="password" required></label><br>
        <label>First Name: <input type="text" class="form-control" name="first_name" required maxlength="50"></label><br>
        <label>Last Name: <input type="text" class="form-control" name="last_name" required maxlength="50"></label><br>
        <label>Phone Number: <input type="text" class="form-control" name="phone_number" maxlength="20"></label><br>
        <label>Date of Birth: <input type="date" class="form-control" name="date_of_birth"></label><br>
        <label>Address: <textarea class="form-control" name="address"></textarea></label><br>
        <label>Profile Image: <input type="file" class="form-control" name="profile_image" accept="image/*"></label><br>
        <label>Member Type:
            <select class="form-control" name="member_type">
                <option value="basic">Basic</option>
                <option value="premium">Premium</option>
                <option value="platinum">Platinum</option>
            </select>
        </label><br>
        <button type="submit">Sign Up</button>
    </form>
</body>

</html>
<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_loggedin']) && $_SESSION['admin_loggedin'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // DB connection parameters (same used in signup.php)
    $host = 'localhost';
    $db   = 'miniproject';
    $user = 'root';
    $pass = '';
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        // Try to find an admin record in admins table
        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM admins WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            if (password_verify($password, $admin['password_hash'])) {
                $_SESSION['admin_loggedin'] = true;
                $_SESSION['admin_username'] = $admin['username'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            // fallback: if no admins table or user not found, allow default credential
            if ($username === 'admin' && $password === 'password') {
                $_SESSION['admin_loggedin'] = true;
                $_SESSION['admin_username'] = 'admin';
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        }
    } catch (PDOException $e) {
        // DB not available -> fallback credential
        if ($username === 'admin' && $password === 'password') {
            $_SESSION['admin_loggedin'] = true;
            $_SESSION['admin_username'] = 'admin';
            header('Location: dashboard.php');
            exit;
        }
        $error = 'Database error: ' . htmlspecialchars($e->getMessage());
    }
}
?>
<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Login - Nexo Banking</title>
    <link rel="stylesheet" href="../assets/style/nav.css">
    <link rel="stylesheet" href="../assets/style/admin.css">
  </head>
  <body>
    <div class="admin-login-wrap">
      <div class="admin-login-card">
        <h2>Admin Panel</h2>
        <?php if ($error): ?>
          <div class="admin-error"><?=htmlspecialchars($error)?></div>
        <?php endif; ?>

        <form method="post" action="login.php">
          <label>Username
            <input type="text" name="username" required autofocus>
          </label>
          <label>Password
            <input type="password" name="password" required>
          </label>
          <div class="admin-actions">
            <button type="submit" class="btn-primary">Sign in</button>
          </div>
        </form>
        <p class="small">Default admin: <code>admin</code> / <code>password</code> (create an admin record in DB for production)</p>
      </div>
    </div>
    <script src="../assets/js/admin.js"></script>
  </body>
</html>

<?php
session_start();
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

$host = 'localhost';
$db   = 'miniproject';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

$message = '';
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Handle deletion
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
        $id = (int)$_POST['delete_id'];
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $message = 'User deleted.';
    }

    // Fetch users
    $stmt = $pdo->query('SELECT id, username, email, first_name, last_name FROM users ORDER BY id DESC');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
    $message = 'Database error: ' . htmlspecialchars($e->getMessage());
}

include __DIR__ . '/_header.php';
?>

    <section class="admin-content">
        <h1>Users</h1>
        <?php if ($message): ?><div class="admin-info"><?= $message ?></div><?php endif; ?>
        <?php if (empty($users)): ?>
            <p>No users found.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Name</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?=htmlspecialchars($u['id'])?></td>
                        <td><?=htmlspecialchars($u['username'])?></td>
                        <td><?=htmlspecialchars($u['email'])?></td>
                        <td><?=htmlspecialchars($u['first_name'] . ' ' . $u['last_name'])?></td>
                        <td>
                            <form method="post" style="display:inline">
                                <input type="hidden" name="delete_id" value="<?=htmlspecialchars($u['id'])?>">
                                <button type="submit" class="btn-secondary" onclick="return confirm('Delete user?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

<?php include __DIR__ . '/_footer.php';

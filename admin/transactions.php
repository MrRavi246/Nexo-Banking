<?php
session_start();
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// DB connection (same convention as other admin pages)
$host = 'localhost';
$db   = 'miniproject';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

$message = '';
$transactions = [];

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Basic search and filtering
    $where = [];
    $params = [];

    if (!empty($_GET['q'])) {
        $where[] = "(from_account LIKE ? OR to_account LIKE ? OR type LIKE ? OR status LIKE ? OR id LIKE ?)";
        $q = '%' . $_GET['q'] . '%';
        $params = array_merge($params, [$q, $q, $q, $q, $q]);
    }

    if (!empty($_GET['status'])) {
        $where[] = 'status = ?';
        $params[] = $_GET['status'];
    }

    $sql = 'SELECT id, from_account, to_account, amount, currency, type, status, created_at FROM transactions';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY created_at DESC LIMIT 500';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = 'Database error: ' . htmlspecialchars($e->getMessage());
}

include __DIR__ . '/_header.php';
?>

    <section class="admin-content">
        <h1>Transactions</h1>

        <form method="get" class="admin-filter" style="display:flex;gap:8px;align-items:center;margin-bottom:12px">
            <input type="text" name="q" placeholder="Search by account, type, status or id" value="<?=htmlspecialchars($_GET['q'] ?? '')?>" style="flex:1;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,0.06);background:transparent;color:#fff">
            <select name="status" style="padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,0.06);background:transparent;color:#fff">
                <option value="">All Statuses</option>
                <option value="pending" <?= (isset($_GET['status']) && $_GET['status']=='pending')?'selected':''?>>Pending</option>
                <option value="completed" <?= (isset($_GET['status']) && $_GET['status']=='completed')?'selected':''?>>Completed</option>
                <option value="failed" <?= (isset($_GET['status']) && $_GET['status']=='failed')?'selected':''?>>Failed</option>
            </select>
            <button class="btn-primary">Filter</button>
        </form>

        <?php if ($message): ?>
            <div class="admin-info"><?= $message ?></div>
        <?php endif; ?>

        <?php if (empty($transactions)): ?>
            <p>No transactions found.</p>
        <?php else: ?>
            <div style="overflow:auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td><?=htmlspecialchars($t['id'])?></td>
                        <td><?=htmlspecialchars($t['from_account'] ?? '')?></td>
                        <td><?=htmlspecialchars($t['to_account'] ?? '')?></td>
                        <td><?=htmlspecialchars((isset($t['currency'])?$t['currency'].' ':'').number_format($t['amount'],2))?></td>
                        <td><?=htmlspecialchars($t['type'] ?? '')?></td>
                        <td><?=htmlspecialchars($t['status'] ?? '')?></td>
                        <td><?=htmlspecialchars($t['created_at'] ?? '')?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>
    </section>

<?php include __DIR__ . '/_footer.php';

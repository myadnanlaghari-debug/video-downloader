<?php
/**
 * User Transactions History Page
 */

require_once '../config/database.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

startSecureSession();
requireLogin();

$pdo = (new Database())->getConnection();
$user_id = getCurrentUserId();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Filter
$type_filter = isset($_GET['type']) ? sanitizeInput($_GET['type']) : '';
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

// Build query
$where_clauses = ["user_id = ?"];
$params = [$user_id];

if ($type_filter) {
    $where_clauses[] = "type = ?";
    $params[] = $type_filter;
}

if ($status_filter) {
    $where_clauses[] = "status = ?";
    $params[] = $status_filter;
}

$where_sql = implode(' AND ', $where_clauses);

try {
    // Get total count
    $count_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM transactions WHERE $where_sql");
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_records / $limit);
    
    // Get transactions
    $stmt = $pdo->prepare("
        SELECT * FROM transactions 
        WHERE $where_sql 
        ORDER BY created_at DESC 
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute($params);
    $transactions = $stmt->fetchAll();
} catch (PDOException $e) {
    $transactions = [];
    $total_pages = 0;
}

$page_title = 'Transactions';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                <h1 class="h2 text-gradient"><i class="fas fa-history me-2"></i>Transaction History</h1>
            </div>
            
            <!-- Filters -->
            <div class="card glass-card shadow-primary mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Filter by Type</label>
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="deposit" <?php echo $type_filter === 'deposit' ? 'selected' : ''; ?>>Deposit</option>
                                <option value="withdrawal" <?php echo $type_filter === 'withdrawal' ? 'selected' : ''; ?>>Withdrawal</option>
                                <option value="investment" <?php echo $type_filter === 'investment' ? 'selected' : ''; ?>>Investment</option>
                                <option value="profit" <?php echo $type_filter === 'profit' ? 'selected' : ''; ?>>Profit</option>
                                <option value="referral" <?php echo $type_filter === 'referral' ? 'selected' : ''; ?>>Referral</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Filter by Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                            <a href="transactions.php" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-2"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Transactions Table -->
            <div class="card glass-card shadow-success">
                <div class="card-body p-0">
                    <?php if (empty($transactions)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
                            <h5>No transactions found.</h5>
                            <p class="text-muted">Your transaction history will appear here.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 transaction-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $txn): ?>
                                        <tr>
                                            <td>#<?php echo $txn['id']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $txn['type'] === 'deposit' ? 'success' : 
                                                        ($txn['type'] === 'withdrawal' ? 'danger' : 
                                                        ($txn['type'] === 'profit' ? 'warning' : 'info')); 
                                                ?>">
                                                    <i class="fas fa-<?php 
                                                        echo $txn['type'] === 'deposit' ? 'arrow-down' : 
                                                            ($txn['type'] === 'withdrawal' ? 'arrow-up' : 
                                                            ($txn['type'] === 'profit' ? 'coins' : 'exchange')); 
                                                    ?> me-1"></i>
                                                    <?php echo ucfirst($txn['type']); ?>
                                                </span>
                                            </td>
                                            <td class="<?php echo $txn['amount'] > 0 ? 'text-success' : 'text-danger'; ?> fw-bold">
                                                <?php echo ($txn['amount'] > 0 ? '+' : '') . formatCurrency($txn['amount']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($txn['description']); ?></td>
                                            <td>
                                                <span class="badge badge-completed">Completed</span>
                                            </td>
                                            <td><?php echo formatDate($txn['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="p-4">
                                <nav aria-label="Transaction pagination">
                                    <ul class="pagination justify-content-center mb-0">
                                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $type_filter ? '&type=' . $type_filter : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">
                                                <i class="fas fa-chevron-left"></i> Previous
                                            </a>
                                        </li>
                                        
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $type_filter ? '&type=' . $type_filter : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $type_filter ? '&type=' . $type_filter : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">
                                                Next <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Summary Cards -->
            <div class="row mt-4 g-4">
                <div class="col-md-3">
                    <div class="card stat-card success h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-arrow-down fa-2x text-success mb-2"></i>
                            <h6 class="text-muted">Total Deposits</h6>
                            <?php
                            $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND type = 'deposit'");
                            $stmt->execute([$user_id]);
                            echo '<h4 class="text-success">' . formatCurrency($stmt->fetch()['total']) . '</h4>';
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card danger h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-arrow-up fa-2x text-danger mb-2"></i>
                            <h6 class="text-muted">Total Withdrawals</h6>
                            <?php
                            $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND type = 'withdrawal'");
                            $stmt->execute([$user_id]);
                            echo '<h4 class="text-danger">' . formatCurrency(abs($stmt->fetch()['total'])) . '</h4>';
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card warning h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-coins fa-2x text-warning mb-2"></i>
                            <h6 class="text-muted">Total Profits</h6>
                            <?php
                            $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND type = 'profit'");
                            $stmt->execute([$user_id]);
                            echo '<h4 class="text-warning">' . formatCurrency($stmt->fetch()['total']) . '</h4>';
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-gift fa-2x text-info mb-2"></i>
                            <h6 class="text-muted">Referral Earnings</h6>
                            <?php
                            $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND type = 'referral'");
                            $stmt->execute([$user_id]);
                            echo '<h4 class="text-info">' . formatCurrency($stmt->fetch()['total']) . '</h4>';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

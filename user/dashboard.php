<?php
/**
 * User Dashboard
 */

require_once '../config/database.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

startSecureSession();
requireLogin();

$pdo = (new Database())->getConnection();
$user_id = getCurrentUserId();

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    // Get balance
    $balance = $user['balance'];
    
    // Get total deposits
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM deposits WHERE user_id = ? AND status = 'approved'");
    $stmt->execute([$user_id]);
    $total_deposits = $stmt->fetch()['total'];
    
    // Get total withdrawals
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM withdrawals WHERE user_id = ? AND status = 'completed'");
    $stmt->execute([$user_id]);
    $total_withdrawals = $stmt->fetch()['total'];
    
    // Get active investments
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM investments WHERE user_id = ? AND status = 'active'");
    $stmt->execute([$user_id]);
    $active_investments = $stmt->fetch()['total'];
    
    // Get total profits
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND type = 'profit'");
    $stmt->execute([$user_id]);
    $total_profits = $stmt->fetch()['total'];
    
    // Get recent transactions
    $stmt = $pdo->prepare("
        SELECT * FROM transactions 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $recent_transactions = $stmt->fetchAll();
    
    // Get pending deposits count
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM deposits WHERE user_id = ? AND status = 'pending'");
    $stmt->execute([$user_id]);
    $pending_deposits = $stmt->fetch()['count'];
    
    // Get pending withdrawals count
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM withdrawals WHERE user_id = ? AND status IN ('pending', 'processing')");
    $stmt->execute([$user_id]);
    $pending_withdrawals = $stmt->fetch()['count'];
    
    // Get active investment plans count
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM investments WHERE user_id = ? AND status = 'active'");
    $stmt->execute([$user_id]);
    $active_plans_count = $stmt->fetch()['count'];
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = 'Dashboard';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <span class="text-muted">Welcome, <?php echo htmlspecialchars($user['username']); ?></span>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card dashboard-card bg-primary text-white h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-subtitle mb-2">Available Balance</h6>
                                <h3 class="card-title mb-0"><?php echo formatCurrency($balance); ?></h3>
                            </div>
                            <i class="fas fa-wallet card-icon"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card dashboard-card bg-success text-white h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-subtitle mb-2">Total Deposits</h6>
                                <h3 class="card-title mb-0"><?php echo formatCurrency($total_deposits); ?></h3>
                            </div>
                            <i class="fas fa-arrow-down card-icon"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card dashboard-card bg-info text-white h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-subtitle mb-2">Active Investments</h6>
                                <h3 class="card-title mb-0"><?php echo formatCurrency($active_investments); ?></h3>
                            </div>
                            <i class="fas fa-chart-line card-icon"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card dashboard-card bg-warning text-dark h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-subtitle mb-2">Total Profits</h6>
                                <h3 class="card-title mb-0"><?php echo formatCurrency($total_profits); ?></h3>
                            </div>
                            <i class="fas fa-coins card-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <a href="deposit.php" class="btn btn-success w-100 py-3">
                                        <i class="fas fa-plus-circle fa-2x mb-2"></i><br>
                                        Deposit
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <a href="withdraw.php" class="btn btn-danger w-100 py-3">
                                        <i class="fas fa-minus-circle fa-2x mb-2"></i><br>
                                        Withdraw
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <a href="invest.php" class="btn btn-primary w-100 py-3">
                                        <i class="fas fa-hand-holding-usd fa-2x mb-2"></i><br>
                                        Invest
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="transactions.php" class="btn btn-info w-100 py-3">
                                        <i class="fas fa-history fa-2x mb-2"></i><br>
                                        Transactions
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Transactions & Active Plans -->
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Transactions</h5>
                            <a href="transactions.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_transactions)): ?>
                                <p class="text-muted text-center mb-0">No transactions yet.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Amount</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_transactions as $transaction): ?>
                                                <tr>
                                                    <td>
                                                        <i class="fas fa-<?php 
                                                            echo $transaction['type'] === 'deposit' ? 'arrow-down text-success' : 
                                                                ($transaction['type'] === 'withdrawal' ? 'arrow-up text-danger' : 
                                                                ($transaction['type'] === 'profit' ? 'coins text-warning' : 'exchange text-info')); 
                                                        ?> me-2"></i>
                                                        <?php echo ucfirst($transaction['type']); ?>
                                                    </td>
                                                    <td class="<?php echo $transaction['type'] === 'deposit' || $transaction['type'] === 'profit' ? 'text-success' : 'text-danger'; ?>">
                                                        <?php echo ($transaction['type'] === 'deposit' || $transaction['type'] === 'profit' ? '+' : '-'); ?>
                                                        <?php echo formatCurrency($transaction['amount']); ?>
                                                    </td>
                                                    <td><?php echo formatDate($transaction['created_at']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo getStatusBadgeClass('completed'); ?>">
                                                            Completed
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Investment Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <div class="display-4 text-primary"><?php echo $active_plans_count; ?></div>
                                <p class="text-muted">Active Plans</p>
                            </div>
                            
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Pending Deposits
                                    <span class="badge bg-warning rounded-pill"><?php echo $pending_deposits; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Pending Withdrawals
                                    <span class="badge bg-info rounded-pill"><?php echo $pending_withdrawals; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Total Withdrawn
                                    <span class="text-success fw-bold"><?php echo formatCurrency($total_withdrawals); ?></span>
                                </li>
                            </ul>
                            
                            <div class="mt-4">
                                <a href="plans.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-list me-2"></i>View All Plans
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Referral Info -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-gift me-2"></i>Refer & Earn</h5>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted mb-2">Your Referral Code:</p>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['referral_code']); ?>" readonly>
                                <button class="btn btn-outline-primary copy-btn" type="button" data-copy-text="<?php echo htmlspecialchars($user['referral_code']); ?>">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <small class="text-muted">Earn 5% on deposits & 2% on investments!</small>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

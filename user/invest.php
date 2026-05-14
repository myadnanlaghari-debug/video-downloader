<?php
/**
 * User Investment Page - Invest in Plans
 */

require_once '../config/database.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

startSecureSession();
requireLogin();

$pdo = (new Database())->getConnection();
$user_id = getCurrentUserId();

$error = '';
$success = '';

// Get user balance
try {
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    $balance = $user['balance'];
} catch (PDOException $e) {
    $balance = 0;
}

// Handle investment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plan_id = $_POST['plan_id'] ?? 0;
    $amount = $_POST['amount'] ?? 0;
    
    // Validate input
    if ($plan_id <= 0 || $amount <= 0) {
        $error = 'Please select a valid plan and enter amount.';
    } else {
        try {
            // Get plan details
            $stmt = $pdo->prepare("SELECT * FROM investment_plans WHERE id = ? AND status = 'active'");
            $stmt->execute([$plan_id]);
            $plan = $stmt->fetch();
            
            if (!$plan) {
                $error = 'Invalid or inactive plan selected.';
            } elseif ($amount < $plan['min_amount']) {
                $error = 'Minimum investment for this plan is ' . formatCurrency($plan['min_amount']);
            } elseif ($amount > $plan['max_amount']) {
                $error = 'Maximum investment for this plan is ' . formatCurrency($plan['max_amount']);
            } elseif ($amount > $balance) {
                $error = 'Insufficient balance. Your current balance is ' . formatCurrency($balance);
            } else {
                $pdo->beginTransaction();
                
                // Deduct balance
                $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
                $stmt->execute([$amount, $user_id]);
                
                // Calculate total profit
                $total_profit = ($amount * $plan['profit_percentage'] / 100) * $plan['duration_days'];
                
                // Create investment
                $stmt = $pdo->prepare("
                    INSERT INTO investments (user_id, plan_id, amount, profit_percentage, duration_days, total_profit, start_date, end_date, status) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? DAY), 'active')
                ");
                $stmt->execute([
                    $user_id, 
                    $plan_id, 
                    $amount, 
                    $plan['profit_percentage'], 
                    $plan['duration_days'],
                    $total_profit,
                    $plan['duration_days']
                ]);
                
                // Record transaction
                $stmt = $pdo->prepare("
                    INSERT INTO transactions (user_id, type, amount, description, reference_id) 
                    VALUES (?, 'investment', ?, ?, ?)
                ");
                $stmt->execute([$user_id, -$amount, 'Invested in ' . $plan['plan_name'], $plan_id]);
                
                $pdo->commit();
                
                $success = 'Investment activated successfully! You will start earning profits soon.';
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'An error occurred. Please try again later.';
        }
    }
}

// Fetch active plans
try {
    $stmt = $pdo->prepare("SELECT * FROM investment_plans WHERE status = 'active' ORDER BY min_amount");
    $stmt->execute();
    $plans = $stmt->fetchAll();
} catch (PDOException $e) {
    $plans = [];
}

$page_title = 'Invest';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                <h1 class="h2 text-gradient"><i class="fas fa-hand-holding-usd me-2"></i>Invest Now</h1>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Balance Info -->
            <div class="alert alert-info mb-4 glass-card shadow-primary">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <i class="fas fa-wallet me-2"></i>
                        <strong>Your Available Balance:</strong>
                    </div>
                    <span class="fs-3 fw-bold text-success"><?php echo formatCurrency($balance); ?></span>
                </div>
            </div>
            
            <!-- Investment Plans -->
            <?php if (empty($plans)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
                    <h4>No investment plans available at the moment.</h4>
                    <p class="text-muted">Please check back later or contact support.</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($plans as $index => $plan): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="card plan-card h-100 <?php echo $index === 1 ? 'popular' : ''; ?>">
                                <div class="card-header text-center">
                                    <h4 class="mb-0"><?php echo htmlspecialchars($plan['plan_name']); ?></h4>
                                    <?php if ($index === 1): ?>
                                        <span class="badge bg-warning mt-2">MOST POPULAR</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body text-center d-flex flex-column">
                                    <div class="mb-4">
                                        <small class="text-muted">Daily Profit</small>
                                        <div class="plan-price my-2"><?php echo $plan['profit_percentage']; ?>%</div>
                                    </div>
                                    
                                    <ul class="list-unstyled mb-4 flex-grow-1">
                                        <li class="py-2 border-bottom">
                                            <i class="fas fa-coins text-primary me-2"></i>
                                            Min: <strong><?php echo formatCurrency($plan['min_amount']); ?></strong>
                                        </li>
                                        <li class="py-2 border-bottom">
                                            <i class="fas fa-coins text-success me-2"></i>
                                            Max: <strong><?php echo formatCurrency($plan['max_amount']); ?></strong>
                                        </li>
                                        <li class="py-2 border-bottom">
                                            <i class="fas fa-calendar-alt text-info me-2"></i>
                                            Duration: <strong><?php echo $plan['duration_days']; ?> Days</strong>
                                        </li>
                                        <li class="py-2">
                                            <i class="fas fa-chart-line text-warning me-2"></i>
                                            Total Return: <strong><?php echo ($plan['profit_percentage'] * $plan['duration_days']); ?>%</strong>
                                        </li>
                                    </ul>
                                    
                                    <form method="POST" class="mt-auto">
                                        <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                                        <div class="mb-3">
                                            <label class="form-label small">Investment Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                                <input type="number" step="0.01" class="form-control" name="amount" 
                                                       min="<?php echo $plan['min_amount']; ?>" 
                                                       max="<?php echo min($plan['max_amount'], $balance); ?>"
                                                       placeholder="Enter amount" required>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-bolt me-2"></i>Invest Now
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Active Investments -->
            <div class="mt-5">
                <h4 class="mb-3"><i class="fas fa-chart-pie me-2"></i>Your Active Investments</h4>
                <div class="card glass-card shadow-success">
                    <div class="card-body p-0">
                        <?php
                        try {
                            $stmt = $pdo->prepare("
                                SELECT i.*, p.plan_name 
                                FROM investments i 
                                JOIN investment_plans p ON i.plan_id = p.id 
                                WHERE i.user_id = ? AND i.status = 'active' 
                                ORDER BY i.created_at DESC
                            ");
                            $stmt->execute([$user_id]);
                            $investments = $stmt->fetchAll();
                            
                            if (empty($investments)):
                        ?>
                            <p class="text-muted text-center py-4 mb-0">No active investments yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Plan</th>
                                            <th>Amount</th>
                                            <th>Daily Profit</th>
                                            <th>Duration</th>
                                            <th>Total Profit</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($investments as $inv): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($inv['plan_name']); ?></strong></td>
                                                <td class="text-success"><?php echo formatCurrency($inv['amount']); ?></td>
                                                <td class="text-warning">+<?php echo ($inv['amount'] * $inv['profit_percentage'] / 100); ?>/day</td>
                                                <td><?php echo $inv['duration_days']; ?> days</td>
                                                <td class="text-info">+<?php echo formatCurrency($inv['total_profit']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($inv['start_date'])); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($inv['end_date'])); ?></td>
                                                <td><span class="badge badge-active">Active</span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php 
                            endif;
                        } catch (PDOException $e) {
                            echo '<p class="text-muted text-center py-4 mb-0">Unable to load investments.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

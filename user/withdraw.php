<?php
/**
 * User Withdraw Page - Manual Crypto Withdrawal
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

// Handle withdrawal submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'] ?? 0;
    $wallet_address = sanitizeInput($_POST['wallet_address'] ?? '');
    $network = sanitizeInput($_POST['network'] ?? '');
    $note = sanitizeInput($_POST['note'] ?? '');
    
    // Validate input
    if ($amount <= 0 || empty($wallet_address) || empty($network)) {
        $error = 'Please fill in all required fields.';
    } elseif ($amount > $balance) {
        $error = 'Insufficient balance. Your current balance is ' . formatCurrency($balance);
    } elseif ($amount < 10) {
        $error = 'Minimum withdrawal amount is 10 USDT.';
    } else {
        try {
            // Deduct balance immediately (will be refunded if rejected)
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $stmt->execute([$amount, $user_id]);
            
            $stmt = $pdo->prepare("
                INSERT INTO withdrawals (user_id, amount, wallet_address, network, note, status) 
                VALUES (?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([$user_id, $amount, $wallet_address, $network, $note]);
            
            $pdo->commit();
            
            $success = 'Withdrawal request submitted successfully! Please wait for admin processing.';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'An error occurred. Please try again later.';
        }
    }
}

$page_title = 'Withdraw';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                <h1 class="h2 text-gradient"><i class="fas fa-minus-circle me-2"></i>Withdraw Funds</h1>
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
            
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card glass-card shadow-primary">
                        <div class="card-header bg-gradient">
                            <h5 class="mb-0 text-white"><i class="fas fa-university me-2"></i>Request Withdrawal</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="alert alert-info mb-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-wallet me-2"></i>Your Available Balance:</span>
                                    <strong class="fs-4 text-success"><?php echo formatCurrency($balance); ?></strong>
                                </div>
                            </div>
                            
                            <form method="POST" id="withdrawForm">
                                <div class="mb-3">
                                    <label for="amount" class="form-label fw-bold">Withdrawal Amount *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-coins"></i></span>
                                        <input type="number" step="0.01" class="form-control" id="amount" name="amount" 
                                               required min="10" max="<?php echo $balance; ?>" placeholder="Enter amount">
                                        <button class="btn btn-outline-primary" type="button" onclick="setMaxAmount()">
                                            MAX
                                        </button>
                                    </div>
                                    <small class="text-muted">Minimum withdrawal: 10 USDT</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="network" class="form-label fw-bold">Network *</label>
                                    <select class="form-select" id="network" name="network" required>
                                        <option value="">Select Network</option>
                                        <option value="TRC20">USDT TRC20</option>
                                        <option value="BEP20">USDT BEP20</option>
                                        <option value="ERC20">USDT ERC20</option>
                                        <option value="BTC">Bitcoin Network</option>
                                        <option value="ETH">Ethereum Network</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="wallet_address" class="form-label fw-bold">Wallet Address *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-wallet"></i></span>
                                        <input type="text" class="form-control" id="wallet_address" name="wallet_address" 
                                               required placeholder="Paste your wallet address here">
                                        <button class="btn btn-outline-secondary" type="button" onclick="pasteFromClipboard()">
                                            <i class="fas fa-paste"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Double-check your address before submitting!</small>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="note" class="form-label fw-bold">Note (Optional)</label>
                                    <textarea class="form-control" id="note" name="note" rows="3" 
                                              placeholder="Any additional information..."></textarea>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Important:</strong> Make sure your wallet address is correct. 
                                    We are not responsible for funds sent to wrong addresses.
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-danger btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i>Submit Withdrawal Request
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="card glass-card shadow-warning">
                        <div class="card-header" style="background: var(--gradient-gold);">
                            <h5 class="mb-0 text-white"><i class="fas fa-info-circle me-2"></i>Withdrawal Process</h5>
                        </div>
                        <div class="card-body">
                            <ol class="list-group list-group-numbered list-group-flush">
                                <li class="list-group-item bg-transparent">Enter the amount you want to withdraw</li>
                                <li class="list-group-item bg-transparent">Select the network (TRC20, BEP20, etc.)</li>
                                <li class="list-group-item bg-transparent">Provide your wallet address</li>
                                <li class="list-group-item bg-transparent">Submit the request</li>
                                <li class="list-group-item bg-transparent">Wait for admin verification</li>
                                <li class="list-group-item bg-transparent">Receive crypto in your wallet</li>
                            </ol>
                            
                            <div class="alert alert-info mt-3 mb-0">
                                <i class="fas fa-clock me-2"></i>
                                <strong>Processing Time:</strong><br>
                                Withdrawals are processed within 24-48 hours after approval.
                            </div>
                        </div>
                    </div>
                    
                    <div class="card glass-card shadow-success mt-3">
                        <div class="card-header" style="background: var(--gradient-success);">
                            <h5 class="mb-0 text-white"><i class="fas fa-list me-2"></i>Your Recent Withdrawals</h5>
                        </div>
                        <div class="card-body p-0">
                            <?php
                            try {
                                $stmt = $pdo->prepare("
                                    SELECT * FROM withdrawals 
                                    WHERE user_id = ? 
                                    ORDER BY created_at DESC 
                                    LIMIT 5
                                ");
                                $stmt->execute([$user_id]);
                                $withdrawals = $stmt->fetchAll();
                                
                                if (empty($withdrawals)):
                            ?>
                                <p class="text-muted text-center py-4 mb-0">No withdrawals yet.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($withdrawals as $w): ?>
                                                <tr>
                                                    <td><?php echo formatCurrency($w['amount']); ?></td>
                                                    <td>
                                                        <span class="badge badge-<?php echo strtolower($w['status']); ?>">
                                                            <?php echo ucfirst($w['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td><small><?php echo date('M d', strtotime($w['created_at'])); ?></small></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php 
                                endif;
                            } catch (PDOException $e) {
                                echo '<p class="text-muted text-center py-4 mb-0">Unable to load withdrawals.</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function setMaxAmount() {
    document.getElementById('amount').value = '<?php echo $balance; ?>';
}

async function pasteFromClipboard() {
    try {
        const text = await navigator.clipboard.readText();
        document.getElementById('wallet_address').value = text;
    } catch (err) {
        alert('Failed to read clipboard. Please paste manually.');
    }
}
</script>

<?php include 'includes/footer.php'; ?>

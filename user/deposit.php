<?php
/**
 * User Deposit Page - Manual Crypto Deposit
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

// Handle deposit submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $wallet_id = $_POST['wallet_id'] ?? 0;
    $amount = $_POST['amount'] ?? 0;
    $txid = sanitizeInput($_POST['txid'] ?? '');
    $note = sanitizeInput($_POST['note'] ?? '');
    
    // Validate input
    if ($wallet_id <= 0 || $amount <= 0 || empty($txid)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Handle file upload
        $screenshot = '';
        if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadFile($_FILES['screenshot'], 'deposits');
            if ($uploadResult['success']) {
                $screenshot = $uploadResult['filename'];
            } else {
                $error = $uploadResult['message'];
            }
        }
        
        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO deposits (user_id, wallet_id, amount, txid, screenshot, note, status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'pending')
                ");
                $stmt->execute([$user_id, $wallet_id, $amount, $txid, $screenshot, $note]);
                
                $success = 'Deposit request submitted successfully! Please wait for admin approval.';
            } catch (PDOException $e) {
                $error = 'An error occurred. Please try again later.';
            }
        }
    }
}

// Fetch active wallets
try {
    $stmt = $pdo->prepare("SELECT * FROM wallets WHERE status = 'active' ORDER BY wallet_name");
    $stmt->execute();
    $wallets = $stmt->fetchAll();
} catch (PDOException $e) {
    $wallets = [];
}

$page_title = 'Deposit';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                <h1 class="h2 text-gradient"><i class="fas fa-plus-circle me-2"></i>Deposit Funds</h1>
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
                            <h5 class="mb-0 text-white"><i class="fas fa-wallet me-2"></i>Select Wallet & Submit Deposit</h5>
                        </div>
                        <div class="card-body p-4">
                            <?php if (empty($wallets)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-wallet fa-4x text-muted mb-3"></i>
                                    <p class="text-muted">No wallets available at the moment. Please contact support.</p>
                                </div>
                            <?php else: ?>
                                <form method="POST" enctype="multipart/form-data" id="depositForm">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Select Cryptocurrency</label>
                                        <div class="row g-3">
                                            <?php foreach ($wallets as $wallet): ?>
                                                <div class="col-md-6 col-lg-4">
                                                    <div class="wallet-card h-100" onclick="selectWallet(this, <?php echo $wallet['id']; ?>)">
                                                        <input type="radio" name="wallet_id" value="<?php echo $wallet['id']; ?>" class="d-none" required>
                                                        <div class="text-center">
                                                            <img src="../uploads/wallets/<?php echo htmlspecialchars($wallet['logo']); ?>" 
                                                                 alt="<?php echo htmlspecialchars($wallet['wallet_name']); ?>" 
                                                                 class="wallet-logo mb-3"
                                                                 onerror="this.src='https://via.placeholder.com/70?text=Crypto'">
                                                            <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($wallet['wallet_name']); ?></h6>
                                                            <small class="text-muted"><?php echo htmlspecialchars($wallet['network']); ?></small>
                                                            <p class="text-primary fw-bold mt-2">Min: <?php echo formatCurrency($wallet['min_deposit']); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="amount" class="form-label fw-bold">Deposit Amount *</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-coins"></i></span>
                                            <input type="number" step="0.01" class="form-control" id="amount" name="amount" required min="0.01">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="txid" class="form-label fw-bold">Transaction Hash (TXID) *</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-fingerprint"></i></span>
                                            <input type="text" class="form-control" id="txid" name="txid" required placeholder="Paste your transaction hash here">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="screenshot" class="form-label fw-bold">Upload Screenshot (Optional)</label>
                                        <input type="file" class="form-control" id="screenshot" name="screenshot" accept="image/*">
                                        <small class="text-muted">Supported: JPG, PNG, JPEG (Max 5MB)</small>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="note" class="form-label fw-bold">Note (Optional)</label>
                                        <textarea class="form-control" id="note" name="note" rows="3" placeholder="Any additional information..."></textarea>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="fas fa-paper-plane me-2"></i>Submit Deposit Request
                                        </button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="card glass-card shadow-warning">
                        <div class="card-header" style="background: var(--gradient-gold);">
                            <h5 class="mb-0 text-white"><i class="fas fa-info-circle me-2"></i>How to Deposit</h5>
                        </div>
                        <div class="card-body">
                            <ol class="list-group list-group-numbered list-group-flush">
                                <li class="list-group-item bg-transparent">Select your preferred cryptocurrency wallet</li>
                                <li class="list-group-item bg-transparent">Send crypto to the displayed wallet address</li>
                                <li class="list-group-item bg-transparent">Copy the transaction hash (TXID)</li>
                                <li class="list-group-item bg-transparent">Fill in the deposit form with amount and TXID</li>
                                <li class="list-group-item bg-transparent">Upload screenshot proof (optional)</li>
                                <li class="list-group-item bg-transparent">Submit and wait for admin approval</li>
                            </ol>
                            
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="fas fa-clock me-2"></i>
                                <strong>Processing Time:</strong><br>
                                Deposits are typically approved within 1-24 hours after blockchain confirmation.
                            </div>
                        </div>
                    </div>
                    
                    <div class="card glass-card shadow-success mt-3">
                        <div class="card-header" style="background: var(--gradient-success);">
                            <h5 class="mb-0 text-white"><i class="fas fa-shield-alt me-2"></i>Security Tips</h5>
                        </div>
                        <div class="card-body">
                            <ul class="mb-0 ps-3">
                                <li class="mb-2">Always verify the wallet address before sending</li>
                                <li class="mb-2">Use the correct network (TRC20, BEP20, etc.)</li>
                                <li class="mb-2">Keep your transaction hash safe</li>
                                <li>Minimum deposit amounts apply</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function selectWallet(element, walletId) {
    document.querySelectorAll('.wallet-card').forEach(card => {
        card.classList.remove('selected');
    });
    element.classList.add('selected');
    element.querySelector('input[type="radio"]').checked = true;
}
</script>

<?php include 'includes/footer.php'; ?>

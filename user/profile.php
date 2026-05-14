<?php
/**
 * User Profile Page
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

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = sanitizeInput($_POST['name'] ?? '');
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    
    if (empty($name) || empty($username) || empty($email)) {
        $error = 'All fields are required.';
    } elseif (!validateEmail($email)) {
        $error = 'Invalid email address.';
    } else {
        try {
            // Check if email is already taken by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetch()) {
                $error = 'Email address is already in use.';
            } else {
                // Check if username is already taken
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
                $stmt->execute([$username, $user_id]);
                if ($stmt->fetch()) {
                    $error = 'Username is already taken.';
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, email = ? WHERE id = ?");
                    $stmt->execute([$name, $username, $email, $user_id]);
                    
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                    
                    $success = 'Profile updated successfully!';
                    
                    // Refresh user data
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch();
                }
            }
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again later.';
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All password fields are required.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } elseif (strlen($new_password) < PASSWORD_MIN_LENGTH) {
        $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.';
    } elseif (!verifyPassword($current_password, $user['password'])) {
        $error = 'Current password is incorrect.';
    } else {
        try {
            $hashed_password = hashPassword($new_password);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $user_id]);
            
            $success = 'Password changed successfully!';
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again later.';
        }
    }
}

$page_title = 'Profile';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                <h1 class="h2 text-gradient"><i class="fas fa-user-circle me-2"></i>My Profile</h1>
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
                <!-- Profile Info Card -->
                <div class="col-lg-4 mb-4">
                    <div class="card glass-card shadow-primary text-center">
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <div class="avatar avatar-lg mx-auto bg-primary text-white d-flex align-items-center justify-content-center">
                                    <span class="fs-2"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></span>
                                </div>
                            </div>
                            <h4 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h4>
                            <p class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></p>
                            <p class="small text-muted mb-3"><?php echo htmlspecialchars($user['email']); ?></p>
                            
                            <div class="badge bg-success mb-3">
                                <i class="fas fa-check-circle me-1"></i> Active
                            </div>
                            
                            <div class="mt-4 pt-3 border-top">
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">Member Since</small>
                                        <div class="fw-bold"><?php echo date('M Y', strtotime($user['created_at'])); ?></div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Last Login</small>
                                        <div class="fw-bold"><?php echo $user['last_login'] ? date('M d', strtotime($user['last_login'])) : 'N/A'; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Referral Card -->
                    <div class="card glass-card shadow-warning mt-3">
                        <div class="card-header" style="background: var(--gradient-gold);">
                            <h5 class="mb-0 text-white"><i class="fas fa-gift me-2"></i>Refer & Earn</h5>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted mb-2">Your Referral Code:</p>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['referral_code']); ?>" readonly>
                                <button class="btn btn-outline-primary copy-btn" type="button" onclick="copyReferral()">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <p class="small text-muted mb-0">Earn 5% on deposits & 2% on investments!</p>
                        </div>
                    </div>
                </div>
                
                <!-- Edit Profile Form -->
                <div class="col-lg-8 mb-4">
                    <!-- Personal Information -->
                    <div class="card glass-card shadow-success mb-4">
                        <div class="card-header" style="background: var(--gradient-success);">
                            <h5 class="mb-0 text-white"><i class="fas fa-edit me-2"></i>Edit Profile Information</h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label fw-bold">Full Name</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="username" class="form-label fw-bold">Username</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-at"></i></span>
                                            <input type="text" class="form-control" id="username" name="username" 
                                                   value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label for="email" class="form-label fw-bold">Email Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" name="update_profile" class="btn btn-success">
                                        <i class="fas fa-save me-2"></i>Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Change Password -->
                    <div class="card glass-card shadow-primary">
                        <div class="card-header bg-gradient">
                            <h5 class="mb-0 text-white"><i class="fas fa-lock me-2"></i>Change Password</h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label fw-bold">Current Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                </div>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="new_password" class="form-label fw-bold">New Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="confirm_password" class="form-label fw-bold">Confirm New Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" name="change_password" class="btn btn-primary">
                                        <i class="fas fa-shield-alt me-2"></i>Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function copyReferral() {
    const referralCode = '<?php echo htmlspecialchars($user['referral_code']); ?>';
    navigator.clipboard.writeText(referralCode).then(() => {
        alert('Referral code copied to clipboard!');
    }).catch(err => {
        alert('Failed to copy. Please copy manually.');
    });
}
</script>

<?php include 'includes/footer.php'; ?>

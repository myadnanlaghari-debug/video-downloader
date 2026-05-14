<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crypto Invest Pro - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-coins"></i> Crypto Invest Pro
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#plans">Investment Plans</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#faq">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-light me-2" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-warning" href="register.php">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section py-5 bg-gradient">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold text-white mb-4">Grow Your Crypto Portfolio</h1>
                    <p class="lead text-white mb-4">Invest in cryptocurrency with confidence. Our platform offers secure manual deposits, verified withdrawals, and profitable investment plans.</p>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                        <a href="register.php" class="btn btn-warning btn-lg px-4 me-md-2">Get Started</a>
                        <a href="#plans" class="btn btn-outline-light btn-lg px-4">View Plans</a>
                    </div>
                </div>
                <div class="col-lg-6 text-center mt-5 mt-lg-0">
                    <img src="assets/images/hero-crypto.svg" alt="Crypto Investment" class="img-fluid" onerror="this.style.display='none'">
                    <div class="text-white">
                        <i class="fas fa-chart-line fa-10x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 mb-4 mb-md-0">
                    <div class="stat-card p-4">
                        <i class="fas fa-users fa-3x text-primary mb-3"></i>
                        <h3>10,000+</h3>
                        <p class="text-muted">Active Users</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4 mb-md-0">
                    <div class="stat-card p-4">
                        <i class="fas fa-hand-holding-usd fa-3x text-success mb-3"></i>
                        <h3>$5M+</h3>
                        <p class="text-muted">Total Deposits</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4 mb-md-0">
                    <div class="stat-card p-4">
                        <i class="fas fa-wallet fa-3x text-warning mb-3"></i>
                        <h3>$2M+</h3>
                        <p class="text-muted">Withdrawals Paid</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-4">
                        <i class="fas fa-shield-alt fa-3x text-info mb-3"></i>
                        <h3>100%</h3>
                        <p class="text-muted">Secure Platform</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Investment Plans Section -->
    <section id="plans" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Investment Plans</h2>
                <p class="text-muted">Choose the perfect plan for your investment goals</p>
            </div>
            <div class="row" id="plansContainer">
                <!-- Plans will be loaded dynamically -->
                <div class="col-12 text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Why Choose Us</h2>
                <p class="text-muted">Platform features that set us apart</p>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="feature-card text-center p-4 bg-white rounded shadow-sm h-100">
                        <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                        <h4>Secure System</h4>
                        <p class="text-muted">Your funds are protected with advanced security measures and manual verification.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card text-center p-4 bg-white rounded shadow-sm h-100">
                        <i class="fas fa-bolt fa-3x text-warning mb-3"></i>
                        <h4>Fast Withdrawals</h4>
                        <p class="text-muted">Quick processing of withdrawal requests by our dedicated team.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card text-center p-4 bg-white rounded shadow-sm h-100">
                        <i class="fas fa-headset fa-3x text-success mb-3"></i>
                        <h4>24/7 Support</h4>
                        <p class="text-muted">Round-the-clock customer support to assist you anytime.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card text-center p-4 bg-white rounded shadow-sm h-100">
                        <i class="fas fa-chart-pie fa-3x text-info mb-3"></i>
                        <h4>Daily Profits</h4>
                        <p class="text-muted">Earn daily returns on your investments with our profit system.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card text-center p-4 bg-white rounded shadow-sm h-100">
                        <i class="fas fa-mobile-alt fa-3x text-danger mb-3"></i>
                        <h4>Mobile Friendly</h4>
                        <p class="text-muted">Access your account from any device, anywhere, anytime.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card text-center p-4 bg-white rounded shadow-sm h-100">
                        <i class="fas fa-gift fa-3x text-primary mb-3"></i>
                        <h4>Referral Bonus</h4>
                        <p class="text-muted">Earn commissions by referring friends to our platform.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Deposit Methods Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Supported Cryptocurrencies</h2>
                <p class="text-muted">Deposit using your preferred cryptocurrency</p>
            </div>
            <div class="row text-center">
                <div class="col-6 col-md-3 mb-4">
                    <div class="crypto-card p-3">
                        <i class="fab fa-bitcoin fa-4x text-warning mb-2"></i>
                        <h5>Bitcoin</h5>
                        <p class="text-muted small">BTC</p>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-4">
                    <div class="crypto-card p-3">
                        <i class="fab fa-ethereum fa-4x text-primary mb-2"></i>
                        <h5>Ethereum</h5>
                        <p class="text-muted small">ETH</p>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-4">
                    <div class="crypto-card p-3">
                        <i class="fas fa-dollar-sign fa-4x text-success mb-2"></i>
                        <h5>USDT TRC20</h5>
                        <p class="text-muted small">Tether</p>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-4">
                    <div class="crypto-card p-3">
                        <i class="fas fa-dollar-sign fa-4x text-info mb-2"></i>
                        <h5>USDT BEP20</h5>
                        <p class="text-muted small">BSC Chain</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Frequently Asked Questions</h2>
                <p class="text-muted">Find answers to common questions</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    How do I start investing?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Simply register an account, make a deposit, choose an investment plan, and start earning daily profits!
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    How long does deposit approval take?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Deposits are manually verified by our team and typically approved within 1-6 hours depending on network confirmations.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    When can I withdraw my profits?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    You can request withdrawals at any time. Withdrawal requests are processed within 24 hours by our team.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Is there a referral program?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes! Earn 5% commission on your referrals' deposits and 2% on their investments.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    What is the minimum deposit?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    The minimum deposit varies by cryptocurrency. Check the deposit page for specific minimum amounts for each wallet.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5><i class="fas fa-coins"></i> Crypto Invest Pro</h5>
                    <p class="text-muted">Your trusted platform for cryptocurrency investments. Secure, reliable, and profitable.</p>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-telegram fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-muted">Home</a></li>
                        <li><a href="login.php" class="text-muted">Login</a></li>
                        <li><a href="register.php" class="text-muted">Register</a></li>
                        <li><a href="#plans" class="text-muted">Investment Plans</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <ul class="list-unstyled text-muted">
                        <li><i class="fas fa-envelope me-2"></i> support@cryptoinvest.com</li>
                        <li><i class="fas fa-clock me-2"></i> 24/7 Support Available</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 bg-secondary">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="text-muted mb-0">&copy; 2024 Crypto Invest Pro. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="text-muted me-3">Terms & Conditions</a>
                    <a href="#" class="text-muted">Privacy Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>

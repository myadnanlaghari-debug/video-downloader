// Crypto Investment Platform - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Load investment plans on homepage
    loadInvestmentPlans();
    
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize copy buttons
    initializeCopyButtons();
});

// Load investment plans from API
async function loadInvestmentPlans() {
    const plansContainer = document.getElementById('plansContainer');
    if (!plansContainer) return;
    
    try {
        const response = await fetch('api/get_plans.php');
        const data = await response.json();
        
        if (data.success && data.plans.length > 0) {
            plansContainer.innerHTML = '';
            data.plans.forEach(plan => {
                const planHTML = createPlanCard(plan);
                plansContainer.innerHTML += planHTML;
            });
        } else {
            plansContainer.innerHTML = `
                <div class="col-12 text-center">
                    <p class="text-muted">No investment plans available at the moment.</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading plans:', error);
        plansContainer.innerHTML = `
            <div class="col-12 text-center">
                <p class="text-danger">Failed to load investment plans. Please try again later.</p>
            </div>
        `;
    }
}

// Create plan card HTML
function createPlanCard(plan) {
    const profitClass = getProfitClass(plan.profit_percentage);
    return `
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card plan-card h-100 ${profitClass}">
                <div class="card-header text-white text-center">
                    <h4 class="mb-0">${escapeHtml(plan.plan_name)}</h4>
                </div>
                <div class="card-body text-center">
                    <div class="plan-price text-primary mb-3">
                        ${plan.profit_percentage}% Daily
                    </div>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-2"><i class="fas fa-calendar-alt me-2"></i>${plan.duration_days} Days</li>
                        <li class="mb-2"><i class="fas fa-dollar-sign me-2"></i>Min: ${formatAmount(plan.min_amount)}</li>
                        <li class="mb-2"><i class="fas fa-wallet me-2"></i>Max: ${formatAmount(plan.max_amount)}</li>
                        <li class="mb-2"><i class="fas fa-chart-line me-2"></i>Total: ${(plan.profit_percentage * plan.duration_days).toFixed(0)}%</li>
                    </ul>
                    ${isLoggedIn() ? 
                        `<a href="user/invest.php?plan=${plan.id}" class="btn btn-primary w-100">Invest Now</a>` :
                        `<a href="register.php" class="btn btn-primary w-100">Get Started</a>`
                    }
                </div>
            </div>
        </div>
    `;
}

// Get profit class based on percentage
function getProfitClass(percentage) {
    if (percentage >= 7) return 'border-warning';
    if (percentage >= 5) return 'border-success';
    if (percentage >= 3) return 'border-info';
    return '';
}

// Format amount display
function formatAmount(amount) {
    return parseFloat(amount).toFixed(2) + ' USDT';
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Check if user is logged in (from body class or data attribute)
function isLoggedIn() {
    return document.body.classList.contains('logged-in') || 
           document.body.dataset.loggedIn === 'true';
}

// Initialize Bootstrap tooltips
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Initialize copy buttons
function initializeCopyButtons() {
    document.querySelectorAll('.copy-btn').forEach(button => {
        button.addEventListener('click', function() {
            const textToCopy = this.dataset.copyText;
            copyToClipboard(textToCopy);
            
            // Show feedback
            const originalIcon = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check"></i>';
            setTimeout(() => {
                this.innerHTML = originalIcon;
            }, 2000);
        });
    });
}

// Copy text to clipboard
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        showToast('Copied to clipboard!', 'success');
    } catch (err) {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showToast('Copied to clipboard!', 'success');
    }
}

// Show toast notification
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show`;
    toast.role = 'alert';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    toastContainer.appendChild(toast);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

// Create toast container if it doesn't exist
function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    document.body.appendChild(container);
    return container;
}

// Form validation helper
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const inputs = form.querySelectorAll('[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Password strength checker
function checkPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;
    
    return strength;
}

// Update password strength indicator
function updatePasswordStrength(password, indicatorId) {
    const indicator = document.getElementById(indicatorId);
    if (!indicator) return;
    
    const strength = checkPasswordStrength(password);
    const colors = ['danger', 'danger', 'warning', 'info', 'success', 'success'];
    const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
    
    indicator.className = `progress-bar bg-${colors[strength]}`;
    indicator.style.width = `${(strength / 5) * 100}%`;
    indicator.textContent = labels[strength];
}

// Confirm action dialog
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Auto-dismiss alerts
function autoDismissAlerts(timeout = 5000) {
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, timeout);
    });
}

// Initialize on page load
window.addEventListener('load', function() {
    autoDismissAlerts();
});

// AJAX helper function
async function ajaxRequest(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(url, options);
        return await response.json();
    } catch (error) {
        console.error('AJAX Error:', error);
        throw error;
    }
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Number formatter
const numberFormatter = new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
});

function formatNumber(num) {
    return numberFormatter.format(num);
}

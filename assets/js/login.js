// Login Form Handler
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const loadingOverlay = document.getElementById('loadingOverlay');

    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Show loading overlay
            if (loadingOverlay) {
                loadingOverlay.style.display = 'flex';
            }
            
            const formData = new FormData(loginForm);
            
            try {
                const response = await fetch('../../backend/login.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Show success message
                    showMessage('success', data.message);
                    
                    // Redirect to dashboard after a short delay
                    setTimeout(() => {
                        window.location.href = data.data.redirect_url;
                    }, 1000);
                } else {
                    // Hide loading overlay
                    if (loadingOverlay) {
                        loadingOverlay.style.display = 'none';
                    }
                    
                    // Show error message
                    showMessage('error', data.message);
                    
                    // Handle specific status codes
                    if (data.data && data.data.status === 'pending') {
                        // Show pending approval message
                        showPendingApprovalMessage();
                    }
                }
            } catch (error) {
                console.error('Login error:', error);
                
                // Hide loading overlay
                if (loadingOverlay) {
                    loadingOverlay.style.display = 'none';
                }
                
                showMessage('error', 'An error occurred. Please try again.');
            }
        });
    }
});

// Toggle password visibility
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('ri-eye-line');
        toggleIcon.classList.add('ri-eye-off-line');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('ri-eye-off-line');
        toggleIcon.classList.add('ri-eye-line');
    }
}

// Show message function
function showMessage(type, message) {
    // Remove any existing messages
    const existingMessage = document.querySelector('.message-alert');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    // Create message element
    const messageDiv = document.createElement('div');
    messageDiv.className = `message-alert message-${type}`;
    messageDiv.innerHTML = `
        <i class="ri-${type === 'success' ? 'checkbox-circle' : 'error-warning'}-line"></i>
        <span>${message}</span>
    `;
    
    // Add to form
    const formContainer = document.querySelector('.login-form-container');
    if (formContainer) {
        formContainer.insertBefore(messageDiv, formContainer.firstChild);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            messageDiv.remove();
        }, 5000);
    }
}

// Show pending approval message
function showPendingApprovalMessage() {
    const messageDiv = document.querySelector('.message-alert');
    if (messageDiv) {
        messageDiv.innerHTML = `
            <i class="ri-time-line"></i>
            <div>
                <strong>Account Pending Approval</strong>
                <p>Your registration is under review. You will receive a notification once your account is approved.</p>
            </div>
        `;
        messageDiv.className = 'message-alert message-info';
    }
}

// Add CSS for messages
const style = document.createElement('style');
style.textContent = `
    .message-alert {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        animation: slideDown 0.3s ease-out;
    }
    
    .message-alert i {
        font-size: 24px;
        flex-shrink: 0;
    }
    
    .message-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .message-error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .message-info {
        background-color: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);

// Register Form Handler
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const profileImageInput = document.getElementById('profile_image');
    const fileNameDisplay = document.getElementById('fileName');

    // Password strength checker
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
        });
    }

    // Confirm password validation
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            validatePasswordMatch();
        });
    }

    // File upload handler
    if (profileImageInput) {
        profileImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Check file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    showMessage('error', 'File size must be less than 5MB');
                    this.value = '';
                    fileNameDisplay.textContent = '';
                    return;
                }
                
                // Check file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    showMessage('error', 'Only JPG, PNG, and GIF files are allowed');
                    this.value = '';
                    fileNameDisplay.textContent = '';
                    return;
                }
                
                fileNameDisplay.textContent = file.name;
            } else {
                fileNameDisplay.textContent = '';
            }
        });
    }

    // Form submission
    if (registerForm) {
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Validate form
            if (!validateForm()) {
                return;
            }
            
            // Show loading overlay
            if (loadingOverlay) {
                loadingOverlay.style.display = 'flex';
            }
            
            const formData = new FormData(registerForm);
            
            try {
                const response = await fetch('../../backend/signup.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Show success message
                    showSuccessModal(data.message);
                    
                    // Reset form
                    registerForm.reset();
                    if (fileNameDisplay) {
                        fileNameDisplay.textContent = '';
                    }
                    
                    // Redirect to login after 3 seconds
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 3000);
                } else {
                    // Hide loading overlay
                    if (loadingOverlay) {
                        loadingOverlay.style.display = 'none';
                    }
                    
                    // Show error message
                    showMessage('error', data.message);
                }
            } catch (error) {
                console.error('Registration error:', error);
                
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
function togglePassword(inputId) {
    const passwordInput = document.getElementById(inputId);
    const toggleIcon = document.getElementById(inputId === 'password' ? 'toggleIcon1' : 'toggleIcon2');
    
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

// Check password strength
function checkPasswordStrength(password) {
    const strengthIndicator = document.getElementById('passwordStrength');
    if (!strengthIndicator) return;
    
    let strength = 0;
    let feedback = '';
    let color = '';
    
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    if (password.length === 0) {
        strengthIndicator.style.display = 'none';
        return;
    }
    
    strengthIndicator.style.display = 'block';
    
    if (strength <= 2) {
        feedback = 'Weak password';
        color = '#dc3545';
    } else if (strength <= 4) {
        feedback = 'Medium password';
        color = '#ffc107';
    } else {
        feedback = 'Strong password';
        color = '#28a745';
    }
    
    strengthIndicator.innerHTML = `
        <div style="display: flex; align-items: center; gap: 8px; margin-top: 5px;">
            <div style="flex: 1; height: 4px; background: #e0e0e0; border-radius: 2px; overflow: hidden;">
                <div style="width: ${(strength / 6) * 100}%; height: 100%; background: ${color}; transition: all 0.3s;"></div>
            </div>
            <span style="font-size: 12px; color: ${color}; font-weight: 500;">${feedback}</span>
        </div>
    `;
}

// Validate password match
function validatePasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    if (confirmPassword.length > 0) {
        if (password === confirmPassword) {
            confirmPasswordInput.style.borderColor = '#28a745';
        } else {
            confirmPasswordInput.style.borderColor = '#dc3545';
        }
    } else {
        confirmPasswordInput.style.borderColor = '';
    }
}

// Validate form
function validateForm() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const terms = document.querySelector('input[name="terms"]').checked;
    
    if (password !== confirmPassword) {
        showMessage('error', 'Passwords do not match');
        return false;
    }
    
    if (!terms) {
        showMessage('error', 'You must agree to the Terms & Conditions and Privacy Policy');
        return false;
    }
    
    return true;
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
    const formContainer = document.querySelector('.register-form-container');
    if (formContainer) {
        formContainer.insertBefore(messageDiv, formContainer.firstChild);
        
        // Scroll to top
        formContainer.scrollTop = 0;
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            messageDiv.remove();
        }, 5000);
    }
}

// Show success modal
function showSuccessModal(message) {
    const loadingOverlay = document.getElementById('loadingOverlay');
    if (loadingOverlay) {
        loadingOverlay.innerHTML = `
            <div class="success-modal">
                <div class="success-icon">
                    <i class="ri-checkbox-circle-fill"></i>
                </div>
                <h2>Registration Successful!</h2>
                <p>${message}</p>
                <p style="margin-top: 15px; font-size: 14px; opacity: 0.8;">Redirecting to login page...</p>
            </div>
        `;
    }
}

// Add CSS for messages and success modal
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
    
    .success-modal {
        background: white;
        padding: 40px;
        border-radius: 16px;
        text-align: center;
        max-width: 400px;
        animation: scaleIn 0.3s ease-out;
    }
    
    .success-icon {
        font-size: 64px;
        color: #28a745;
        margin-bottom: 20px;
    }
    
    .success-modal h2 {
        color: #333;
        margin-bottom: 15px;
    }
    
    .success-modal p {
        color: #666;
        line-height: 1.6;
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
    
    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
`;
document.head.appendChild(style);

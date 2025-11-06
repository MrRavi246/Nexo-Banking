<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Login - Nexo Banking</title>
    <link rel="stylesheet" href="../assets/style/nav.css">
    <link rel="stylesheet" href="../assets/style/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
  </head>
  <body>
    <div class="admin-login-wrap">
      <div class="admin-login-card">
        <h2>Admin Panel</h2>
        <div id="messageContainer"></div>

        <form id="adminLoginForm" method="post">
          <label>Username or Email
            <input type="text" name="username" id="username" required autofocus>
          </label>
          <label>Password
            <input type="password" name="password" id="password" required>
          </label>
          <label class="remember-checkbox">
            <input type="checkbox" name="remember">
            <span>Remember me</span>
          </label>
          <div class="admin-actions">
            <button type="submit" class="btn-primary">Sign in</button>
          </div>
        </form>
        <p class="small">Default admin: <code>admin</code> / <code>Admin@123</code></p>
      </div>
    </div>
    
    <div class="loading-overlay" id="loadingOverlay" style="display: none;">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Signing you in...</p>
        </div>
    </div>

    <script>
    document.getElementById('adminLoginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const loadingOverlay = document.getElementById('loadingOverlay');
        loadingOverlay.style.display = 'flex';
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('../backend/admin_login.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showMessage('success', data.message);
                setTimeout(() => {
                    window.location.href = data.data.redirect_url;
                }, 1000);
            } else {
                loadingOverlay.style.display = 'none';
                showMessage('error', data.message);
            }
        } catch (error) {
            console.error('Login error:', error);
            loadingOverlay.style.display = 'none';
            showMessage('error', 'An error occurred. Please try again.');
        }
    });
    
    function showMessage(type, message) {
        const container = document.getElementById('messageContainer');
        container.innerHTML = `
            <div class="admin-${type}">
                <i class="ri-${type === 'success' ? 'checkbox-circle' : 'error-warning'}-line"></i>
                ${message}
            </div>
        `;
        
        setTimeout(() => {
            container.innerHTML = '';
        }, 5000);
    }
    </script>
    
    <style>
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    
    .loading-spinner {
        text-align: center;
        color: white;
    }
    
    .spinner {
        width: 50px;
        height: 50px;
        border: 4px solid rgba(255, 255, 255, 0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 15px;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    .admin-success {
        background: #d4edda;
        color: #155724;
        padding: 12px;
        border-radius: 4px;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .admin-error {
        background: #f8d7da;
        color: #721c24;
        padding: 12px;
        border-radius: 4px;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .remember-checkbox {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 10px 0;
    }
    
    .remember-checkbox input[type="checkbox"] {
        width: auto;
        margin: 0;
    }
    </style>
  </body>
</html>


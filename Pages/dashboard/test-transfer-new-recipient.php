<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Money - New Recipient Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .test-section {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .status {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background: #0056b3;
        }
        pre {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h2>🧪 Transfer Money - New Recipient Test</h2>
        
        <div class="test-section">
            <h3>Test Scenario: Transfer to New Recipient</h3>
            <p>This will test sending money to a new recipient (not saved in contacts).</p>
            
            <button onclick="testNewRecipient()">▶️ Run Test</button>
            <button onclick="testInvalidNewRecipient()">❌ Test Invalid Data</button>
            <button onclick="checkSessionStatus()">🔐 Check Login Status</button>
        </div>
        
        <div id="results"></div>
    </div>

    <script>
        async function checkSessionStatus() {
            const results = document.getElementById('results');
            results.innerHTML = '<div class="status info">Checking login status...</div>';
            
            try {
                const response = await fetch('../../backend/get_user_data.php', {
                    method: 'GET',
                    credentials: 'include'
                });
                
                const result = await response.json();
                
                if (result.success) {
                    results.innerHTML = `
                        <div class="status success">
                            ✅ Logged in successfully!
                            <br>User: ${result.data.user.full_name}
                            <br>Email: ${result.data.user.email}
                        </div>
                    `;
                } else {
                    results.innerHTML = `
                        <div class="status error">
                            ❌ Not logged in: ${result.message}
                            <br><a href="../auth/login.php">Go to Login</a>
                        </div>
                    `;
                }
            } catch (error) {
                results.innerHTML = `
                    <div class="status error">
                        ❌ Error checking login: ${error.message}
                    </div>
                `;
            }
        }

        async function testNewRecipient() {
            const results = document.getElementById('results');
            results.innerHTML = '<div class="status info">Loading your accounts...</div>';
            
            // First, fetch user's accounts to get a valid account_id
            try {
                const accountResponse = await fetch('../../backend/get_transfer_data.php', {
                    method: 'GET',
                    credentials: 'include'
                });
                
                const accountResult = await accountResponse.json();
                
                if (!accountResult.success || !accountResult.data.accounts || accountResult.data.accounts.length === 0) {
                    results.innerHTML = `
                        <div class="status error">
                            ❌ No accounts found. Please create an account first.
                        </div>
                    `;
                    return;
                }
                
                // Use the first available account
                const firstAccount = accountResult.data.accounts[0];
                const accountId = firstAccount.account_id;
                const accountBalance = firstAccount.balance;
                
                results.innerHTML = `
                    <div class="status info">
                        Using account: ${firstAccount.account_type} (**** ${firstAccount.account_number.slice(-4)})
                        <br>Balance: $${accountBalance}
                        <br>Testing new recipient transfer...
                    </div>
                `;
                
                const transferData = {
                    fromAccount: accountId.toString(),
                    amount: 50.00,
                    transferMethod: "external",
                    recipientType: "new",
                    transferDate: new Date().toISOString().split('T')[0],
                    memo: "Test transfer to new recipient",
                    securityMethod: "sms",
                    // New recipient details
                    recipientName: "Test Recipient",
                    recipientEmail: "test.recipient@example.com",
                    bankName: "Test Bank",
                    accountNumber: "9999888877776666",
                    routingNumber: "123456789",
                    accountType: "checking"
                };
                
                console.log('Sending test data:', transferData);
                
                const response = await fetch('../../backend/process_transfer.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'include',
                    body: JSON.stringify(transferData)
                });
                
                const result = await response.json();
                console.log('Transfer result:', result);
                
                if (result.success) {
                    results.innerHTML = `
                        <div class="status success">
                            ✅ Transfer Successful!
                            <pre>${JSON.stringify(result, null, 2)}</pre>
                        </div>
                    `;
                } else {
                    results.innerHTML = `
                        <div class="status error">
                            ❌ Transfer Failed: ${result.message}
                            <pre>${JSON.stringify(result, null, 2)}</pre>
                        </div>
                    `;
                }
            } catch (error) {
                results.innerHTML = `
                    <div class="status error">
                        ❌ Error: ${error.message}
                        <br>Check console for details.
                    </div>
                `;
                console.error('Error:', error);
            }
        }

        async function testInvalidNewRecipient() {
            const results = document.getElementById('results');
            results.innerHTML = '<div class="status info">Testing validation with missing data...</div>';
            
            // Get a valid account first
            try {
                const accountResponse = await fetch('../../backend/get_transfer_data.php', {
                    method: 'GET',
                    credentials: 'include'
                });
                
                const accountResult = await accountResponse.json();
                
                if (!accountResult.success || !accountResult.data.accounts || accountResult.data.accounts.length === 0) {
                    results.innerHTML = `
                        <div class="status error">
                            ❌ No accounts found.
                        </div>
                    `;
                    return;
                }
                
                const firstAccount = accountResult.data.accounts[0];
                
                const transferData = {
                    fromAccount: firstAccount.account_id.toString(),
                    amount: 50.00,
                    transferMethod: "external",
                    recipientType: "new",
                    transferDate: new Date().toISOString().split('T')[0],
                    // Missing recipient details - should fail validation
                    recipientName: "",
                    accountNumber: ""
                };
                
                const response = await fetch('../../backend/process_transfer.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'include',
                    body: JSON.stringify(transferData)
                });
                
                const result = await response.json();
                
                if (!result.success) {
                    results.innerHTML = `
                        <div class="status success">
                            ✅ Validation working correctly!
                            <br>Error message: ${result.message}
                            <pre>${JSON.stringify(result, null, 2)}</pre>
                        </div>
                    `;
                } else {
                    results.innerHTML = `
                        <div class="status error">
                            ⚠️ Validation should have failed but didn't!
                            <pre>${JSON.stringify(result, null, 2)}</pre>
                        </div>
                    `;
                }
            } catch (error) {
                results.innerHTML = `
                    <div class="status error">
                        ❌ Error: ${error.message}
                    </div>
                `;
            }
        }
        
        // Auto-check login status on load
        window.onload = checkSessionStatus;
    </script>
</body>
</html>

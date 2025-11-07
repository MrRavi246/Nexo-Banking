<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard API Test</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        h1 {
            color: #667eea;
            margin-bottom: 10px;
        }
        .test-section {
            margin-bottom: 30px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }
        .test-header {
            background: #f5f5f5;
            padding: 15px;
            font-weight: bold;
            border-bottom: 1px solid #e0e0e0;
        }
        .test-content {
            padding: 15px;
        }
        pre {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            border: 1px solid #e0e0e0;
        }
        .status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            margin-left: 10px;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
        }
        button {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }
        button:hover {
            opacity: 0.9;
        }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Dashboard API Test</h1>
        <p style="color: #666;">Test if the dashboard API is returning user data correctly</p>

        <div class="info-box">
            <strong>Purpose:</strong> This page tests the <code>backend/get_user_data.php</code> API endpoint to verify it's returning user data correctly.
        </div>

        <button onclick="testAPI()">🔄 Test API Now</button>

        <div id="results"></div>
    </div>

    <script>
        async function testAPI() {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = '<p>Testing API... Please wait...</p>';

            try {
                const response = await fetch('../../backend/get_user_data.php', {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                let html = '';

                // Test 1: Response Status
                html += `
                    <div class="test-section">
                        <div class="test-header">
                            Test 1: API Response
                            <span class="status ${response.ok ? 'success' : 'error'}">
                                ${response.ok ? '✓ SUCCESS' : '✗ FAILED'}
                            </span>
                        </div>
                        <div class="test-content">
                            <p><strong>HTTP Status:</strong> ${response.status} ${response.statusText}</p>
                            <p><strong>Success:</strong> ${data.success ? 'Yes' : 'No'}</p>
                            <p><strong>Message:</strong> ${data.message}</p>
                        </div>
                    </div>
                `;

                if (data.success && data.data) {
                    // Test 2: User Data
                    html += `
                        <div class="test-section">
                            <div class="test-header">
                                Test 2: User Profile Data
                                <span class="status ${data.data.user ? 'success' : 'error'}">
                                    ${data.data.user ? '✓ PRESENT' : '✗ MISSING'}
                                </span>
                            </div>
                            <div class="test-content">
                                <p><strong>Full Name:</strong> ${data.data.user.full_name || 'N/A'}</p>
                                <p><strong>First Name:</strong> ${data.data.user.first_name || 'N/A'}</p>
                                <p><strong>Last Name:</strong> ${data.data.user.last_name || 'N/A'}</p>
                                <p><strong>Username:</strong> ${data.data.user.username || 'N/A'}</p>
                                <p><strong>Email:</strong> ${data.data.user.email || 'N/A'}</p>
                                <p><strong>Member Type:</strong> ${data.data.user.member_type_display || 'N/A'}</p>
                                <p><strong>Profile Image:</strong> ${data.data.user.profile_image || 'N/A'}</p>
                            </div>
                        </div>
                    `;

                    // Test 3: Account Data
                    html += `
                        <div class="test-section">
                            <div class="test-header">
                                Test 3: Account Balances
                                <span class="status ${data.data.accounts ? 'success' : 'error'}">
                                    ${data.data.accounts ? '✓ PRESENT' : '✗ MISSING'}
                                </span>
                            </div>
                            <div class="test-content">
                                <p><strong>Total Balance:</strong> $${data.data.accounts.total_balance || '0.00'}</p>
                                <p><strong>Checking Balance:</strong> $${data.data.accounts.checking.balance || '0.00'}</p>
                                <p><strong>Savings Balance:</strong> $${data.data.accounts.savings.balance || '0.00'}</p>
                                <p><strong>Credit Balance:</strong> $${data.data.accounts.credit.balance || '0.00'}</p>
                            </div>
                        </div>
                    `;

                    // Test 4: Transactions
                    const txCount = data.data.transactions ? data.data.transactions.length : 0;
                    html += `
                        <div class="test-section">
                            <div class="test-header">
                                Test 4: Recent Transactions
                                <span class="status ${txCount > 0 ? 'success' : 'error'}">
                                    ${txCount} transactions
                                </span>
                            </div>
                            <div class="test-content">
                                <p><strong>Transaction Count:</strong> ${txCount}</p>
                                ${txCount === 0 ? '<p style="color: orange;">⚠️ No transactions found. Run the test data script to add sample transactions.</p>' : ''}
                            </div>
                        </div>
                    `;

                    // Test 5: Savings Goals
                    const goalsCount = data.data.savings_goals ? data.data.savings_goals.length : 0;
                    html += `
                        <div class="test-section">
                            <div class="test-header">
                                Test 5: Savings Goals
                                <span class="status ${goalsCount > 0 ? 'success' : 'error'}">
                                    ${goalsCount} goals
                                </span>
                            </div>
                            <div class="test-content">
                                <p><strong>Goals Count:</strong> ${goalsCount}</p>
                                ${goalsCount === 0 ? '<p style="color: orange;">⚠️ No savings goals found. Run the test data script to add sample goals.</p>' : ''}
                            </div>
                        </div>
                    `;

                    // Test 6: Credit Score
                    html += `
                        <div class="test-section">
                            <div class="test-header">
                                Test 6: Credit Score
                                <span class="status ${data.data.credit_score ? 'success' : 'error'}">
                                    ${data.data.credit_score ? '✓ PRESENT' : '✗ MISSING'}
                                </span>
                            </div>
                            <div class="test-content">
                                <p><strong>Score:</strong> ${data.data.credit_score ? data.data.credit_score.score : 'N/A'}</p>
                                <p><strong>Status:</strong> ${data.data.credit_score ? data.data.credit_score.status : 'N/A'}</p>
                            </div>
                        </div>
                    `;

                    // Full JSON Response
                    html += `
                        <div class="test-section">
                            <div class="test-header">
                                Full JSON Response
                            </div>
                            <div class="test-content">
                                <pre>${JSON.stringify(data, null, 2)}</pre>
                            </div>
                        </div>
                    `;

                } else {
                    html += `
                        <div class="test-section">
                            <div class="test-header">
                                Error Details
                                <span class="status error">✗ ERROR</span>
                            </div>
                            <div class="test-content">
                                <p style="color: red;"><strong>Error Message:</strong> ${data.message || 'Unknown error'}</p>
                                <pre>${JSON.stringify(data, null, 2)}</pre>
                            </div>
                        </div>
                    `;
                }

                resultsDiv.innerHTML = html;

            } catch (error) {
                resultsDiv.innerHTML = `
                    <div class="test-section">
                        <div class="test-header">
                            Fatal Error
                            <span class="status error">✗ FAILED</span>
                        </div>
                        <div class="test-content">
                            <p style="color: red;"><strong>Error:</strong> ${error.message}</p>
                            <p>Make sure:</p>
                            <ul>
                                <li>You are logged in</li>
                                <li>XAMPP is running</li>
                                <li>The file <code>backend/get_user_data.php</code> exists</li>
                            </ul>
                        </div>
                    </div>
                `;
            }
        }

        // Auto-run test on page load
        window.addEventListener('DOMContentLoaded', function() {
            setTimeout(testAPI, 500);
        });
    </script>
</body>
</html>

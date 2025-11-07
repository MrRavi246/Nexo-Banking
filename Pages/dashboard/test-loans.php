<?php
require_once __DIR__ . '/../../backend/config.php';
require_once __DIR__ . '/../../backend/functions.php';

// Simple test page to manually exercise loan endpoints. Requires an active session (logged in user).
if (!isLoggedIn()) {
    echo "<p>Please log in first to test loans endpoints.</p>";
    exit();
}

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Test Loans Endpoints</title>
    <style>body{font-family:Arial;padding:1rem;} input,select{margin:0.25rem 0;padding:0.5rem;width:100%;max-width:360px;} button{padding:0.5rem 1rem;margin-top:0.5rem}</style>
    <script>
    async function callList() {
        const res = await fetch('/Nexo-Banking/backend/get_loans.php', {credentials: 'same-origin'});
        const json = await res.json();
        document.getElementById('output').textContent = JSON.stringify(json, null, 2);
    }
    async function callApply() {
        const payload = {
            loan_type: document.getElementById('loan_type').value,
            principal: parseFloat(document.getElementById('principal').value),
            term_months: parseInt(document.getElementById('term_months').value),
            purpose: document.getElementById('purpose').value
        };
        const res = await fetch('/Nexo-Banking/backend/apply_loan.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(payload)
        });
        const json = await res.json();
        document.getElementById('output').textContent = JSON.stringify(json, null, 2);
    }
    async function callPay() {
        const payload = {
            loan_id: parseInt(document.getElementById('pay_loan_id').value),
            amount: parseFloat(document.getElementById('pay_amount').value),
            account_id: parseInt(document.getElementById('pay_account_id').value)
        };
        const res = await fetch('/Nexo-Banking/backend/pay_loan.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(payload)
        });
        const json = await res.json();
        document.getElementById('output').textContent = JSON.stringify(json, null, 2);
    }
    </script>
</head>
<body>
    <h2>Loans API Test</h2>
    <div>
        <button onclick="callList()">List My Loans</button>
    </div>

    <hr>
    <h3>Apply for Loan</h3>
    <label>Type</label>
    <select id="loan_type">
        <option value="personal">Personal</option>
        <option value="auto">Auto</option>
        <option value="home">Home</option>
        <option value="business">Business</option>
    </select>
    <label>Principal</label>
    <input id="principal" type="number" value="5000">
    <label>Term (months)</label>
    <input id="term_months" type="number" value="24">
    <label>Purpose</label>
    <input id="purpose" type="text" value="Test application">
    <div><button onclick="callApply()">Submit Application</button></div>

    <hr>
    <h3>Pay Loan</h3>
    <label>Loan ID</label>
    <input id="pay_loan_id" type="number" value="">
    <label>Amount</label>
    <input id="pay_amount" type="number" value="100">
    <label>From Account ID</label>
    <input id="pay_account_id" type="number" value="1">
    <div><button onclick="callPay()">Pay</button></div>

    <hr>
    <h3>Output</h3>
    <pre id="output" style="background:#f5f5f5;padding:1rem;border-radius:6px;">No output yet</pre>
</body>
</html>

<?php
echo "Starting...<br>";
flush();

try {
    echo "Connecting to database...<br>";
    flush();
    
    $conn = new PDO("mysql:host=localhost;dbname=nexo_banking", 'root', '');
    
    echo "Connected!<br>";
    flush();
    
    $result = $conn->query("SELECT COUNT(*) as c FROM accounts");
    $row = $result->fetch();
    
    echo "Accounts found: " . $row['c'] . "<br>";
    echo "Success!";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

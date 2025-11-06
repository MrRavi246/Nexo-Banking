<?php
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method', null, 405);
}

try {
    $conn = getDBConnection();
    
    // Collect and sanitize input
    $firstName = sanitizeInput($_POST['first_name'] ?? '');
    $lastName = sanitizeInput($_POST['last_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phoneNumber = sanitizeInput($_POST['phone_number'] ?? '');
    $dateOfBirth = sanitizeInput($_POST['date_of_birth'] ?? '');
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $address = sanitizeInput($_POST['address'] ?? '');
    $memberType = sanitizeInput($_POST['member_type'] ?? 'basic');
    
    // Validation
    $errors = [];
    
    if (empty($firstName) || empty($lastName)) {
        $errors[] = "First name and last name are required";
    }
    
    if (empty($email) || !validateEmail($email)) {
        $errors[] = "Valid email address is required";
    }
    
    if (empty($username) || strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters long";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } else {
        $passwordErrors = validatePassword($password);
        if (!empty($passwordErrors)) {
            $errors = array_merge($errors, $passwordErrors);
        }
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($phoneNumber)) {
        $errors[] = "Phone number is required";
    }
    
    if (empty($dateOfBirth)) {
        $errors[] = "Date of birth is required";
    } else {
        // Check if user is at least 18 years old
        $dob = new DateTime($dateOfBirth);
        $today = new DateTime();
        $age = $today->diff($dob)->y;
        if ($age < 18) {
            $errors[] = "You must be at least 18 years old to register";
        }
    }
    
    if (empty($address)) {
        $errors[] = "Address is required";
    }
    
    if (!in_array($memberType, ['basic', 'premium', 'business'])) {
        $errors[] = "Invalid account type";
    }
    
    if (!empty($errors)) {
        sendResponse(false, implode('. ', $errors), null, 400);
    }
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        sendResponse(false, 'Email address is already registered', null, 409);
    }
    
    // Check if username already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) {
        sendResponse(false, 'Username is already taken', null, 409);
    }
    
    // Handle profile image upload
    $profileImage = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        try {
            $profileImage = handleFileUpload($_FILES['profile_image']);
        } catch (Exception $e) {
            sendResponse(false, $e->getMessage(), null, 400);
        }
    }
    
    // Hash password
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    
    // Begin transaction
    $conn->beginTransaction();
    
    try {
        // Insert user with pending status
        $stmt = $conn->prepare("
            INSERT INTO users 
            (username, email, password_hash, first_name, last_name, phone_number, 
             date_of_birth, address, profile_image, member_type, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        
        $stmt->execute([
            $username,
            $email,
            $passwordHash,
            $firstName,
            $lastName,
            $phoneNumber,
            $dateOfBirth,
            $address,
            $profileImage,
            $memberType
        ]);
        
        $userId = $conn->lastInsertId();
        
        // Generate account number for the user (will be activated after approval)
        $accountNumber = generateAccountNumber($conn);
        
        // Create a checking account (inactive until approved)
        $stmt = $conn->prepare("
            INSERT INTO accounts 
            (user_id, account_type, account_number, balance, status) 
            VALUES (?, 'checking', ?, 0.00, 'inactive')
        ");
        $stmt->execute([$userId, $accountNumber]);
        
        // Create a savings account (inactive until approved)
        $accountNumber2 = generateAccountNumber($conn);
        $stmt = $conn->prepare("
            INSERT INTO accounts 
            (user_id, account_type, account_number, balance, interest_rate, status) 
            VALUES (?, 'savings', ?, 0.00, 0.025, 'inactive')
        ");
        $stmt->execute([$userId, $accountNumber2]);
        
        // Log audit
        logAudit($conn, $userId, 'USER_REGISTRATION', 'users', $userId, null, [
            'username' => $username,
            'email' => $email,
            'member_type' => $memberType
        ]);
        
        // Commit transaction
        $conn->commit();
        
        sendResponse(true, 'Registration successful! Your account is pending approval. You will receive a notification once your account is approved.', [
            'user_id' => $userId,
            'status' => 'pending'
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Registration Error: " . $e->getMessage());
        
        // Delete uploaded file if registration fails
        if ($profileImage && file_exists(UPLOAD_DIR . basename($profileImage))) {
            unlink(UPLOAD_DIR . basename($profileImage));
        }
        
        sendResponse(false, 'Registration failed. Please try again.', null, 500);
    }
    
} catch (Exception $e) {
    error_log("Signup Error: " . $e->getMessage());
    sendResponse(false, 'An error occurred. Please try again later.', null, 500);
}
?>

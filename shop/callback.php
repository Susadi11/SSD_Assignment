<?php
// Initialize session with secure cookie parameters
include_once 'lib/Session.php';
Session::init();

// Security headers to prevent clickjacking attacks
header("X-Frame-Options: DENY");
header("Content-Security-Policy: frame-ancestors 'none'");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

include_once 'config/config.php';
include_once 'config/google_oauth.php';
include_once 'classess/Customer.php';

$cmr = new Customer();

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // Exchange code for access token
    $token_data = getGoogleAccessToken($code);
    
    if ($token_data && isset($token_data['access_token'])) {
        $access_token = $token_data['access_token'];
        
        // Get user info from Google
        $user_info = getGoogleUserInfo($access_token);
        
        if ($user_info) {
            $google_id = $user_info['id'];
            $email = $user_info['email'];
            $name = $user_info['name'];
            $picture = isset($user_info['picture']) ? $user_info['picture'] : '';
            
            // Check if user already exists
            $existing_user = $cmr->getCustomerByEmail($email);
            
            if ($existing_user) {
                // Update existing user with Google ID if not set
                if (empty($existing_user['google_id'])) {
                    $cmr->updateCustomerGoogleId($existing_user['id'], $google_id, $picture);
                }
                
                // Set session and redirect
                Session::set("cuslogin", true);
                Session::set("cmrId", $existing_user['id']);
                Session::set("cmrName", $existing_user['name']);
                header("Location: cart.php");
                exit();
            } else {
                // Create new user with Google OAuth data
                $customer_data = array(
                    'name' => $name,
                    'email' => $email,
                    'google_id' => $google_id,
                    'profile_picture' => $picture,
                    'oauth_provider' => 'google'
                );
                
                $result = $cmr->createGoogleCustomer($customer_data);
                
                if ($result) {
                    // Get the newly created customer
                    $new_customer = $cmr->getCustomerByEmail($email);
                    
                    // Set session and redirect
                    Session::set("cuslogin", true);
                    Session::set("cmrId", $new_customer['id']);
                    Session::set("cmrName", $new_customer['name']);
                    header("Location: cart.php");
                    exit();
                } else {
                    $error = "Failed to create account. Please try again.";
                }
            }
        } else {
            $error = "Failed to get user information from Google.";
        }
    } else {
        $error = "Failed to get access token from Google.";
    }
} else {
    $error = "Authorization code not received.";
}

// If we reach here, there was an error
?>
<!DOCTYPE html>
<html>
<head>
    <title>Google Sign-In Error</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .error { color: red; background: #ffe6e6; padding: 20px; border-radius: 5px; display: inline-block; }
        .back-link { margin-top: 20px; }
        .back-link a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="error">
        <h2>Google Sign-In Error</h2>
        <p><?php echo isset($error) ? $error : 'An unknown error occurred.'; ?></p>
        <div class="back-link">
            <a href="login.php">← Back to Login</a>
        </div>
    </div>
</body>
</html>

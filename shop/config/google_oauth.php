<?php
// Google OAuth Configuration (Updated for 2024 - Google Identity Services)
define('GOOGLE_CLIENT_ID', '1046815570922-tn4d2erbdneoebmfvgml5k4jof4n24ag.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'http://localhost/ssd%20assignment%20fixed/SSD_Assignment/shop/callback.php');
define('GOOGLE_AUTH_URL', 'https://accounts.google.com/o/oauth2/v2/auth');
define('GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token');
define('GOOGLE_USER_INFO_URL', 'https://www.googleapis.com/oauth2/v2/userinfo');

// Google OAuth Scopes
define('GOOGLE_SCOPES', 'email profile');

// Generate Google OAuth URL
function getGoogleAuthUrl() {
    $params = array(
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'scope' => GOOGLE_SCOPES,
        'response_type' => 'code',
        'access_type' => 'offline',
        'prompt' => 'consent'
    );
    
    return GOOGLE_AUTH_URL . '?' . http_build_query($params);
}

// Exchange authorization code for access token
function getGoogleAccessToken($code) {
    $data = array(
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code',
        'code' => $code
    );
    
    $options = array(
        'http' => array(
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        )
    );
    
    $context = stream_context_create($options);
    $result = file_get_contents(GOOGLE_TOKEN_URL, false, $context);
    
    if ($result === FALSE) {
        return false;
    }
    
    return json_decode($result, true);
}

// Get user info from Google
function getGoogleUserInfo($access_token) {
    $url = GOOGLE_USER_INFO_URL . '?access_token=' . $access_token;
    $result = file_get_contents($url);
    
    if ($result === FALSE) {
        return false;
    }
    
    return json_decode($result, true);
}
?>

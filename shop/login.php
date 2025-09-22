<?php 
include 'inc/header.php';
include 'config/google_oauth.php';

$login = Session::get("cuslogin");
if ($login === true) {
    header("Location: order.php");
    exit;
}

// Ensure CSRF token exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$custLogin = null;
$customerReg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(400);
        die("Invalid request.");
    }

    if (isset($_POST['login'])) {
        // Sanitize inputs
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $pass  = $_POST['pass'] ?? '';

        if (!$email || empty($pass)) {
            $custLogin = "<span class='error'>Invalid email or password.</span>";
        } else {
            $custLogin = $cmr->customerLogin([
                'email' => $email,
                'pass'  => $pass
            ]);
        }
    }

    if (isset($_POST['register'])) {
        // Sanitize & validate registration fields
        $data = [];
        $data['name']    = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
        $data['city']    = trim(filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING));
        $data['zip']     = filter_input(INPUT_POST, 'zip', FILTER_VALIDATE_INT);
        $data['email']   = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $data['address'] = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING));
        $data['country'] = trim(filter_input(INPUT_POST, 'country', FILTER_SANITIZE_STRING));
        $data['phone']   = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING));
        $data['pass']    = $_POST['pass'] ?? '';

        if (!$data['email'] || empty($data['pass']) || empty($data['name'])) {
            $customerReg = "<span class='error'>Please fill in required fields correctly.</span>";
        } else {
            $customerReg = $cmr->customerRegistration($data);
        }
    }
}
?> 

<div class="main">
  <div class="content">
    <div class="login_panel">
      <?php if ($custLogin) echo $custLogin; ?>
      <h3>Existing Customers</h3>
      <p>Sign in with the form below.</p>
      <form action="" method="post" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input name="email" placeholder="Email" type="email" required />
        <input name="pass" placeholder="Password" type="password" required />
        <div class="buttons"><div><button class="grey" name="login">Sign In</button></div></div>
      </form>
      <div class="google-signin-container" style="margin-top: 15px;">
        <a href="<?php echo htmlspecialchars(getGoogleAuthUrl()); ?>" class="google-signin-btn">
          <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google">
          Sign in with Google
        </a>
      </div>
    </div>

    <div class="register_account">
      <?php if ($customerReg) echo $customerReg; ?>
      <h3>Register New Account</h3>
      <form action="" method="post" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <table>
          <tbody>
            <tr>
              <td>
                <div><input type="text" name="name" placeholder="Name" required /></div>
                <div><input type="text" name="city" placeholder="City" /></div>
                <div><input type="text" name="zip" placeholder="Zip-Code" /></div>
                <div><input type="email" name="email" placeholder="Email" required /></div>
              </td>
              <td>
                <div><input type="text" name="address" placeholder="Address" /></div>
                <div><input type="text" name="country" placeholder="Country" /></div>
                <div><input type="text" name="phone" placeholder="Phone" /></div>
                <div><input type="password" name="pass" placeholder="Password" required /></div>
              </td>
            </tr> 
          </tbody>
        </table>
        <div class="search"><div><button class="grey" name="register">Create Account</button></div></div>
        <div class="clear"></div>
      </form>
    </div>
    <div class="clear"></div>
  </div>
</div>
<?php include 'inc/footer.php'; ?>
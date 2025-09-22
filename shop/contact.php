<?php 
include 'inc/header.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(400);
        die("Invalid request.");
    }

    // Sanitize and validate inputs
    $name    = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
    $email   = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $contact = trim(filter_input(INPUT_POST, 'contact', FILTER_SANITIZE_STRING));
    $message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING));

    if (empty($name)) {
        $error = "Name must not be empty!";
    } elseif (!$email) {
        $error = "Invalid Email Address!";
    } elseif (empty($contact)) {
        $error = "Contact field must not be empty!";
    } elseif (empty($message)) {
        $error = "Message field must not be empty!";
    } else {
        // Use prepared statement
        $sql = "INSERT INTO tbl_contact (name, email, contact, message) VALUES (?, ?, ?, ?)";
        $stmt = $db->link->prepare($sql);

        if ($stmt === false) {
            error_log("Prepare failed: " . $db->link->error);
            $error = "Something went wrong. Please try again later.";
        } else {
            $stmt->bind_param("ssss", $name, $email, $contact, $message);
            if ($stmt->execute()) {
                $msg = "Message Sent Successfully.";
            } else {
                error_log("Execute failed: " . $stmt->error);
                $error = "Message not sent!";
            }
            $stmt->close();
        }
    }
}
?>
<div class="main">
    <div class="content">
        <div class="support">
            <div class="support_desc">
                <h3>Live Support</h3>
                <p><span>24/7 Live Technical Support</span></p>
                <p>Lorem Ipsum placeholder text…</p>
            </div>
            <img src="images/contact.png" alt="" />
            <div class="clear"></div>
        </div>
        <div class="section group">
            <div class="col span_2_of_3">
                <div class="contact-form">
                    <h2>Contact Us</h2>

                    <?php 
                    if ($error) echo "<span style='color:red'>$error</span>";
                    if ($msg) echo "<span style='color:green'>$msg</span>";
                    ?>

                    <form action="" method="post" autocomplete="off">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div>
                            <span><label>NAME</label></span>
                            <span><input type="text" name="name" required></span>
                        </div>
                        <div>
                            <span><label>E-MAIL</label></span>
                            <span><input type="email" name="email" required></span>
                        </div>
                        <div>
                            <span><label>MOBILE.NO</label></span>
                            <span><input type="text" name="contact" pattern="[0-9+\-\s]{6,20}" required></span>
                        </div>
                        <div>
                            <span><label>MESSAGE</label></span>
                            <span><textarea name="message" required></textarea></span>
                        </div>
                        <div>
                            <span><input type="submit" name="submit" value="SUBMIT"></span>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col span_1_of_3">
                <div class="company_address">
                    <h2>Company Information :</h2>
                    <p>344 East-Goran,</p>
                    <p>Khilgaon,Dhaka-1219,</p>
                    <p>Bangladesh</p>
                    <p>Mobile:01622425286</p>
                    <p>Phone: 0176210187</p>
                    <p>Email: <span>nayemhowlader77@gmial.com</span></p>
                    <p>Follow on: <span>Facebook</span>, <span>Twitter</span></p>
                </div>
            </div>
        </div>    	
    </div>
</div>
<?php include 'inc/footer.php'; ?>
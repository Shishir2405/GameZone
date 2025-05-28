<?php
// Enhanced Database Connection with Environment Support
// Supports both local development and cloud deployment (Railway, Heroku, etc.)

// Database configuration - uses environment variables for production
$host = $_ENV['MYSQLHOST'] ?? $_SERVER['MYSQLHOST'] ?? 'localhost';
$username = $_ENV['MYSQLUSER'] ?? $_SERVER['MYSQLUSER'] ?? 'root';
$password = $_ENV['MYSQLPASSWORD'] ?? $_SERVER['MYSQLPASSWORD'] ?? '';
$database = $_ENV['MYSQLDATABASE'] ?? $_SERVER['MYSQLDATABASE'] ?? 'webservices_colour';
$port = $_ENV['MYSQLPORT'] ?? $_SERVER['MYSQLPORT'] ?? 3306;

// Establish database connection with proper error handling
try {
    $con = new mysqli($host, $username, $password, $database, $port);
    
    // Check connection
    if ($con->connect_error) {
        error_log("Database Connection Failed: " . $con->connect_error);
        die("Database connection failed. Please try again later.");
    }
    
    // Set charset to prevent encoding issues
    $con->set_charset("utf8mb4");
    
} catch (Exception $e) {
    error_log("Database Error: " . $e->getMessage());
    die("Database service unavailable. Please try again later.");
}

// Set timezone
date_default_timezone_set("Asia/Kolkata");

// Enhanced encryption function with stronger security
function encryptor($action, $string) {
    $output = false;
    
    // Use environment variables for encryption keys in production
    $secret_key = $_ENV['ENCRYPTION_KEY'] ?? $_SERVER['ENCRYPTION_KEY'] ?? 'your-super-secret-key-change-me';
    $secret_iv = $_ENV['ENCRYPTION_IV'] ?? $_SERVER['ENCRYPTION_IV'] ?? 'your-secret-iv-change-me-too';
    
    $encrypt_method = "AES-256-CBC";
    
    // Generate secure key and IV
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    
    try {
        if ($action == 'encrypt') {
            $encrypted = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($encrypted);
        } else if ($action == 'decrypt') {
            $decrypted = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
            $output = $decrypted;
        }
    } catch (Exception $e) {
        error_log("Encryption Error: " . $e->getMessage());
        return false;
    }
    
    return $output;
}

// Generate secure reference code
function refcode() {
    $characters = '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ'; // Added letters, removed confusing chars
    $length = 6; // Increased length for better uniqueness
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, strlen($characters) - 1)];
    }
    
    return $randomString;
}

// Generate secure OTP
function generateOTP() {
    $characters = '123456789';
    $length = 6; // Increased OTP length for better security
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, strlen($characters) - 1)];
    }
    
    return $randomString;
}

// Improved user function with prepared statements
function user($con, $field, $id) {
    // Validate field name to prevent SQL injection
    $allowed_fields = ['id', 'username', 'mobile', 'email', 'code', 'owncode', 'status', 'created_at'];
    if (!in_array($field, $allowed_fields)) {
        return false;
    }
    
    $stmt = $con->prepare("SELECT `$field` FROM `tbl_user` WHERE `id` = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $con->error);
        return false;
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row[$field];
    }
    
    $stmt->close();
    return false;
}

// Improved wallet function with prepared statements
function wallet($con, $field, $id) {
    $allowed_fields = ['id', 'userid', 'amount', 'created_at', 'updated_at'];
    if (!in_array($field, $allowed_fields)) {
        return false;
    }
    
    $stmt = $con->prepare("SELECT `$field` FROM `tbl_wallet` WHERE `userid` = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $con->error);
        return false;
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row[$field];
    }
    
    $stmt->close();
    return 0; // Return 0 for wallet amounts if not found
}

// Improved bonus function with prepared statements
function bonus($con, $field, $id) {
    $allowed_fields = ['id', 'userid', 'amount', 'level1', 'level2', 'created_at', 'updated_at'];
    if (!in_array($field, $allowed_fields)) {
        return false;
    }
    
    $stmt = $con->prepare("SELECT `$field` FROM `tbl_bonus` WHERE `userid` = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $con->error);
        return false;
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row[$field];
    }
    
    $stmt->close();
    return 0; // Return 0 for bonus amounts if not found
}

// Improved gameid function
function gameid($con) {
    $stmt = $con->prepare("SELECT `gameid` FROM `tbl_gameid` ORDER BY id DESC LIMIT 1");
    if (!$stmt) {
        error_log("Prepare failed: " . $con->error);
        return false;
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row['gameid'];
    }
    
    $stmt->close();
    return null;
}

// Improved content function
function content($con, $page) {
    $allowed_pages = ['about', 'privacy', 'terms', 'support'];
    if (!in_array($page, $allowed_pages)) {
        return false;
    }
    
    $stmt = $con->prepare("SELECT `$page` FROM `content` WHERE `id` = 1");
    if (!$stmt) {
        error_log("Prepare failed: " . $con->error);
        return false;
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row[$page];
    }
    
    $stmt->close();
    return '';
}

// Improved payment settings function
function minamountsetting($con, $page) {
    $allowed_settings = ['level1', 'level2', 'bonusamount', 'min_withdraw', 'min_recharge'];
    if (!in_array($page, $allowed_settings)) {
        return false;
    }
    
    $stmt = $con->prepare("SELECT `$page` FROM `tbl_paymentsetting` WHERE `id` = 1");
    if (!$stmt) {
        error_log("Prepare failed: " . $con->error);
        return false;
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row[$page];
    }
    
    $stmt->close();
    return 0;
}

// Text truncation functions (unchanged)
function truncate($mytext) {
    $chars = 610;
    $mytext = substr($mytext, 0, $chars);
    $mytext = substr($mytext, 0, strrpos($mytext, ' '));
    return $mytext;
}

function truncate2($mytext) {
    $chars = 220;
    $mytext = substr($mytext, 0, $chars);
    $mytext = substr($mytext, 0, strrpos($mytext, ' '));
    return $mytext;
}

// Improved site settings function
function setting($con, $page) {
    $allowed_settings = ['site_name', 'site_logo', 'site_url', 'contact_email', 'contact_phone'];
    if (!in_array($page, $allowed_settings)) {
        return false;
    }
    
    $stmt = $con->prepare("SELECT `$page` FROM `site_setting` WHERE `id` = 1");
    if (!$stmt) {
        error_log("Prepare failed: " . $con->error);
        return false;
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row[$page];
    }
    
    $stmt->close();
    return '';
}

// Enhanced winner calculation function (kept original logic but with prepared statements)
function winner($con, $periodid, $tab, $column) {
    $stmt = $con->prepare("SELECT 
        (SUM(amount) - SUM(amount)/100*2) as tradeamountwithtax,
        SUM(amount) as tradeamount,
        SUM(CASE WHEN type = 'button' THEN amount END) button,
        SUM(CASE WHEN value = 'Green' THEN amount END) as green,
        (SUM(CASE WHEN value = 'Green' THEN amount END) - (SUM(CASE WHEN value = 'Green' THEN amount END)/100*2))*2 as greenwinamount,
        (SUM(CASE WHEN value = 'Green' THEN amount END) - (SUM(CASE WHEN value = 'Green' THEN amount END)/100*2))*1.5 as greenwinamountwithviolet,
        SUM(CASE WHEN value = 'Violet' THEN amount END) violet,
        (SUM(CASE WHEN value = 'Violet' THEN amount END) - (SUM(CASE WHEN value = 'Violet' THEN amount END)/100*2))*4.5 as violetwinamount,
        SUM(CASE WHEN value = 'Red' THEN amount END) red,
        (SUM(CASE WHEN value = 'Red' THEN amount END) - (SUM(CASE WHEN value = 'Red' THEN amount END)/100*2))*2 as redwinamount,
        (SUM(CASE WHEN value = 'Red' THEN amount END) - (SUM(CASE WHEN value = 'Red' THEN amount END)/100*2))*1.5 as redwinamountwithviolet,
        SUM(CASE WHEN type = 'number' THEN amount END) number,
        SUM(CASE WHEN value = '0' THEN amount END) `zero`,
        (SUM(CASE WHEN value = '0' THEN amount END) - (SUM(CASE WHEN value = '0' THEN amount END)/100*2))*9 as zerowinamount,
        SUM(CASE WHEN value = '1' THEN amount END) `one`,
        (SUM(CASE WHEN value = '1' THEN amount END) - (SUM(CASE WHEN value = '1' THEN amount END)/100*2))*9 as onewinamount,
        SUM(CASE WHEN value = '2' THEN amount END) `two`,
        (SUM(CASE WHEN value = '2' THEN amount END) - (SUM(CASE WHEN value = '2' THEN amount END)/100*2))*9 as twowinamount,
        SUM(CASE WHEN value = '3' THEN amount END) `three`,
        (SUM(CASE WHEN value = '3' THEN amount END) - (SUM(CASE WHEN value = '3' THEN amount END)/100*2))*9 as threewinamount,
        SUM(CASE WHEN value = '4' THEN amount END) `four`,
        (SUM(CASE WHEN value = '4' THEN amount END) - (SUM(CASE WHEN value = '4' THEN amount END)/100*2))*9 as fourwinamount,
        SUM(CASE WHEN value = '5' THEN amount END) `five`,
        (SUM(CASE WHEN value = '5' THEN amount END) - (SUM(CASE WHEN value = '5' THEN amount END)/100*2))*9 as fivewinamount,
        SUM(CASE WHEN value = '6' THEN amount END) `six`,
        (SUM(CASE WHEN value = '6' THEN amount END) - (SUM(CASE WHEN value = '6' THEN amount END)/100*2))*9 as sixwinamount,
        SUM(CASE WHEN value = '7' THEN amount END) `seven`,
        (SUM(CASE WHEN value = '7' THEN amount END) - (SUM(CASE WHEN value = '7' THEN amount END)/100*2))*9 as sevenwinamount,
        SUM(CASE WHEN value = '8' THEN amount END) `eight`,
        (SUM(CASE WHEN value = '8' THEN amount END) - (SUM(CASE WHEN value = '8' THEN amount END)/100*2))*9 as eightwinamount,
        SUM(CASE WHEN value = '9' THEN amount END) `nine`,
        (SUM(CASE WHEN value = '9' THEN amount END) - (SUM(CASE WHEN value = '9' THEN amount END)/100*2))*9 as ninewinamount
    FROM `tbl_betting` WHERE `periodid` = ? AND `tab` = ?");
    
    if (!$stmt) {
        error_log("Prepare failed: " . $con->error);
        return false;
    }
    
    $stmt->bind_param("ss", $periodid, $tab);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row[$column] ?? 0;
    }
    
    $stmt->close();
    return 0;
}

// Number mappings array
$numbermappings = array("zero", "one", "two", "three", "four", "five", "six", "seven", "eight", "nine");

// Enhanced user promo code function with prepared statements
function userpromocode($con, $userid, $code, $tradeamount, $periodid) {
    $today = date("Y-m-d H:i:s");
    
    // Get commission settings
    $stmt = $con->prepare("SELECT * FROM `tbl_paymentsetting` WHERE `id` = 1");
    $stmt->execute();
    $commissionResult = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$commissionResult) return false;
    
    $level1commission = $commissionResult['level1'];
    $level2commission = $commissionResult['level2'];
    $level1 = ($tradeamount * $level1commission / 100);
    $level2 = ($tradeamount * $level2commission / 100);
    
    // Get user level information
    $stmt = $con->prepare("SELECT `code`, 
        (SELECT `id` FROM `tbl_user` WHERE `owncode` = ?) as level1id,
        (SELECT `code` FROM `tbl_user` WHERE `owncode` = ?) as level1code 
        FROM `tbl_user` WHERE `id` = ?");
    
    $stmt->bind_param("ssi", $code, $code, $userid);
    $stmt->execute();
    $userlevel1Result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$userlevel1Result) return false;
    
    $level1id = $userlevel1Result['level1id'];
    $level1code = $userlevel1Result['level1code'];
    
    // Get level 2 user
    $level2id = null;
    if ($level1code) {
        $stmt = $con->prepare("SELECT `id` FROM `tbl_user` WHERE `owncode` = ?");
        $stmt->bind_param("s", $level1code);
        $stmt->execute();
        $userlevel2Result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($userlevel2Result) {
            $level2id = $userlevel2Result['id'];
        }
    }
    
    // Insert bonus summary
    $stmt = $con->prepare("INSERT INTO `tbl_bonussummery`(`userid`, `periodid`, `level1id`, `level2id`, `level1amount`, `level2amount`, `tradeamount`, `createdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isiiddds", $userid, $periodid, $level1id, $level2id, $level1, $level2, $tradeamount, $today);
    $stmt->execute();
    $stmt->close();
    
    // Update level 1 bonus
    if ($level1id) {
        $level1balance = bonus($con, 'level1', $level1id);
        $finallevel1balance = $level1balance + $level1;
        $bonusbalance1 = bonus($con, 'amount', $level1id);
        $finalbonusbalance1 = $bonusbalance1 + $level1;
        
        $stmt = $con->prepare("UPDATE `tbl_bonus` SET `amount` = ?, `level1` = ? WHERE `userid` = ?");
        $stmt->bind_param("ddi", $finalbonusbalance1, $finallevel1balance, $level1id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Update level 2 bonus
    if ($level2id) {
        $level2balance = bonus($con, 'level2', $level2id);
        $finallevel2balance = $level2balance + $level2;
        $bonusbalance2 = bonus($con, 'amount', $level2id);
        $finalbonusbalance2 = $bonusbalance2 + $level2;
        
        $stmt = $con->prepare("UPDATE `tbl_bonus` SET `amount` = ?, `level2` = ? WHERE `userid` = ?");
        $stmt->bind_param("ddi", $finalbonusbalance2, $finallevel2balance, $level2id);
        $stmt->execute();
        $stmt->close();
    }
    
    return true;
}

// Enhanced invite bonus function
function invitebonus($con, $userid, $refcode) {
    // Check if user has made first recharge
    $stmt = $con->prepare("SELECT * FROM `tbl_walletsummery` WHERE `userid` = ? AND `actiontype` = 'recharge'");
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $chksummeryRow = $stmt->get_result()->num_rows;
    $stmt->close();
    
    if ($chksummeryRow == 1) {
        // Get referrer user ID
        $stmt = $con->prepare("SELECT `id` FROM `tbl_user` WHERE `owncode` = ?");
        $stmt->bind_param("s", $refcode);
        $stmt->execute();
        $userResult = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$userResult) return false;
        
        $refuserid = $userResult['id'];
        
        // Get current bonus balance
        $availableBalance = bonus($con, 'amount', $refuserid);
        
        // Get bonus amount from settings
        $bonusAmount = minamountsetting($con, 'bonusamount');
        $finalbonusbalance = $availableBalance + $bonusAmount;
        $today = date("Y-m-d H:i:s");
        
        // Update bonus
        $stmt = $con->prepare("UPDATE `tbl_bonus` SET `amount` = ?, `level1` = ? WHERE `userid` = ?");
        $stmt->bind_param("ddi", $finalbonusbalance, $finalbonusbalance, $refuserid);
        $stmt->execute();
        $stmt->close();
        
        // Insert bonus summary
        $stmt = $con->prepare("INSERT INTO `tbl_bonussummery`(`userid`, `periodid`, `level1id`, `level2id`, `level1amount`, `level2amount`, `tradeamount`, `createdate`) VALUES (?, '0', ?, '0', '110', '0', '0', ?)");
        $stmt->bind_param("iis", $userid, $refuserid, $today);
        $stmt->execute();
        $stmt->close();
        
        return true;
    }
    
    return false;
}

// Enhanced base URL function
function getBaseUrl() {
    $currentPath = $_SERVER['PHP_SELF'];
    $pathInfo = pathinfo($currentPath);
    $hostName = $_SERVER['HTTP_HOST'];
    
    // Detect protocol more reliably
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
               $_SERVER['SERVER_PORT'] == 443 ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    
    $protocol = $isHttps ? 'https://' : 'http://';
    
    return $protocol . $hostName . $pathInfo['dirname'] . '/';
}

// Helper function to sanitize input
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Helper function to validate email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Helper function to validate mobile number
function validate_mobile($mobile) {
    return preg_match('/^[0-9]{10}$/', $mobile);
}

// Helper function to generate secure password hash
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Helper function to verify password
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

?>
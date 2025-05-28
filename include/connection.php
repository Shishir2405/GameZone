<?php
// PostgreSQL Database Connection for Render.com
// Your database credentials from Render:
// Host: dpg-d0rkitp5pdvs73e381c0-a
// Database: webservices_colour_user  
// Password: 0r1wd0EzpKkObj5Ba8WFJ5q9BLTRXitL
// Port: 5432

$host = $_ENV['PGHOST'] ?? $_SERVER['PGHOST'] ?? 'dpg-d0rkitp5pdvs73e381c0-a';
$username = $_ENV['PGUSER'] ?? $_SERVER['PGUSER'] ?? 'webservices_colour_user';
$password = $_ENV['PGPASSWORD'] ?? $_SERVER['PGPASSWORD'] ?? '0r1wd0EzpKkObj5Ba8WFJ5q9BLTRXitL';
$database = $_ENV['PGDATABASE'] ?? $_SERVER['PGDATABASE'] ?? 'webservices_colour_user';
$port = $_ENV['PGPORT'] ?? $_SERVER['PGPORT'] ?? 5432;

// Create PostgreSQL connection
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$database;sslmode=require";
    $con = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    // Set timezone
    $con->exec("SET timezone = 'Asia/Kolkata'");
    
} catch (PDOException $e) {
    error_log("Database Connection Failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

// Set timezone for PHP
date_default_timezone_set("Asia/Kolkata");

// Enhanced encryption function
function encryptor($action, $string) {
    $output = false;
    
    $secret_key = $_ENV['ENCRYPTION_KEY'] ?? $_SERVER['ENCRYPTION_KEY'] ?? 'your-super-secret-key-change-me';
    $secret_iv = $_ENV['ENCRYPTION_IV'] ?? $_SERVER['ENCRYPTION_IV'] ?? 'your-secret-iv-change-me-too';
    
    $encrypt_method = "AES-256-CBC";
    
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
    $characters = '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ';
    $length = 6;
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, strlen($characters) - 1)];
    }
    
    return $randomString;
}

// Generate secure OTP
function generateOTP() {
    $characters = '123456789';
    $length = 6;
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, strlen($characters) - 1)];
    }
    
    return $randomString;
}

// Updated user function for PostgreSQL
function user($con, $field, $id) {
    $allowed_fields = ['id', 'username', 'mobile', 'email', 'code', 'owncode', 'status', 'created_at'];
    if (!in_array($field, $allowed_fields)) {
        return false;
    }
    
    try {
        $stmt = $con->prepare("SELECT $field FROM tbl_user WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        return $result ? $result[$field] : false;
    } catch (PDOException $e) {
        error_log("User query failed: " . $e->getMessage());
        return false;
    }
}

// Updated wallet function for PostgreSQL
function wallet($con, $field, $id) {
    $allowed_fields = ['id', 'userid', 'amount', 'created_at', 'updated_at'];
    if (!in_array($field, $allowed_fields)) {
        return false;
    }
    
    try {
        $stmt = $con->prepare("SELECT $field FROM tbl_wallet WHERE userid = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        return $result ? $result[$field] : 0;
    } catch (PDOException $e) {
        error_log("Wallet query failed: " . $e->getMessage());
        return 0;
    }
}

// Updated bonus function for PostgreSQL
function bonus($con, $field, $id) {
    $allowed_fields = ['id', 'userid', 'amount', 'level1', 'level2', 'created_at', 'updated_at'];
    if (!in_array($field, $allowed_fields)) {
        return false;
    }
    
    try {
        $stmt = $con->prepare("SELECT $field FROM tbl_bonus WHERE userid = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        return $result ? $result[$field] : 0;
    } catch (PDOException $e) {
        error_log("Bonus query failed: " . $e->getMessage());
        return 0;
    }
}

// Updated gameid function for PostgreSQL
function gameid($con) {
    try {
        $stmt = $con->prepare("SELECT gameid FROM tbl_gameid ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result ? $result['gameid'] : null;
    } catch (PDOException $e) {
        error_log("Game ID query failed: " . $e->getMessage());
        return null;
    }
}

// Updated content function for PostgreSQL
function content($con, $page) {
    $allowed_pages = ['about', 'privacy', 'terms', 'support'];
    if (!in_array($page, $allowed_pages)) {
        return false;
    }
    
    try {
        $stmt = $con->prepare("SELECT $page FROM content WHERE id = 1");
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result ? $result[$page] : '';
    } catch (PDOException $e) {
        error_log("Content query failed: " . $e->getMessage());
        return '';
    }
}

// Updated payment settings function for PostgreSQL
function minamountsetting($con, $page) {
    $allowed_settings = ['level1', 'level2', 'bonusamount', 'min_withdraw', 'min_recharge'];
    if (!in_array($page, $allowed_settings)) {
        return false;
    }
    
    try {
        $stmt = $con->prepare("SELECT $page FROM tbl_paymentsetting WHERE id = 1");
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result ? $result[$page] : 0;
    } catch (PDOException $e) {
        error_log("Payment setting query failed: " . $e->getMessage());
        return 0;
    }
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

// Updated site settings function for PostgreSQL
function setting($con, $page) {
    $allowed_settings = ['site_name', 'site_logo', 'site_url', 'contact_email', 'contact_phone'];
    if (!in_array($page, $allowed_settings)) {
        return false;
    }
    
    try {
        $stmt = $con->prepare("SELECT $page FROM site_setting WHERE id = 1");
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result ? $result[$page] : '';
    } catch (PDOException $e) {
        error_log("Setting query failed: " . $e->getMessage());
        return '';
    }
}

// Updated winner function for PostgreSQL
function winner($con, $periodid, $tab, $column) {
    try {
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
            SUM(CASE WHEN value = '0' THEN amount END) as zero,
            (SUM(CASE WHEN value = '0' THEN amount END) - (SUM(CASE WHEN value = '0' THEN amount END)/100*2))*9 as zerowinamount,
            SUM(CASE WHEN value = '1' THEN amount END) as one,
            (SUM(CASE WHEN value = '1' THEN amount END) - (SUM(CASE WHEN value = '1' THEN amount END)/100*2))*9 as onewinamount,
            SUM(CASE WHEN value = '2' THEN amount END) as two,
            (SUM(CASE WHEN value = '2' THEN amount END) - (SUM(CASE WHEN value = '2' THEN amount END)/100*2))*9 as twowinamount,
            SUM(CASE WHEN value = '3' THEN amount END) as three,
            (SUM(CASE WHEN value = '3' THEN amount END) - (SUM(CASE WHEN value = '3' THEN amount END)/100*2))*9 as threewinamount,
            SUM(CASE WHEN value = '4' THEN amount END) as four,
            (SUM(CASE WHEN value = '4' THEN amount END) - (SUM(CASE WHEN value = '4' THEN amount END)/100*2))*9 as fourwinamount,
            SUM(CASE WHEN value = '5' THEN amount END) as five,
            (SUM(CASE WHEN value = '5' THEN amount END) - (SUM(CASE WHEN value = '5' THEN amount END)/100*2))*9 as fivewinamount,
            SUM(CASE WHEN value = '6' THEN amount END) as six,
            (SUM(CASE WHEN value = '6' THEN amount END) - (SUM(CASE WHEN value = '6' THEN amount END)/100*2))*9 as sixwinamount,
            SUM(CASE WHEN value = '7' THEN amount END) as seven,
            (SUM(CASE WHEN value = '7' THEN amount END) - (SUM(CASE WHEN value = '7' THEN amount END)/100*2))*9 as sevenwinamount,
            SUM(CASE WHEN value = '8' THEN amount END) as eight,
            (SUM(CASE WHEN value = '8' THEN amount END) - (SUM(CASE WHEN value = '8' THEN amount END)/100*2))*9 as eightwinamount,
            SUM(CASE WHEN value = '9' THEN amount END) as nine,
            (SUM(CASE WHEN value = '9' THEN amount END) - (SUM(CASE WHEN value = '9' THEN amount END)/100*2))*9 as ninewinamount
        FROM tbl_betting WHERE periodid = ? AND tab = ?");
        
        $stmt->execute([$periodid, $tab]);
        $result = $stmt->fetch();
        
        return $result ? ($result[$column] ?? 0) : 0;
    } catch (PDOException $e) {
        error_log("Winner query failed: " . $e->getMessage());
        return 0;
    }
}

// Number mappings array
$numbermappings = array("zero", "one", "two", "three", "four", "five", "six", "seven", "eight", "nine");

// Helper functions
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_mobile($mobile) {
    return preg_match('/^[0-9]{10}$/', $mobile);
}

function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

function getBaseUrl() {
    $currentPath = $_SERVER['PHP_SELF'];
    $pathInfo = pathinfo($currentPath);
    $hostName = $_SERVER['HTTP_HOST'];
    
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
               $_SERVER['SERVER_PORT'] == 443 ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    
    $protocol = $isHttps ? 'https://' : 'http://';
    
    return $protocol . $hostName . $pathInfo['dirname'] . '/';
}

?>
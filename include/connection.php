<?php
// PostgreSQL Database Connection for Render.com
// Database credentials - update these based on your Render dashboard
$host = $_ENV['PGHOST'] ?? $_SERVER['PGHOST'] ?? 'dpg-d0rkitp5pdvs73e381c0-a';
$username = $_ENV['PGUSER'] ?? $_SERVER['PGUSER'] ?? 'webservices_colour_user';
$password = $_ENV['PGPASSWORD'] ?? $_SERVER['PGPASSWORD'] ?? '0r1wd0EzpKkObj5Ba8WFJ5q9BLTRXitL';
$database = $_ENV['PGDATABASE'] ?? $_SERVER['PGDATABASE'] ?? 'postgres'; // Try 'postgres' as default
$port = $_ENV['PGPORT'] ?? $_SERVER['PGPORT'] ?? 5432;

// Create PostgreSQL connection using PDO
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
    echo "Error: Database connection failed";
    exit();
}

// Set timezone for PHP
date_default_timezone_set("Asia/Kolkata");

function encryptor($action, $string) {
    $output = false;

    $encrypt_method = "AES-256-CBC";
    // Use environment variables for security, fallback to original values
    $secret_key = $_ENV['ENCRYPTION_KEY'] ?? 'muni';
    $secret_iv = $_ENV['ENCRYPTION_IV'] ?? 'muni123';

    // hash
    $key = hash('sha256', $secret_key);

    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    //do the encyption given text/string/number
    if( $action == 'encrypt' ) {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    }
    else if( $action == 'decrypt' ){
        //decrypt the given text/string/number
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }

    return $output;
}

function refcode() {
    $characters = '123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < 5; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $pin=$randomString;
}

function generateOTP() {
    $characters = '123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < 4; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $pin=$randomString;
}

function user($a, $field, $id) {
    try {
        $stmt = $a->prepare("SELECT $field FROM tbl_user WHERE id = ?");
        $stmt->execute([$id]);
        $userresult = $stmt->fetch();
        return $userresult ? $userresult[$field] : null;
    } catch (PDOException $e) {
        error_log("User query failed: " . $e->getMessage());
        return null;
    }
}

function wallet($a, $field, $id) {
    try {
        $stmt = $a->prepare("SELECT $field FROM tbl_wallet WHERE userid = ?");
        $stmt->execute([$id]);
        $walletResult = $stmt->fetch();
        return $walletResult ? $walletResult[$field] : 0;
    } catch (PDOException $e) {
        error_log("Wallet query failed: " . $e->getMessage());
        return 0;
    }
}

function bonus($a, $field, $id) {
    try {
        $stmt = $a->prepare("SELECT $field FROM tbl_bonus WHERE userid = ?");
        $stmt->execute([$id]);
        $walletResult = $stmt->fetch();
        return $walletResult ? $walletResult[$field] : 0;
    } catch (PDOException $e) {
        error_log("Bonus query failed: " . $e->getMessage());
        return 0;
    }
}

function gameid($a) {
    try {
        $stmt = $a->prepare("SELECT gameid FROM tbl_gameid ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $userresult = $stmt->fetch();
        return $userresult ? $userresult['gameid'] : null;
    } catch (PDOException $e) {
        error_log("Game ID query failed: " . $e->getMessage());
        return null;
    }
}

function content($a, $page) {
    try {
        $stmt = $a->prepare("SELECT $page FROM content WHERE id = 1");
        $stmt->execute();
        $page_result = $stmt->fetch();
        return $page_result ? $page_result[$page] : '';
    } catch (PDOException $e) {
        error_log("Content query failed: " . $e->getMessage());
        return '';
    }
}

function minamountsetting($a, $page) {
    try {
        $stmt = $a->prepare("SELECT $page FROM tbl_paymentsetting WHERE id = 1");
        $stmt->execute();
        $page_result = $stmt->fetch();
        return $page_result ? $page_result[$page] : 0;
    } catch (PDOException $e) {
        error_log("Payment setting query failed: " . $e->getMessage());
        return 0;
    }
}

function truncate($mytext) {
    //Number of characters to show
    $chars = 610;
    $mytext = substr($mytext, 0, $chars);
    $mytext = substr($mytext, 0, strrpos($mytext, ' '));
    return $mytext;
}

function truncate2($mytext) {
    //Number of characters to show
    $chars = 220;
    $mytext = substr($mytext, 0, $chars);
    $mytext = substr($mytext, 0, strrpos($mytext, ' '));
    return $mytext;
}

function setting($a, $page) {
    try {
        $stmt = $a->prepare("SELECT $page FROM site_setting WHERE id = 1");
        $stmt->execute();
        $page_result = $stmt->fetch();
        return $page_result ? $page_result[$page] : '';
    } catch (PDOException $e) {
        error_log("Setting query failed: " . $e->getMessage());
        return '';
    }
}

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

$numbermappings = array("zero", "one", "two", "three", "four", "five", "six", "seven", "eight", "nine");

function userpromocode($a, $userid, $code, $tradeamount, $periodid) {
    $today = date("Y-m-d H:i:s");
    
    try {
        // Get commission settings
        $stmt = $a->prepare("SELECT * FROM tbl_paymentsetting WHERE id = 1");
        $stmt->execute();
        $commissionResult = $stmt->fetch();
        
        if (!$commissionResult) return false;
        
        $level1commission = $commissionResult['level1'];
        $level2commission = $commissionResult['level2'];
        $level1 = ($tradeamount * $level1commission / 100);
        $level2 = ($tradeamount * $level2commission / 100);

        // Get user level information with subqueries
        $stmt = $a->prepare("SELECT code, 
            (SELECT id FROM tbl_user WHERE owncode = ?) as level1id,
            (SELECT code FROM tbl_user WHERE owncode = ?) as level1code 
            FROM tbl_user WHERE id = ?");
        $stmt->execute([$code, $code, $userid]);
        $userlevel1Result = $stmt->fetch();
        
        if (!$userlevel1Result) return false;
        
        $level1id = $userlevel1Result['level1id'];
        $level1code = $userlevel1Result['level1code'];

        // Get level 2 user
        $level2id = null;
        if ($level1code) {
            $stmt = $a->prepare("SELECT id FROM tbl_user WHERE owncode = ?");
            $stmt->execute([$level1code]);
            $userlevel2Result = $stmt->fetch();
            if ($userlevel2Result) {
                $level2id = $userlevel2Result['id'];
            }
        }

        // Insert bonus summary
        $stmt = $a->prepare("INSERT INTO tbl_bonussummery (userid, periodid, level1id, level2id, level1amount, level2amount, tradeamount, createdate) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userid, $periodid, $level1id, $level2id, $level1, $level2, $tradeamount, $today]);

        // Update level 1 bonus
        if ($level1id) {
            $level1balance = bonus($a, 'level1', $level1id);
            $finallevel1balance = $level1balance + $level1;
            $bonusbalance1 = bonus($a, 'amount', $level1id);
            $finalbonusbalance1 = $bonusbalance1 + $level1;

            $stmt = $a->prepare("UPDATE tbl_bonus SET amount = ?, level1 = ? WHERE userid = ?");
            $stmt->execute([$finalbonusbalance1, $finallevel1balance, $level1id]);
        }

        // Update level 2 bonus
        if ($level2id) {
            $level2balance = bonus($a, 'level2', $level2id);
            $finallevel2balance = $level2balance + $level2;
            $bonusbalance2 = bonus($a, 'amount', $level2id);
            $finalbonusbalance2 = $bonusbalance2 + $level2;

            $stmt = $a->prepare("UPDATE tbl_bonus SET amount = ?, level2 = ? WHERE userid = ?");
            $stmt->execute([$finalbonusbalance2, $finallevel2balance, $level2id]);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("User promo code failed: " . $e->getMessage());
        return false;
    }
}

function invitebonus($a, $userid, $refcode) {
    try {
        // Check if user has made first recharge
        $stmt = $a->prepare("SELECT * FROM tbl_walletsummery WHERE userid = ? AND actiontype = 'recharge'");
        $stmt->execute([$userid]);
        $chksummeryRow = $stmt->rowCount();
        
        if ($chksummeryRow == 1) {
            // Get referrer user ID
            $stmt = $a->prepare("SELECT id FROM tbl_user WHERE owncode = ?");
            $stmt->execute([$refcode]);
            $userResult = $stmt->fetch();
            
            if (!$userResult) return false;
            
            $refuserid = $userResult['id'];
            
            // Get current bonus balance
            $availableBalance = bonus($a, 'amount', $refuserid);
            
            // Get bonus amount from settings
            $bonusAmount = minamountsetting($a, 'bonusamount');
            $finalbonusbalance = $availableBalance + $bonusAmount;
            $today = date("Y-m-d H:i:s");

            // Update bonus
            $stmt = $a->prepare("UPDATE tbl_bonus SET amount = ?, level1 = ? WHERE userid = ?");
            $stmt->execute([$finalbonusbalance, $finalbonusbalance, $refuserid]);
            
            // Insert bonus summary
            $stmt = $a->prepare("INSERT INTO tbl_bonussummery (userid, periodid, level1id, level2id, level1amount, level2amount, tradeamount, createdate) VALUES (?, '0', ?, '0', '110', '0', '0', ?)");
            $stmt->execute([$userid, $refuserid, $today]);
            
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Invite bonus failed: " . $e->getMessage());
        return false;
    }
}

function getBaseUrl() {
    // output: /myproject/index.php
    $currentPath = $_SERVER['PHP_SELF'];
    
    // output: Array ( [dirname] => /myproject [basename] => index.php [extension] => php [filename] => index )
    $pathInfo = pathinfo($currentPath);
    
    // output: localhost
    $hostName = $_SERVER['HTTP_HOST'];
    
    // Enhanced protocol detection for cloud platforms
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
               $_SERVER['SERVER_PORT'] == 443 ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    
    $protocol = $isHttps ? 'https://' : 'http://';
    
    // return: http://localhost/myproject/
    return $protocol . $hostName . $pathInfo['dirname'] . '/';
}

// Helper functions for enhanced functionality
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

?>
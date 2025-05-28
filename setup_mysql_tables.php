<?php
// setup_mysql_tables.php - Run this once to create all MySQL tables
// Delete this file after running successfully

$host = 'mysql.railway.internal';
$username = 'root';
$password = 'eP0irvmTdOsjGKmbWybUGVbkhypYILSF';
$database = 'railway';
$port = 3306;

// Create MySQL connection
$con = @mysqli_connect($host, $username, $password, $database, $port);
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "✅ Connected to MySQL database!<br><br>";

// SQL commands to create all tables
$sql_commands = [
    // Users table
    "CREATE TABLE IF NOT EXISTS `tbl_user` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `username` varchar(100) DEFAULT NULL,
        `mobile` varchar(15) NOT NULL,
        `password` varchar(255) NOT NULL,
        `code` varchar(20) DEFAULT NULL,
        `owncode` varchar(20) DEFAULT NULL,
        `email` varchar(100) DEFAULT NULL,
        `privacy` tinyint(1) DEFAULT 0,
        `status` enum('0','1') DEFAULT '1',
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `mobile` (`mobile`),
        UNIQUE KEY `owncode` (`owncode`)
    )",

    // Wallet table
    "CREATE TABLE IF NOT EXISTS `tbl_wallet` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `userid` int(11) NOT NULL,
        `amount` decimal(10,2) DEFAULT 0.00,
        `envelopestatus` tinyint(1) DEFAULT 0,
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `userid` (`userid`)
    )",

    // Bonus table
    "CREATE TABLE IF NOT EXISTS `tbl_bonus` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `userid` int(11) NOT NULL,
        `amount` decimal(10,2) DEFAULT 0.00,
        `level1` decimal(10,2) DEFAULT 0.00,
        `level2` decimal(10,2) DEFAULT 0.00,
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `userid` (`userid`)
    )",

    // Betting table
    "CREATE TABLE IF NOT EXISTS `tbl_betting` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `userid` int(11) NOT NULL,
        `periodid` varchar(50) NOT NULL,
        `tab` varchar(20) NOT NULL,
        `type` enum('button','number') NOT NULL,
        `value` varchar(10) NOT NULL,
        `amount` decimal(10,2) NOT NULL,
        `acceptrule` varchar(10) DEFAULT NULL,
        `status` enum('pending','win','loss') DEFAULT 'pending',
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `userid` (`userid`),
        KEY `periodid` (`periodid`)
    )",

    // Game ID table
    "CREATE TABLE IF NOT EXISTS `tbl_gameid` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `gameid` varchar(50) NOT NULL,
        `result` varchar(10) DEFAULT NULL,
        `status` enum('active','completed') DEFAULT 'active',
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    )",

    // Product table
    "CREATE TABLE IF NOT EXISTS `tbl_product` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `description` text,
        `price` decimal(10,2) NOT NULL,
        `status` enum('active','inactive') DEFAULT 'active',
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    )",

    // Content table
    "CREATE TABLE IF NOT EXISTS `content` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `about` text,
        `privacy` text,
        `terms` text,
        `support` text,
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    )",

    // Payment settings table
    "CREATE TABLE IF NOT EXISTS `tbl_paymentsetting` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `level1` decimal(5,2) DEFAULT 0.00,
        `level2` decimal(5,2) DEFAULT 0.00,
        `bonusamount` decimal(10,2) DEFAULT 0.00,
        `min_withdraw` decimal(10,2) DEFAULT 100.00,
        `min_recharge` decimal(10,2) DEFAULT 50.00,
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    )",

    // Site settings table
    "CREATE TABLE IF NOT EXISTS `site_setting` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `site_name` varchar(100) DEFAULT 'Color Game',
        `site_logo` varchar(255) DEFAULT NULL,
        `site_url` varchar(255) DEFAULT NULL,
        `contact_email` varchar(100) DEFAULT NULL,
        `contact_phone` varchar(20) DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    )",

    // Bonus summary table
    "CREATE TABLE IF NOT EXISTS `tbl_bonussummery` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `userid` int(11) NOT NULL,
        `periodid` varchar(50) DEFAULT NULL,
        `level1id` int(11) DEFAULT NULL,
        `level2id` int(11) DEFAULT NULL,
        `level1amount` decimal(10,2) DEFAULT 0.00,
        `level2amount` decimal(10,2) DEFAULT 0.00,
        `tradeamount` decimal(10,2) DEFAULT 0.00,
        `createdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `userid` (`userid`)
    )",

    // Wallet summary table
    "CREATE TABLE IF NOT EXISTS `tbl_walletsummery` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `userid` int(11) NOT NULL,
        `orderid` int(11) DEFAULT NULL,
        `amount` decimal(10,2) NOT NULL,
        `type` enum('debit','credit') DEFAULT 'credit',
        `actiontype` enum('recharge','withdraw','betting','bonus','win') NOT NULL,
        `description` text,
        `status` enum('pending','completed','failed') DEFAULT 'pending',
        `createdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `userid` (`userid`)
    )",

    // Order table
    "CREATE TABLE IF NOT EXISTS `tbl_order` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `userid` int(11) NOT NULL,
        `transactionid` varchar(100) NOT NULL,
        `amount` decimal(10,2) NOT NULL,
        `status` enum('0','1') DEFAULT '0',
        `createdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `userid` (`userid`)
    )",

    // User result table
    "CREATE TABLE IF NOT EXISTS `tbl_userresult` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `userid` int(11) NOT NULL,
        `periodid` varchar(50) NOT NULL,
        `type` varchar(20) NOT NULL,
        `value` varchar(10) NOT NULL,
        `amount` decimal(10,2) NOT NULL,
        `openprice` decimal(10,2) NOT NULL,
        `tab` varchar(20) NOT NULL,
        `paidamount` decimal(10,2) NOT NULL,
        `fee` decimal(10,2) DEFAULT 0.00,
        `status` enum('pending','win','loss') DEFAULT 'pending',
        `createdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `userid` (`userid`)
    )",

    // Result table
    "CREATE TABLE IF NOT EXISTS `tbl_result` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `periodid` varchar(50) NOT NULL,
        `price` decimal(10,2) DEFAULT 0.00,
        `randomprice` decimal(10,2) DEFAULT 0.00,
        `result` varchar(10) DEFAULT NULL,
        `randomresult` varchar(10) DEFAULT NULL,
        `color` varchar(20) DEFAULT NULL,
        `randomcolor` varchar(20) DEFAULT NULL,
        `resulttype` enum('real','random') DEFAULT 'real',
        `tabtype` varchar(20) DEFAULT NULL,
        `createdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `periodid` (`periodid`)
    )",

    // Other required tables
    "CREATE TABLE IF NOT EXISTS `tbl_tempwinner` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `periodid` varchar(50) NOT NULL,
        `number` varchar(10) NOT NULL,
        `color` varchar(20) NOT NULL,
        `total` decimal(10,2) NOT NULL,
        `type` varchar(20) NOT NULL,
        PRIMARY KEY (`id`)
    )",

    "CREATE TABLE IF NOT EXISTS `tbl_randomdata` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `number` varchar(10) NOT NULL,
        `color` varchar(20) NOT NULL,
        PRIMARY KEY (`id`)
    )",

    "CREATE TABLE IF NOT EXISTS `tbl_manualresultswitch` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `status` enum('0','1') DEFAULT '0',
        PRIMARY KEY (`id`)
    )",

    "CREATE TABLE IF NOT EXISTS `tbl_manualresult` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `value` varchar(10) NOT NULL,
        `number` varchar(10) NOT NULL,
        `status` enum('0','1') DEFAULT '0',
        PRIMARY KEY (`id`)
    )",

    "CREATE TABLE IF NOT EXISTS `tbl_gamesettings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `setting_name` varchar(100) NOT NULL,
        `setting_value` varchar(255) NOT NULL,
        PRIMARY KEY (`id`)
    )",

    "CREATE TABLE IF NOT EXISTS `tbl_recharge` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `userid` int(11) NOT NULL,
        `amount` decimal(10,2) NOT NULL,
        `status` enum('pending','completed','failed') DEFAULT 'pending',
        `createdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        `mobile` varchar(15) DEFAULT NULL,
        `txn` varchar(100) DEFAULT NULL,
        `paymentMethod` varchar(50) DEFAULT NULL,
        PRIMARY KEY (`id`)
    )",

    "CREATE TABLE IF NOT EXISTS `tbl_withdrawal` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `userid` int(11) NOT NULL,
        `amount` decimal(10,2) NOT NULL,
        `status` enum('pending','completed','failed') DEFAULT 'pending',
        `createdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    )",

    "CREATE TABLE IF NOT EXISTS `admin_bank` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `bank_name` varchar(100) NOT NULL,
        `account_number` varchar(50) NOT NULL,
        `upi_id` varchar(100) DEFAULT NULL,
        PRIMARY KEY (`id`)
    )",

    "CREATE TABLE IF NOT EXISTS `tbl_envelop` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `userid` int(11) NOT NULL,
        `amount` decimal(10,2) NOT NULL,
        `rechargestatus` enum('0','1') DEFAULT '0',
        `createdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    )"
];

// Execute each command
foreach ($sql_commands as $index => $sql) {
    if (mysqli_query($con, $sql)) {
        echo "✅ Table " . ($index + 1) . " created successfully<br>";
    } else {
        echo "❌ Error creating table " . ($index + 1) . ": " . mysqli_error($con) . "<br>";
    }
}

echo "<br>🎉 Database setup completed!<br><br>";

// Insert default data
echo "📋 Inserting default data...<br>";

$default_data = [
    "INSERT INTO `content` (`id`, `about`, `privacy`, `terms`, `support`) VALUES 
     (1, 'Welcome to Color Game', 'Privacy Policy Content', 'Terms and Conditions', 'Contact Support')
     ON DUPLICATE KEY UPDATE `about` = VALUES(`about`)",
    
    "INSERT INTO `tbl_paymentsetting` (`id`, `level1`, `level2`, `bonusamount`, `min_withdraw`, `min_recharge`) VALUES 
     (1, 2.00, 1.00, 110.00, 100.00, 50.00)
     ON DUPLICATE KEY UPDATE `level1` = VALUES(`level1`)",
    
    "INSERT INTO `site_setting` (`id`, `site_name`, `site_logo`, `site_url`, `contact_email`, `contact_phone`) VALUES 
     (1, 'Color Game', 'logo9.jpg', 'https://your-app.onrender.com', 'admin@colorgame.com', '+1234567890')
     ON DUPLICATE KEY UPDATE `site_name` = VALUES(`site_name`)",
    
    "INSERT INTO `tbl_gameid` (`gameid`, `status`) VALUES ('GAME001', 'active')",
    
    "INSERT INTO `tbl_manualresultswitch` (`id`, `status`) VALUES (1, '0') ON DUPLICATE KEY UPDATE `status` = VALUES(`status`)",
    
    "INSERT INTO `tbl_gamesettings` (`id`, `setting_name`, `setting_value`) VALUES (1, 'game_active', '1') ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)",
    
    "INSERT INTO `admin_bank` (`id`, `bank_name`, `account_number`, `upi_id`) VALUES 
     (1, 'Demo Bank', '1234567890', 'demo@upi') ON DUPLICATE KEY UPDATE `bank_name` = VALUES(`bank_name`)",
    
    // Insert some sample random data for the game
    "INSERT INTO `tbl_randomdata` (`number`, `color`) VALUES 
     ('0', 'Red'), ('1', 'Green'), ('2', 'Red'), ('3', 'Green'), ('4', 'Red'), 
     ('5', 'Green'), ('6', 'Red'), ('7', 'Green'), ('8', 'Red'), ('9', 'Green')",
    
    // Insert manual result options
    "INSERT INTO `tbl_manualresult` (`value`, `number`, `status`) VALUES 
     ('Green', '1', '0'), ('Red', '2', '0'), ('Violet', '0', '0')"
];

foreach ($default_data as $index => $sql) {
    if (mysqli_query($con, $sql)) {
        echo "✅ Default data " . ($index + 1) . " inserted<br>";
    } else {
        echo "❌ Error inserting data " . ($index + 1) . ": " . mysqli_error($con) . "<br>";
    }
}

echo "<br>📊 Database summary:<br>";

// Show created tables
$tables_query = mysqli_query($con, "SHOW TABLES");
echo "<br>📋 Created tables:<br>";
while ($table = mysqli_fetch_array($tables_query)) {
    echo "- " . $table[0] . "<br>";
}

echo "<br>⚠️ <strong>IMPORTANT:</strong><br>";
echo "1. Delete this setup_mysql_tables.php file after successful setup<br>";
echo "2. Update your Render environment variables with MySQL credentials<br>";
echo "3. Your application should now work with MySQL!<br>";

mysqli_close($con);
?>
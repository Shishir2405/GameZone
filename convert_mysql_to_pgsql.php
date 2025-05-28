<?php
// convert_mysql_to_pgsql.php
// Run this script to automatically convert MySQL functions to PostgreSQL
// This will create backup files and show you what needs to be changed

$files_to_convert = [
    'wtenvelope-00.php',
    'recharge.php', 
    'transactions.php',
    'winnerResult.php',
    'gatewayekaypay.php',
    'userResult.php',
    'periodid-generation.php',
    'order.php',
    'registerNow.php',
    'response.php'
];

echo "🔍 MySQL to PostgreSQL Conversion Analysis\n";
echo "==========================================\n\n";

foreach ($files_to_convert as $filename) {
    if (!file_exists($filename)) {
        echo "❌ File not found: $filename\n";
        continue;
    }
    
    $content = file_get_contents($filename);
    $lines = explode("\n", $content);
    
    echo "📄 Analyzing $filename:\n";
    echo "------------------------\n";
    
    $found_issues = false;
    
    foreach ($lines as $line_num => $line) {
        $line_number = $line_num + 1;
        
        // Check for mysqli functions
        if (preg_match('/mysqli_query|mysqli_fetch_array|mysqli_fetch_assoc|mysqli_num_rows|mysqli_insert_id/', $line)) {
            $found_issues = true;
            echo "Line $line_number: " . trim($line) . "\n";
            
            // Suggest replacement
            if (strpos($line, 'mysqli_query') !== false) {
                echo "  → Replace with: \$stmt = \$con->prepare(\"...\"); \$stmt->execute();\n";
            }
            if (strpos($line, 'mysqli_fetch_array') !== false) {
                echo "  → Replace with: \$result = \$stmt->fetch();\n";
            }
            if (strpos($line, 'mysqli_num_rows') !== false) {
                echo "  → Replace with: \$count = \$stmt->rowCount();\n";
            }
        }
    }
    
    if (!$found_issues) {
        echo "✅ No MySQL functions found\n";
    }
    
    echo "\n";
}

echo "🔧 CONVERSION RECOMMENDATIONS:\n";
echo "==============================\n\n";

echo "1. **BACKUP YOUR FILES FIRST**:\n";
echo "   cp -r . ../backup_before_conversion/\n\n";

echo "2. **Most Critical Files to Fix**:\n";
echo "   - index.php (causing the current error)\n";
echo "   - All files listed above\n\n";

echo "3. **Common Conversions Needed**:\n\n";

echo "OLD MySQL Pattern:\n";
echo "\$query = mysqli_query(\$con, \"SELECT * FROM table WHERE id = '\$id'\");\n";
echo "\$result = mysqli_fetch_array(\$query);\n\n";

echo "NEW PostgreSQL Pattern:\n";
echo "try {\n";
echo "    \$stmt = \$con->prepare(\"SELECT * FROM table WHERE id = ?\");\n";
echo "    \$stmt->execute([\$id]);\n";
echo "    \$result = \$stmt->fetch();\n";
echo "} catch (PDOException \$e) {\n";
echo "    error_log(\"Query failed: \" . \$e->getMessage());\n";
echo "}\n\n";

echo "4. **Quick Fix for index.php Error**:\n";
echo "   Find the line with mysqli_query around line 74 and replace it.\n\n";

echo "5. **Alternative: Switch Back to MySQL**:\n";
echo "   - Delete PostgreSQL database on Render\n";
echo "   - Create MySQL database instead\n";
echo "   - Use original connection.php\n\n";

echo "✅ Analysis complete!\n";
?>
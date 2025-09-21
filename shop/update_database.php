<?php
// Database Update Script for Google OAuth
include_once 'config/config.php';
include_once 'lib/Database.php';

$db = new Database();

echo "<h2>Database Update for Google OAuth</h2>";

// Check if columns already exist
$check_columns = "SHOW COLUMNS FROM tbl_customer LIKE 'google_id'";
$result = $db->select($check_columns);

if ($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✅ Google OAuth columns already exist in the database.</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Google OAuth columns not found. Adding them now...</p>";
    
    // Add the columns
    $sql1 = "ALTER TABLE `tbl_customer` 
             ADD COLUMN `google_id` VARCHAR(255) NULL AFTER `pass`,
             ADD COLUMN `oauth_provider` ENUM('local', 'google') DEFAULT 'local' AFTER `google_id`,
             ADD COLUMN `profile_picture` VARCHAR(500) NULL AFTER `oauth_provider`";
    
    $result1 = $db->insert($sql1);
    
    if ($result1) {
        echo "<p style='color: green;'>✅ Successfully added Google OAuth columns to tbl_customer table.</p>";
        
        // Add indexes
        $sql2 = "ALTER TABLE `tbl_customer` ADD UNIQUE INDEX `unique_google_id` (`google_id`)";
        $result2 = $db->insert($sql2);
        
        $sql3 = "ALTER TABLE `tbl_customer` ADD UNIQUE INDEX `unique_email` (`email`)";
        $result3 = $db->insert($sql3);
        
        if ($result2 && $result3) {
            echo "<p style='color: green;'>✅ Successfully added indexes.</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Columns added but some indexes may already exist.</p>";
        }
        
        echo "<p style='color: green; font-weight: bold;'>🎉 Database update completed successfully!</p>";
        echo "<p><a href='login.php'>Click here to test Google Sign-In</a></p>";
        
    } else {
        echo "<p style='color: red;'>❌ Error adding columns. Please check the error message:</p>";
        echo "<p style='color: red;'>" . mysqli_error($db->link) . "</p>";
    }
}

// Show current table structure
echo "<h3>Current tbl_customer table structure:</h3>";
$show_structure = "DESCRIBE tbl_customer";
$structure_result = $db->select($show_structure);

if ($structure_result) {
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $structure_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<br><p><strong>Note:</strong> You can delete this file (update_database.php) after running it successfully.</p>";
?>

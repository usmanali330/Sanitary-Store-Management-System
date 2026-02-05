<?php
/**
 * Database Connection Test Script
 * 
 * Use this file to test your database connection before using the main application
 * 
 * Instructions:
 * 1. Update the database credentials below
 * 2. Open this file in your browser: http://localhost/Sanitary-Store-Management-System/test_connection.php
 * 3. Check if connection is successful
 */

// ============================================
// UPDATE THESE VALUES WITH YOUR DATABASE INFO
// ============================================
$db_host = 'localhost';           // Your database host
$db_user = 'root';                // Your database username
$db_pass = '';                     // Your database password
$db_name = 'sanitary_store_db';    // Your database name

// ============================================
// TEST CONNECTION
// ============================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #c3e6cb;
            margin: 20px 0;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #f5c6cb;
            margin: 20px 0;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #bee5eb;
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔌 Database Connection Test</h1>
        
        <?php
        echo '<div class="info">';
        echo '<strong>Testing connection with:</strong><br>';
        echo 'Host: <span class="code">' . htmlspecialchars($db_host) . '</span><br>';
        echo 'User: <span class="code">' . htmlspecialchars($db_user) . '</span><br>';
        echo 'Database: <span class="code">' . htmlspecialchars($db_name) . '</span>';
        echo '</div>';
        
        // Test connection
        $conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);
        
        if ($conn->connect_error) {
            echo '<div class="error">';
            echo '<strong>❌ Connection Failed!</strong><br>';
            echo 'Error: ' . htmlspecialchars($conn->connect_error) . '<br><br>';
            echo '<strong>Common Solutions:</strong><br>';
            echo '1. Check if host, username, password, and database name are correct<br>';
            echo '2. Verify remote MySQL access is enabled on your server<br>';
            echo '3. Check if your IP is whitelisted in Remote MySQL settings<br>';
            echo '4. Verify firewall is not blocking port 3306<br>';
            echo '5. Try adding port number: <span class="code">hostname:3306</span>';
            echo '</div>';
        } else {
            echo '<div class="success">';
            echo '<strong>✅ Connection Successful!</strong><br>';
            echo 'You can now use the application.';
            echo '</div>';
            
            // Test database operations
            echo '<h2>Database Information</h2>';
            echo '<table>';
            echo '<tr><th>Property</th><th>Value</th></tr>';
            echo '<tr><td>Server Version</td><td>' . $conn->server_info . '</td></tr>';
            echo '<tr><td>Host Info</td><td>' . $conn->host_info . '</td></tr>';
            echo '<tr><td>Character Set</td><td>' . $conn->character_set_name() . '</td></tr>';
            echo '</table>';
            
            // Check if tables exist
            $tables_query = "SHOW TABLES";
            $tables_result = $conn->query($tables_query);
            
            if ($tables_result && $tables_result->num_rows > 0) {
                echo '<h2>Database Tables (' . $tables_result->num_rows . ')</h2>';
                echo '<table>';
                echo '<tr><th>Table Name</th></tr>';
                while ($row = $tables_result->fetch_array()) {
                    echo '<tr><td>' . htmlspecialchars($row[0]) . '</td></tr>';
                }
                echo '</table>';
            } else {
                echo '<div class="info">';
                echo '<strong>⚠️ No tables found.</strong><br>';
                echo 'You may need to import the database schema. Check <span class="code">database/schema.sql</span>';
                echo '</div>';
            }
            
            $conn->close();
        }
        ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
            <p><strong>Next Steps:</strong></p>
            <ol>
                <li>If connection is successful, update <span class="code">includes/config.php</span> with the same credentials</li>
                <li>Open the main application: <a href="index.php">Go to Dashboard</a></li>
                <li>Delete this test file for security: <span class="code">test_connection.php</span></li>
            </ol>
        </div>
    </div>
</body>
</html>


# Localhost Setup Guide - Connect to Remote Database

This guide will help you set up the Sanitary Store Management System on localhost while connecting to your existing remote database.

## Prerequisites

1. **XAMPP** (or WAMP/MAMP) installed on your local machine
2. **Remote database credentials** from your server:
   - Database Host (e.g., `your-server.com` or IP address)
   - Database Username
   - Database Password
   - Database Name
3. **Remote database must allow remote connections** (check with your hosting provider)

## Step 1: Install XAMPP

1. Download and install XAMPP from https://www.apachefriends.org/
2. Start **Apache** from XAMPP Control Panel
3. **Note:** You don't need to start MySQL since we'll use the remote database

## Step 2: Copy Project Files

1. Copy the entire `Sanitary-Store-Management-System` folder to:
   - **Windows:** `C:\xampp\htdocs\Sanitary-Store-Management-System\`
   - **Mac:** `/Applications/XAMPP/htdocs/Sanitary-Store-Management-System/`
   - **Linux:** `/opt/lampp/htdocs/Sanitary-Store-Management-System/`

## Step 3: Configure Database Connection

1. Open `includes/config.php` in a text editor
2. Update the database credentials to connect to your remote database:

```php
<?php
// Remote Database Configuration
define('DB_HOST', 'your-remote-server.com');  // Your server hostname or IP
define('DB_USER', 'your_database_username');   // Your database username
define('DB_PASS', 'your_database_password');    // Your database password
define('DB_NAME', 'sanitary_store_db');         // Your database name

// ... rest of the file
```

**Important:** Replace:
- `your-remote-server.com` with your actual server hostname or IP address
- `your_database_username` with your actual database username
- `your_database_password` with your actual database password
- `sanitary_store_db` with your actual database name (if different)

## Step 4: Enable Remote Database Access

**On your remote server/hosting:**

1. **cPanel/WHM:**
   - Go to "Remote MySQL" in cPanel
   - Add your local IP address or use `%` to allow all IPs (less secure)
   - Or add your home IP address

2. **Direct MySQL:**
   - The database user must have remote access privileges
   - Contact your hosting provider if you're not sure

3. **Firewall:**
   - Ensure MySQL port (usually 3306) is open on your server
   - Some hosts use different ports - check with your provider

## Step 5: Test Connection

1. Open your browser and go to:
   ```
   http://localhost/Sanitary-Store-Management-System/
   ```

2. If you see the login page or dashboard, the connection is working!

3. If you see a connection error:
   - Double-check your database credentials
   - Verify remote database access is enabled
   - Check if your firewall is blocking the connection
   - Contact your hosting provider for assistance

## Step 6: Verify Everything Works

1. **Login:** Use your existing admin credentials
2. **Test Features:**
   - View products
   - Create a test sale
   - Generate an invoice
   - Check reports

## Troubleshooting

### Error: "Connection refused" or "Can't connect to MySQL server"

**Solutions:**
- Verify the database host is correct (might need port number: `hostname:3306`)
- Check if remote MySQL access is enabled on your server
- Verify your IP is whitelisted in remote MySQL settings
- Check firewall settings on both local and remote servers

### Error: "Access denied for user"

**Solutions:**
- Double-check username and password
- Verify the user has remote access privileges
- Try creating a new database user with remote access

### Error: "Unknown database"

**Solutions:**
- Verify the database name is correct
- Check if the database exists on the remote server

### Slow Performance

**Possible causes:**
- Network latency (normal when using remote database)
- Large database size
- Internet connection speed

**Solutions:**
- Consider using a local database for development
- Optimize your internet connection
- Use a VPN if your ISP is blocking the connection

## Security Notes

⚠️ **Important Security Considerations:**

1. **Never commit database credentials to Git**
   - Keep `config.php` out of version control
   - Use environment variables or a separate config file

2. **Use strong passwords** for database access

3. **Limit remote access** to specific IP addresses when possible

4. **Use SSL/SSL connection** if your hosting provider supports it:
   ```php
   define('DB_HOST', 'ssl://your-remote-server.com');
   ```

## Alternative: Local Database Setup

If you prefer to use a local database for testing:

1. Start **MySQL** in XAMPP
2. Open phpMyAdmin: `http://localhost/phpmyadmin`
3. Create a new database: `sanitary_store_db`
4. Import the SQL file: `database/schema.sql`
5. Update `config.php` to use localhost:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'sanitary_store_db');
   ```

## Support

If you encounter any issues:
1. Check the error messages in your browser
2. Check Apache error logs in XAMPP
3. Verify all credentials are correct
4. Contact your hosting provider for database access issues

---

**Setup completed!** Your dashboard should now be running on localhost and connected to your remote database.


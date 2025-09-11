-- Travel Order System Database Setup
-- Run these commands in phpMyAdmin or MySQL command line on your VPS

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `travelorderdb` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user and grant privileges
CREATE USER IF NOT EXISTS 'nginx123'@'localhost' IDENTIFIED BY '6qbXKVI62Lbcfu9UFPsj';

-- Grant all privileges on the travel orders database
GRANT ALL PRIVILEGES ON `travelorderdb`.* TO 'nginx123'@'localhost';

-- Grant additional privileges that might be needed
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES, LOCK TABLES ON `travelorderdb`.* TO 'nginx123'@'localhost';

-- Also create user for any IP (in case of connection issues)
CREATE USER IF NOT EXISTS 'nginx123'@'%' IDENTIFIED BY '6qbXKVI62Lbcfu9UFPsj';
GRANT ALL PRIVILEGES ON `travelorderdb`.* TO 'nginx123'@'%';

-- Flush privileges to apply changes
FLUSH PRIVILEGES;

-- Verify the database was created
SHOW DATABASES;

-- Verify the user was created
SELECT User, Host FROM mysql.user WHERE User = 'nginx123';

-- Show grants for the user
SHOW GRANTS FOR 'nginx123'@'localhost';
SHOW GRANTS FOR 'nginx123'@'%';

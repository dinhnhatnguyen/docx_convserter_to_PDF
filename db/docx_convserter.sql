

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS docx_converter;
USE docx_converter;

-- Create users table to track user information (optional)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create conversions table to track all document conversions
CREATE TABLE IF NOT EXISTS conversions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    original_filename VARCHAR(255) NOT NULL,
    original_file_size INT NOT NULL COMMENT 'File size in bytes',
    converted_filename VARCHAR(255) NOT NULL,
    converted_file_size INT NOT NULL COMMENT 'File size in bytes',
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    error_message TEXT NULL,
    ip_address VARCHAR(45) NOT NULL COMMENT 'IPv4 or IPv6 address',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create settings table for application configuration
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('max_file_size', '10485760', 'Maximum file size allowed in bytes (10MB)'),
('allowed_extensions', '.docx,.doc', 'Allowed file extensions for upload'),
('delete_original_after', '24', 'Delete original file after X hours'),
('delete_converted_after', '72', 'Delete converted file after X hours');

-- Create table for conversion statistics
CREATE TABLE IF NOT EXISTS conversion_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE UNIQUE NOT NULL,
    total_conversions INT DEFAULT 0,
    successful_conversions INT DEFAULT 0,
    failed_conversions INT DEFAULT 0,
    total_size_processed BIGINT DEFAULT 0 COMMENT 'Total size processed in bytes',
    average_conversion_time FLOAT DEFAULT 0 COMMENT 'Average conversion time in seconds',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create index for better query performance
CREATE INDEX idx_conversions_status ON conversions(status);
CREATE INDEX idx_conversions_created_at ON conversions(created_at);
CREATE INDEX idx_conversion_stats_date ON conversion_stats(date);

-- Create a view for conversion success rate
CREATE OR REPLACE VIEW conversion_success_rate AS
SELECT 
    DATE(created_at) as conversion_date,
    COUNT(*) as total_attempts,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as successful,
    ROUND((SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as success_rate
FROM conversions
GROUP BY DATE(created_at)
ORDER BY DATE(created_at) DESC;

-- Create stored procedure for cleanup old files
DELIMITER //
CREATE PROCEDURE cleanup_old_files()
BEGIN
    -- Mark old conversions for cleanup
    UPDATE conversions 
    SET status = 'cleanup_required'
    WHERE status = 'completed' 
    AND created_at < DATE_SUB(NOW(), INTERVAL 72 HOUR);
END //
DELIMITER ;

-- Create event to run cleanup procedure daily
CREATE EVENT IF NOT EXISTS daily_cleanup
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO CALL cleanup_old_files();

-- Grant permissions to application user
GRANT SELECT, INSERT, UPDATE, DELETE ON docx_converter.* TO 'php_docker'@'%';
FLUSH PRIVILEGES;
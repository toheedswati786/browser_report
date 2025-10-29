CREATE DATABASE IF NOT EXISTS browser_report CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE browser_report;

CREATE TABLE IF NOT EXISTS reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_agent TEXT,
  public_ip VARCHAR(45),
  latitude DECIMAL(10,7),
  longitude DECIMAL(10,7),
  accuracy FLOAT NULL,
  address TEXT,
  languages VARCHAR(255),
  time_zone VARCHAR(100),
  hardware_json JSON,
  referrer TEXT,
  screen_info VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

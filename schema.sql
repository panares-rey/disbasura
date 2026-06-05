-- DisBasura Full Schema v2 — MySQL/MariaDB
-- Fresh install: mysql -u root -p < schema.sql
-- Already installed: use schema_update.sql instead

CREATE DATABASE IF NOT EXISTS disbasura CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE disbasura;

CREATE TABLE IF NOT EXISTS sitios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('resident','leader','admin') NOT NULL DEFAULT 'resident',
    sitio VARCHAR(255),
    phone VARCHAR(50),
    sms_number VARCHAR(20),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS collectors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    sitio VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    status ENUM('available','sick','unavailable') NOT NULL DEFAULT 'available',
    username VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    truck_lat DECIMAL(10,7),
    truck_lng DECIMAL(10,7),
    truck_updated_at DATETIME,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sitio VARCHAR(255) NOT NULL,
    scheduled_at DATETIME NOT NULL,
    waste_type VARCHAR(100) NOT NULL,
    collector_id INT,
    status ENUM('scheduled','assigned','completed','cancelled','disputed','unverified') NOT NULL DEFAULT 'scheduled',
    proof_photo VARCHAR(500),
    resident_proof_photo VARCHAR(500),
    completed_at DATETIME,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (collector_id) REFERENCES collectors(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS weekly_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    day_name VARCHAR(20) NOT NULL,
    sitio VARCHAR(255) NOT NULL,
    status ENUM('pending','received','missed','inactive') NOT NULL DEFAULT 'pending',
    collection_time TIME NOT NULL DEFAULT '07:00:00',
    waste_type VARCHAR(100) NOT NULL DEFAULT 'Mixed',
    collector_id INT,
    notes TEXT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_day_sitio (day_name, sitio),
    FOREIGN KEY (collector_id) REFERENCES collectors(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    submitted_by INT,
    sitio VARCHAR(255) NOT NULL,
    location VARCHAR(500),
    waste_type VARCHAR(100) NOT NULL,
    preferred_date DATETIME NOT NULL,
    note TEXT,
    status ENUM('pending','approved','rejected','completed','assigned') NOT NULL DEFAULT 'pending',
    collector_id INT,
    proof_photo VARCHAR(500),
    resident_proof_photo VARCHAR(500),
    completed_at DATETIME,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (collector_id) REFERENCES collectors(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS disputes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NOT NULL,
    resident_id INT NOT NULL,
    photo VARCHAR(500),
    note TEXT,
    status ENUM('pending','resolved','rejected') NOT NULL DEFAULT 'pending',
    admin_verdict TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (resident_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reset_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    code VARCHAR(10) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- NEW: Feedback / Rating
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NOT NULL,
    resident_id INT NOT NULL,
    rating TINYINT NOT NULL DEFAULT 5,
    comment TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_feedback (schedule_id, resident_id),
    FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (resident_id) REFERENCES users(id) ON DELETE CASCADE
);

-- NEW: Announcements
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    sitio VARCHAR(255) DEFAULT NULL,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- NEW: Activity Log
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- NEW: SMS Log
CREATE TABLE IF NOT EXISTS sms_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('sent','failed','pending') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- NEW: User Preferences (dark mode, language)
CREATE TABLE IF NOT EXISTS user_preferences (
    user_id INT PRIMARY KEY,
    dark_mode TINYINT NOT NULL DEFAULT 0,
    language ENUM('en','fil') NOT NULL DEFAULT 'en',
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

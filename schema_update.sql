-- DisBasura Schema Update — Add New Features
-- Run this in phpMyAdmin > SQL tab if you already have the database
-- OR just use the full schema.sql for a fresh install

USE disbasura;

-- 1. FEEDBACK / RATING
CREATE TABLE IF NOT EXISTS feedback (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id   INT NOT NULL,
    resident_id   INT NOT NULL,
    rating        TINYINT NOT NULL DEFAULT 5 COMMENT '1-5 stars',
    comment       TEXT,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_feedback (schedule_id, resident_id),
    FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (resident_id) REFERENCES users(id)     ON DELETE CASCADE
);

-- 2. ANNOUNCEMENTS
CREATE TABLE IF NOT EXISTS announcements (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    title      VARCHAR(255) NOT NULL,
    message    TEXT NOT NULL,
    sitio      VARCHAR(255) DEFAULT NULL COMMENT 'NULL = all sitios',
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. ACTIVITY LOG
CREATE TABLE IF NOT EXISTS activity_log (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    admin_id   INT NOT NULL,
    action     VARCHAR(255) NOT NULL,
    details    TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 4. SMS LOG (for tracking sent messages)
CREATE TABLE IF NOT EXISTS sms_log (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    phone      VARCHAR(20) NOT NULL,
    message    TEXT NOT NULL,
    status     ENUM('sent','failed','pending') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 5. Add sms_number to users (if not exists)
ALTER TABLE users ADD COLUMN IF NOT EXISTS sms_number VARCHAR(20) DEFAULT NULL;

-- 6. Add unverified status to schedules
ALTER TABLE schedules MODIFY COLUMN status
    ENUM('scheduled','assigned','completed','cancelled','disputed','unverified')
    NOT NULL DEFAULT 'scheduled';

-- 7. USER PREFERENCES (dark mode, language)
CREATE TABLE IF NOT EXISTS user_preferences (
    user_id    INT PRIMARY KEY,
    dark_mode  TINYINT NOT NULL DEFAULT 0,
    language   ENUM('en','fil') NOT NULL DEFAULT 'en',
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 8. Add resident_proof_photo column to schedules (resident-submitted proof)
ALTER TABLE schedules ADD COLUMN IF NOT EXISTS resident_proof_photo VARCHAR(500) DEFAULT NULL;

-- 9. Add proof_photo to requests table (if not exists)
ALTER TABLE requests ADD COLUMN IF NOT EXISTS proof_photo VARCHAR(500) DEFAULT NULL;
ALTER TABLE requests ADD COLUMN IF NOT EXISTS completed_at DATETIME DEFAULT NULL;

-- 10. Fix requests.status ENUM — add 'assigned' value (was missing, causing blank status on assign)
ALTER TABLE requests MODIFY COLUMN status
    ENUM('pending','approved','rejected','completed','assigned') NOT NULL DEFAULT 'pending';

-- 11. Fix existing rows where collector was assigned but status is blank/null
UPDATE requests SET status='assigned' WHERE collector_id IS NOT NULL AND (status='' OR status IS NULL);

-- 12. Fix collectors.status ENUM — ensure 'sick' and 'unavailable' are valid values
ALTER TABLE collectors MODIFY COLUMN status
    ENUM('available','sick','unavailable') NOT NULL DEFAULT 'available';

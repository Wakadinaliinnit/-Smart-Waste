-- ============================================================
-- Smart Waste Collection Management System
-- Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS smart_waste_db;
USE smart_waste_db;

-- ------------------------------------------------------------
-- USERS TABLE
-- Stores all system users: resident, collector, admin, officer
-- ------------------------------------------------------------
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('resident', 'collector', 'admin', 'officer') NOT NULL,
    address VARCHAR(255),
    zone VARCHAR(100),
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- COLLECTION REQUESTS
-- Residents request waste pickup
-- ------------------------------------------------------------
CREATE TABLE collection_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    address VARCHAR(255) NOT NULL,
    zone VARCHAR(100),
    waste_type VARCHAR(50) DEFAULT 'general',
    notes TEXT,
    estimated_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('pending', 'assigned', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    assigned_collector_id INT NULL,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (resident_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_collector_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- WASTE REPORTS
-- Residents report full / uncollected bins
-- ------------------------------------------------------------
CREATE TABLE waste_reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    location VARCHAR(255) NOT NULL,
    zone VARCHAR(100),
    issue_type ENUM('overflowing_bin', 'uncollected_waste', 'illegal_dumping', 'other') NOT NULL,
    description TEXT,
    photo_path VARCHAR(255) NULL,
    status ENUM('open', 'assigned', 'resolved', 'rejected') DEFAULT 'open',
    assigned_collector_id INT NULL,
    reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    FOREIGN KEY (resident_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_collector_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- COLLECTION SCHEDULES
-- Admin-managed schedules per zone
-- ------------------------------------------------------------
CREATE TABLE collection_schedules (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY,
    zone VARCHAR(100) NOT NULL,
    collector_id INT NULL,
    scheduled_date DATE NOT NULL,
    scheduled_time TIME,
    status ENUM('planned', 'in_progress', 'completed', 'missed') DEFAULT 'planned',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (collector_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- NOTIFICATIONS
-- System notifications sent to users
-- ------------------------------------------------------------
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- ACTIVITY LOG (for reports/decision-making)
-- ------------------------------------------------------------
CREATE TABLE activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- SEED: Initial admin account
-- ------------------------------------------------------------
INSERT INTO users (full_name, email, phone, password_hash, role, status)
VALUES ('System Admin', 'admin@localhost', '0700000000',
'$2y$10$qr7b7S6I0ggwL0v206gM8e0U3t3f1dC1.ThMGPkjw5DgVCeZwCwD.', 'admin', 'active');
-- NOTE: Change seeded account credentials immediately after first login.

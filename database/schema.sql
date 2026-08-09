-- =====================================================
-- Campus Connect - University Community Platform
-- Database Schema (ERD implementation)
-- =====================================================

CREATE DATABASE IF NOT EXISTS campus_connect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campus_connect;

-- =====================================================
-- 1. USERS & ROLES
-- =====================================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student', 'club_admin', 'admin') NOT NULL DEFAULT 'student',
    university_id VARCHAR(50) DEFAULT NULL,
    department VARCHAR(100) DEFAULT NULL,
    semester VARCHAR(20) DEFAULT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    status ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- 2. MARKETPLACE (Buy & Sell)
-- =====================================================
CREATE TABLE marketplace_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    category VARCHAR(50),
    price DECIMAL(10,2) NOT NULL,
    condition_type ENUM('new', 'like_new', 'used', 'worn') DEFAULT 'used',
    image_path VARCHAR(255) DEFAULT NULL,
    status ENUM('available', 'sold', 'removed') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- 3. ACADEMIC NOTES SHARING
-- =====================================================
CREATE TABLE notes (
    note_id INT AUTO_INCREMENT PRIMARY KEY,
    uploader_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    subject VARCHAR(100) NOT NULL,
    semester VARCHAR(20) NOT NULL,
    description TEXT,
    file_path VARCHAR(255) NOT NULL,
    download_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploader_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- 4. LOST & FOUND
-- =====================================================
CREATE TABLE lost_found_items (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    reporter_id INT NOT NULL,
    item_type ENUM('lost', 'found') NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    location VARCHAR(150),
    image_path VARCHAR(255) DEFAULT NULL,
    status ENUM('open', 'resolved') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- 5. CLUBS & SOCIETIES
-- =====================================================
CREATE TABLE clubs (
    club_id INT AUTO_INCREMENT PRIMARY KEY,
    club_admin_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    category VARCHAR(80),
    logo_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (club_admin_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE club_members (
    membership_id INT AUTO_INCREMENT PRIMARY KEY,
    club_id INT NOT NULL,
    student_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (club_id) REFERENCES clubs(club_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_membership (club_id, student_id)
) ENGINE=InnoDB;

CREATE TABLE club_posts (
    post_id INT AUTO_INCREMENT PRIMARY KEY,
    club_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (club_id) REFERENCES clubs(club_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- 6. EVENTS & INTERNSHIPS
-- =====================================================
CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    posted_by INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    category ENUM('event', 'workshop', 'internship') NOT NULL,
    description TEXT,
    event_date DATE DEFAULT NULL,
    location VARCHAR(150),
    image_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- 7. COMPLAINTS PORTAL
-- =====================================================
CREATE TABLE complaints (
    complaint_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(80),
    status ENUM('open', 'in_progress', 'resolved', 'rejected') DEFAULT 'open',
    admin_response TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- Seed: default admin account (password: Admin@123 -- change after first login)
-- Hash generated with PHP password_hash(), bcrypt
-- =====================================================
INSERT INTO users (full_name, email, password_hash, role, status)
VALUES ('System Admin', 'admin@campusconnect.edu', '$2y$10$examplehashreplaceonfirstrun.......................', 'admin', 'active');

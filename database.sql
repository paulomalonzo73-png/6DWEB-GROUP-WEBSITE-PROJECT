-- ============================================
--  Limitless Fitness App Database
--  All 4 unique backend features included
-- ============================================

CREATE DATABASE IF NOT EXISTS limitless_db;
USE limitless_db;

-- ============================================
--  USERS TABLE
--  UNIQUE BACKEND 1: is_admin for Admin Access
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  UNIQUE NOT NULL,
    email      VARCHAR(100) UNIQUE NOT NULL,
    password   VARCHAR(255)        NOT NULL,
    is_admin   TINYINT(1)   DEFAULT 0,        -- 1 = admin, 0 = regular user
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
--  USER PROFILES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS user_profiles (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT  NOT NULL,
    gender       ENUM('male', 'female', 'other') NOT NULL,
    age          INT NOT NULL,
    weight       DECIMAL(6,2) NOT NULL,
    weight_unit  ENUM('kg', 'lbs') DEFAULT 'kg',
    height       DECIMAL(6,2) NOT NULL,
    height_unit  ENUM('cm', 'inches', 'ft') DEFAULT 'cm',
    experience   ENUM('beginner', 'intermediate', 'expert') NOT NULL,
    goal         ENUM('bulking', 'cutting', 'endurance', 'general_fitness') NOT NULL,
    metabolism   ENUM('fast', 'moderate', 'slow') NOT NULL,
    workout_type ENUM('gym', 'home') NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
--  GENERATED PLANS TABLE
--  UNIQUE BACKEND 4: Plan History
--  is_deleted + deleted_at for soft delete/restore
-- ============================================
CREATE TABLE IF NOT EXISTS generated_plans (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT      NOT NULL,
    calories   INT      NOT NULL,
    protein    INT      NOT NULL,
    carbs      INT      NOT NULL,
    fats       INT      NOT NULL,
    plan_data  LONGTEXT NOT NULL,
    is_deleted TINYINT(1) DEFAULT 0,       -- UNIQUE BACKEND 4: soft delete flag
    deleted_at TIMESTAMP  NULL DEFAULT NULL, -- UNIQUE BACKEND 4: when deleted
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
--  ACTIVITY LOGS TABLE
--  UNIQUE BACKEND 2: Activity Logs
--  Tracks login, logout, register, plan_generated
-- ============================================
CREATE TABLE IF NOT EXISTS activity_logs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NULL,
    username   VARCHAR(50)  NOT NULL DEFAULT '',  -- store name directly for easy display
    action     VARCHAR(100) NOT NULL,
    details    TEXT         NULL,
    ip_address VARCHAR(45)  NOT NULL DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================
--  SAMPLE DATA
-- ============================================

-- Admin user | password: password
INSERT IGNORE INTO users (username, email, password, is_admin) VALUES
('admin', 'admin@limitless.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Sample regular users | password: password
INSERT IGNORE INTO users (username, email, password, is_admin) VALUES
('john_doe',  'john@example.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0),
('jane_smith','jane@example.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0),
('mike_jones','mike@example.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0);

-- Sample profiles for regular users
INSERT IGNORE INTO user_profiles (user_id, gender, age, weight, weight_unit, height, height_unit, experience, goal, metabolism, workout_type)
SELECT id, 'male',   25, 75.00, 'kg', 175.00, 'cm', 'intermediate', 'bulking',         'moderate', 'gym'  FROM users WHERE username = 'john_doe';

INSERT IGNORE INTO user_profiles (user_id, gender, age, weight, weight_unit, height, height_unit, experience, goal, metabolism, workout_type)
SELECT id, 'female', 22, 58.00, 'kg', 162.00, 'cm', 'beginner',     'general_fitness', 'fast',     'home' FROM users WHERE username = 'jane_smith';

INSERT IGNORE INTO user_profiles (user_id, gender, age, weight, weight_unit, height, height_unit, experience, goal, metabolism, workout_type)
SELECT id, 'male',   28, 85.00, 'kg', 180.00, 'cm', 'expert',       'cutting',         'slow',     'gym'  FROM users WHERE username = 'mike_jones';

-- Sample generated plans for regular users (UNIQUE BACKEND 4: plan history)
INSERT IGNORE INTO generated_plans (user_id, calories, protein, carbs, fats, plan_data, is_deleted)
SELECT id, 2800, 180, 320, 85, '{"goal":"bulking","experience":"intermediate","workout_type":"gym"}', 0
FROM users WHERE username = 'john_doe';

INSERT IGNORE INTO generated_plans (user_id, calories, protein, carbs, fats, plan_data, is_deleted)
SELECT id, 2100, 140, 240, 65, '{"goal":"general_fitness","experience":"beginner","workout_type":"home"}', 0
FROM users WHERE username = 'jane_smith';


INSERT IGNORE INTO generated_plans (user_id, calories, protein, carbs, fats, plan_data, is_deleted)
SELECT id, 1900, 200, 180, 58, '{"goal":"cutting","experience":"expert","workout_type":"gym"}', 0
FROM users WHERE username = 'mike_jones';

-- One soft-deleted plan to demo UNIQUE BACKEND 4
INSERT IGNORE INTO generated_plans (user_id, calories, protein, carbs, fats, plan_data, is_deleted, deleted_at)
SELECT id, 2400, 160, 280, 72, '{"goal":"endurance","experience":"intermediate","workout_type":"home"}', 1, NOW()
FROM users WHERE username = 'john_doe';

-- Sample activity logs (UNIQUE BACKEND 2: activity logs)
INSERT IGNORE INTO activity_logs (user_id, username, action, details, ip_address) VALUES
(1, 'admin',      'login',          'User logged in successfully',         '127.0.0.1'),
(2, 'john_doe',   'register',       'New account created: john_doe',       '127.0.0.1'),
(2, 'john_doe',   'login',          'User logged in successfully',         '127.0.0.1'),
(2, 'john_doe',   'plan_generated', 'Goal: bulking | Experience: intermediate | Type: gym', '127.0.0.1'),
(3, 'jane_smith', 'register',       'New account created: jane_smith',     '127.0.0.1'),
(3, 'jane_smith', 'login',          'User logged in successfully',         '127.0.0.1'),
(3, 'jane_smith', 'plan_generated', 'Goal: general_fitness | Experience: beginner | Type: home', '127.0.0.1'),
(4, 'mike_jones', 'register',       'New account created: mike_jones',     '127.0.0.1'),
(4, 'mike_jones', 'login',          'User logged in successfully',         '127.0.0.1'),
(4, 'mike_jones', 'logout',         'User signed out',                     '127.0.0.1');

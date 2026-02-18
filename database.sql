-- Limitless Fitness App Database
CREATE DATABASE IF NOT EXISTS limitless_db;
USE limitless_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User profiles table
CREATE TABLE IF NOT EXISTS user_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    age INT NOT NULL,
    weight DECIMAL(6,2) NOT NULL,
    weight_unit ENUM('kg', 'lbs') DEFAULT 'kg',
    height DECIMAL(6,2) NOT NULL,
    height_unit ENUM('cm', 'inches', 'ft') DEFAULT 'cm',
    experience ENUM('beginner', 'intermediate', 'expert') NOT NULL,
    goal ENUM('bulking', 'cutting', 'endurance', 'general_fitness') NOT NULL,
    metabolism ENUM('fast', 'moderate', 'slow') NOT NULL,
    workout_type ENUM('gym', 'home') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Generated plans table
CREATE TABLE IF NOT EXISTS generated_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    calories INT NOT NULL,
    protein INT NOT NULL,
    carbs INT NOT NULL,
    fats INT NOT NULL,
    plan_data LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Sample users (password: Test1234!)
INSERT INTO users (username, email, password) VALUES
('demo_user', 'demo@limitless.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

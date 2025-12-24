-- SkillGap Analyzer Database Schema
-- Run this in phpMyAdmin or MySQL CLI

CREATE DATABASE IF NOT EXISTS skillgap_db;
USE skillgap_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    career_goal VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Career paths
CREATE TABLE IF NOT EXISTS career_paths (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50) DEFAULT '💼'
);

-- Required skills for each career path
CREATE TABLE IF NOT EXISTS required_skills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    career_path VARCHAR(100) NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    required_level INT DEFAULT 70
);

-- User's current skills
CREATE TABLE IF NOT EXISTS user_skills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    current_level INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Learning resources
CREATE TABLE IF NOT EXISTS resources (
    id INT PRIMARY KEY AUTO_INCREMENT,
    skill_name VARCHAR(100) NOT NULL,
    title VARCHAR(200) NOT NULL,
    url VARCHAR(500),
    type ENUM('course', 'tutorial', 'article', 'video') DEFAULT 'course',
    platform VARCHAR(100)
);

-- Insert sample career paths
INSERT INTO career_paths (name, description, icon) VALUES
('Full Stack Developer', 'Build complete web applications from frontend to backend', '🌐'),
('Frontend Developer', 'Create beautiful and interactive user interfaces', '🎨'),
('Backend Developer', 'Design robust server-side applications and APIs', '⚙️'),
('Data Scientist', 'Analyze data and build machine learning models', '📊'),
('AI/ML Engineer', 'Develop artificial intelligence and machine learning systems', '🤖'),
('Mobile App Developer', 'Build native and cross-platform mobile applications', '📱'),
('DevOps Engineer', 'Streamline development and deployment processes', '🚀'),
('Cybersecurity Analyst', 'Protect systems and data from cyber threats', '🔒');

-- Insert required skills for career paths
INSERT INTO required_skills (career_path, skill_name, required_level) VALUES
-- Full Stack Developer
('Full Stack Developer', 'HTML/CSS', 85),
('Full Stack Developer', 'JavaScript', 85),
('Full Stack Developer', 'React/Vue', 75),
('Full Stack Developer', 'Node.js', 75),
('Full Stack Developer', 'SQL/Database', 70),
('Full Stack Developer', 'Git', 70),

-- Frontend Developer
('Frontend Developer', 'HTML/CSS', 90),
('Frontend Developer', 'JavaScript', 85),
('Frontend Developer', 'React/Vue', 80),
('Frontend Developer', 'UI/UX Design', 70),
('Frontend Developer', 'Responsive Design', 85),

-- Backend Developer
('Backend Developer', 'PHP/Python/Node', 85),
('Backend Developer', 'SQL/Database', 85),
('Backend Developer', 'REST APIs', 80),
('Backend Developer', 'Authentication', 75),
('Backend Developer', 'Server Management', 70),

-- Data Scientist
('Data Scientist', 'Python', 90),
('Data Scientist', 'Statistics', 85),
('Data Scientist', 'Machine Learning', 80),
('Data Scientist', 'Data Visualization', 75),
('Data Scientist', 'SQL/Database', 70),

-- AI/ML Engineer
('AI/ML Engineer', 'Python', 90),
('AI/ML Engineer', 'Machine Learning', 85),
('AI/ML Engineer', 'Deep Learning', 80),
('AI/ML Engineer', 'TensorFlow/PyTorch', 75),
('AI/ML Engineer', 'Mathematics', 80);

-- Insert sample resources
INSERT INTO resources (skill_name, title, url, type, platform) VALUES
('HTML/CSS', 'HTML & CSS Full Course', 'https://www.freecodecamp.org', 'course', 'freeCodeCamp'),
('HTML/CSS', 'CSS Grid Tutorial', 'https://cssgridgarden.com', 'tutorial', 'CSS Grid Garden'),
('JavaScript', 'JavaScript Fundamentals', 'https://javascript.info', 'course', 'JavaScript.info'),
('JavaScript', 'ES6+ Features', 'https://www.youtube.com/watch?v=NCwa_xi0Uuc', 'video', 'YouTube'),
('React/Vue', 'React Official Tutorial', 'https://react.dev/learn', 'tutorial', 'React.dev'),
('Python', 'Python for Beginners', 'https://www.python.org/about/gettingstarted/', 'course', 'Python.org'),
('SQL/Database', 'SQL Tutorial', 'https://www.w3schools.com/sql/', 'tutorial', 'W3Schools'),
('Git', 'Git & GitHub Crash Course', 'https://www.youtube.com/watch?v=RGOj5yH7evk', 'video', 'YouTube'),
('Machine Learning', 'ML Course by Andrew Ng', 'https://www.coursera.org/learn/machine-learning', 'course', 'Coursera');

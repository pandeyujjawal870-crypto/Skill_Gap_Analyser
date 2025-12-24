-- Fix emoji display issue by updating database charset to utf8mb4
-- Run this SQL in phpMyAdmin to fix the character encoding

USE skillgap_db;

-- Update database charset
ALTER DATABASE skillgap_db CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Update career_paths table
ALTER TABLE career_paths CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Re-insert career paths with proper emojis (delete old ones first)
DELETE FROM career_paths;

INSERT INTO career_paths (name, description, icon) VALUES
('Full Stack Developer', 'Build complete web applications from frontend to backend', '💻'),
('Frontend Developer', 'Create beautiful, responsive user interfaces', '🎨'),
('Backend Developer', 'Design and build server-side logic and databases', '⚙️'),
('Data Scientist', 'Analyze complex data to help organizations make better decisions', '📊'),
('AI/ML Engineer', 'Build intelligent systems that learn and adapt', '🤖'),
('Mobile Developer', 'Create apps for iOS and Android devices', '📱'),
('DevOps Engineer', 'Streamline development and deployment processes', '🚀'),
('Cybersecurity Analyst', 'Protect organizations from cyber threats', '🔒');

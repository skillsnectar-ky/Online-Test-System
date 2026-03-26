-- ============================================================
-- Aspirian.pk Online Test System
-- Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS aspirian_test_system
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE aspirian_test_system;

-- ------------------------------------------------------------
-- Table: users
-- Stores both admin and student accounts
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id          INT(11)      NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('admin','student') NOT NULL DEFAULT 'student',
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: mcqs
-- Stores multiple-choice questions per topic
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mcqs (
    id              INT(11)       NOT NULL AUTO_INCREMENT,
    topic           VARCHAR(100)  NOT NULL,
    question        TEXT          NOT NULL,
    option_a        VARCHAR(255)  NOT NULL,
    option_b        VARCHAR(255)  NOT NULL,
    option_c        VARCHAR(255)  NOT NULL,
    option_d        VARCHAR(255)  NOT NULL,
    correct_option  ENUM('a','b','c','d') NOT NULL,
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_topic (topic)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: results
-- Stores student test results
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS results (
    id          INT(11)      NOT NULL AUTO_INCREMENT,
    user_id     INT(11)      NOT NULL,
    topic       VARCHAR(100) NOT NULL,
    score       INT(11)      NOT NULL DEFAULT 0,
    total       INT(11)      NOT NULL DEFAULT 0,
    date        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_topic (topic)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Default Admin Account
-- Email: admin@aspirian.pk | Password: admin123
-- ------------------------------------------------------------
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@aspirian.pk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- NOTE: Default password hash above is for "password" (Laravel default).
-- After import, run update_admin_password.php or change manually.
-- For plain admin123, generate with: password_hash('admin123', PASSWORD_DEFAULT)

-- ------------------------------------------------------------
-- Sample MCQ Data
-- ------------------------------------------------------------

-- MS Word
INSERT INTO mcqs (topic, question, option_a, option_b, option_c, option_d, correct_option) VALUES
('MS Word', 'What is the shortcut key to Bold text in MS Word?', 'Ctrl+I', 'Ctrl+B', 'Ctrl+U', 'Ctrl+S', 'b'),
('MS Word', 'Which tab contains the "Paragraph" group in MS Word?', 'Insert', 'Review', 'Home', 'View', 'c'),
('MS Word', 'What extension does MS Word 2016 files use by default?', '.doc', '.docx', '.txt', '.odt', 'b'),
('MS Word', 'Which feature checks spelling automatically in MS Word?', 'AutoCorrect', 'AutoFormat', 'AutoText', 'AutoSave', 'a'),
('MS Word', 'What is the shortcut to save a document in MS Word?', 'Ctrl+P', 'Ctrl+S', 'Ctrl+Z', 'Ctrl+O', 'b');

-- MS Excel
INSERT INTO mcqs (topic, question, option_a, option_b, option_c, option_d, correct_option) VALUES
('MS Excel', 'What symbol is used to start a formula in Excel?', '#', '@', '=', '$', 'c'),
('MS Excel', 'Which function returns the largest value in a range?', 'MIN()', 'MAX()', 'SUM()', 'AVERAGE()', 'b'),
('MS Excel', 'What is the shortcut to insert a new worksheet in Excel?', 'Shift+F11', 'Ctrl+F11', 'Alt+F11', 'F11', 'a'),
('MS Excel', 'A cell address like $A$1 is called?', 'Relative reference', 'Mixed reference', 'Absolute reference', 'Named range', 'c'),
('MS Excel', 'Which chart type is best for showing trends over time?', 'Pie chart', 'Bar chart', 'Line chart', 'Scatter chart', 'c');

-- PowerPoint
INSERT INTO mcqs (topic, question, option_a, option_b, option_c, option_d, correct_option) VALUES
('PowerPoint', 'What is a single page in a PowerPoint presentation called?', 'Sheet', 'Slide', 'Page', 'Frame', 'b'),
('PowerPoint', 'Which view shows all slides as thumbnails?', 'Normal View', 'Outline View', 'Slide Sorter View', 'Reading View', 'c'),
('PowerPoint', 'What shortcut starts a slideshow from the beginning?', 'F4', 'F5', 'F6', 'F7', 'b'),
('PowerPoint', 'Which animation effect moves a slide element onto the slide?', 'Exit', 'Emphasis', 'Entrance', 'Motion Path', 'c'),
('PowerPoint', 'What is the default file extension for PowerPoint 2016?', '.ppt', '.pps', '.pptx', '.ppsx', 'c');

-- Internet
INSERT INTO mcqs (topic, question, option_a, option_b, option_c, option_d, correct_option) VALUES
('Internet', 'What does WWW stand for?', 'World Wide Web', 'World Wide Wire', 'Web World Wide', 'Wide World Web', 'a'),
('Internet', 'Which protocol is used to send emails?', 'FTP', 'HTTP', 'SMTP', 'POP3', 'c'),
('Internet', 'What does URL stand for?', 'Uniform Resource Locator', 'Universal Resource Link', 'Unified Resource Locator', 'Uniform Reference Locator', 'a'),
('Internet', 'Which of the following is NOT a web browser?', 'Chrome', 'Firefox', 'Photoshop', 'Safari', 'c'),
('Internet', 'What does ISP stand for?', 'Internet Service Provider', 'Internet Software Program', 'Internal Service Protocol', 'Internet Signal Provider', 'a');

-- Urdu InPage
INSERT INTO mcqs (topic, question, option_a, option_b, option_c, option_d, correct_option) VALUES
('Urdu InPage', 'InPage is primarily used for typing in which language?', 'English', 'Arabic', 'Urdu', 'Persian', 'c'),
('Urdu InPage', 'What is the keyboard shortcut to change text direction to Right-to-Left in InPage?', 'Ctrl+R', 'Ctrl+L', 'Ctrl+T', 'Ctrl+D', 'a'),
('Urdu InPage', 'Which company developed InPage software?', 'Microsoft', 'Concept Software', 'Adobe', 'Corel', 'b'),
('Urdu InPage', 'What is the default font used in InPage for Urdu typing?', 'Nastaliq', 'Naskh', 'Thuluth', 'Kufic', 'a'),
('Urdu InPage', 'InPage stores documents with which file extension?', '.inp', '.inpg', '.inp2', '.ubx', 'a');

-- Introduction to Computer
INSERT INTO mcqs (topic, question, option_a, option_b, option_c, option_d, correct_option) VALUES
('Introduction to Computer', 'What does CPU stand for?', 'Central Processing Unit', 'Computer Processing Unit', 'Central Program Utility', 'Core Processing Unit', 'a'),
('Introduction to Computer', 'Which of the following is an input device?', 'Monitor', 'Printer', 'Keyboard', 'Speaker', 'c'),
('Introduction to Computer', 'RAM stands for?', 'Read Access Memory', 'Random Access Memory', 'Rapid Access Memory', 'Read Accessible Memory', 'b'),
('Introduction to Computer', 'Which of the following is an Operating System?', 'MS Word', 'Windows 10', 'Google Chrome', 'MySQL', 'b'),
('Introduction to Computer', '1 Kilobyte equals how many bytes?', '100', '1000', '1024', '2048', 'c');

-- ============================================================
-- Campus Helpdesk – Database Schema
-- INS3064 Final Project – Đề 13
-- ============================================================

CREATE DATABASE IF NOT EXISTS helpdesk_db
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE helpdesk_db;

-- ─────────────────────────────────────────
-- THÀNH VIÊN 1: users, departments, ticket_categories
-- ─────────────────────────────────────────

CREATE TABLE IF NOT EXISTS departments (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    manager_id  INT DEFAULT NULL,   -- FK → users (set sau)
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('admin','staff','user') DEFAULT 'user',
    dept_id     INT DEFAULT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dept_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- Gán manager sau khi bảng users tồn tại
ALTER TABLE departments
    ADD CONSTRAINT fk_dept_manager
    FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS ticket_categories (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(100) NOT NULL,
    department_id  INT NOT NULL,
    sla_hours      INT NOT NULL DEFAULT 24 COMMENT 'Thời gian xử lý tối đa (giờ)',
    priority_level ENUM('low','medium','high','critical') DEFAULT 'medium',
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- ─────────────────────────────────────────
-- THÀNH VIÊN 2: tickets, ticket_assignments, ticket_comments
-- ─────────────────────────────────────────

CREATE TABLE IF NOT EXISTS tickets (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category_id INT NOT NULL,
    created_by  INT NOT NULL,
    status      ENUM('open','in_progress','pending','resolved','closed') DEFAULT 'open',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES ticket_categories(id),
    FOREIGN KEY (created_by)  REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS ticket_assignments (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id   INT NOT NULL,
    assigned_to INT NOT NULL,
    assigned_by INT NOT NULL,
    assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id)   REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    FOREIGN KEY (assigned_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS ticket_comments (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id  INT NOT NULL,
    user_id    INT NOT NULL,
    body       TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)
);

-- ─────────────────────────────────────────
-- THÀNH VIÊN 3: ticket_status_logs, escalation_logs, satisfaction_surveys
-- ─────────────────────────────────────────

CREATE TABLE IF NOT EXISTS ticket_status_logs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id  INT NOT NULL,
    old_status ENUM('open','in_progress','pending','resolved','closed') NOT NULL,
    new_status ENUM('open','in_progress','pending','resolved','closed') NOT NULL,
    changed_by INT NOT NULL,
    note       TEXT,
    changed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id)  REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS escalation_logs (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id     INT NOT NULL,
    escalated_by  INT NOT NULL,
    escalated_to  INT NOT NULL,
    reason        TEXT NOT NULL,
    level         TINYINT DEFAULT 1 COMMENT '1=first, 2=second, 3=final',
    escalated_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    resolved_at   DATETIME DEFAULT NULL,
    FOREIGN KEY (ticket_id)    REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (escalated_by) REFERENCES users(id),
    FOREIGN KEY (escalated_to) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS satisfaction_surveys (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id    INT NOT NULL UNIQUE,   -- mỗi ticket chỉ 1 survey
    user_id      INT NOT NULL,
    rating       TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment      TEXT,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)
);

-- ─────────────────────────────────────────
-- SEED DATA – dữ liệu mẫu để test
-- ─────────────────────────────────────────

INSERT INTO departments (name, description) VALUES
('IT Support',      'Xử lý sự cố máy tính, mạng, tài khoản'),
('Facilities',      'Cơ sở vật chất, điện, điều hòa, phòng học'),
('Academic Office', 'Phòng đào tạo, LMS, học vụ');

-- password = "password123" (bcrypt hash)
INSERT INTO users (full_name, email, password, role, dept_id) VALUES
('Admin System',    'admin@ischool.vn',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1),
('Nguyen Van IT',   'it@ischool.vn',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 1),
('Tran Thi Facility','facility@ischool.vn','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','staff', 2),
('Le Van Student',  'student@ischool.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user',  NULL),
('Pham Thi Lecturer','lecturer@ischool.vn','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','user', NULL);

UPDATE departments SET manager_id = 2 WHERE id = 1;
UPDATE departments SET manager_id = 3 WHERE id = 2;
UPDATE departments SET manager_id = 1 WHERE id = 3;

INSERT INTO ticket_categories (name, department_id, sla_hours, priority_level) VALUES
('Network / WiFi Issue',    1, 4,  'high'),
('Account / LMS Problem',   1, 8,  'medium'),
('Projector / AV Equipment',2, 2,  'critical'),
('Air Conditioning',        2, 24, 'low'),
('Grade / Transcript Issue',3, 48, 'medium');

INSERT INTO tickets (title, description, category_id, created_by, status) VALUES
('WiFi không kết nối được phòng B201', 'Từ sáng đến giờ không vào được wifi campus', 1, 4, 'open'),
('Tài khoản LMS bị khóa',             'Không đăng nhập được LMS để nộp bài',         2, 5, 'in_progress'),
('Máy chiếu phòng A305 hỏng',         'Màn hình bị sọc, không hiển thị được',        3, 4, 'resolved'),
('Điều hòa phòng C102 không lạnh',    'Bật điều hòa nhưng không ra hơi lạnh',        4, 5, 'closed');

INSERT INTO ticket_status_logs (ticket_id, old_status, new_status, changed_by, note) VALUES
(2, 'open',        'in_progress', 2, 'Đã nhận ticket, đang kiểm tra tài khoản'),
(3, 'open',        'in_progress', 3, 'Đã cử kỹ thuật viên đến kiểm tra'),
(3, 'in_progress', 'resolved',    3, 'Đã thay bóng đèn máy chiếu, hoạt động bình thường'),
(4, 'open',        'in_progress', 3, 'Đã liên hệ đội bảo trì'),
(4, 'in_progress', 'resolved',    3, 'Đã nạp gas điều hòa'),
(4, 'resolved',    'closed',      1, 'Người dùng xác nhận đã xử lý xong');

INSERT INTO satisfaction_surveys (ticket_id, user_id, rating, comment) VALUES
(4, 5, 4, 'Xử lý khá nhanh, nhân viên nhiệt tình. Cảm ơn!');

-- ============================================================
-- Campus Helpdesk - IT & Facility Ticket System
-- Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS campus_helpdesk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campus_helpdesk;

-- 1. users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','staff','user') NOT NULL DEFAULT 'user',
    department_id INT NULL,
    avatar VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. departments
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    manager_id INT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 3. ticket_categories
CREATE TABLE ticket_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    department_id INT NOT NULL,
    sla_hours INT NOT NULL DEFAULT 24 COMMENT 'Max hours to resolve',
    priority_default ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
    keywords TEXT NULL COMMENT 'Comma-separated keywords for auto-routing',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT
);

-- 4. tickets
CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_code VARCHAR(20) NOT NULL UNIQUE,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category_id INT NOT NULL,
    submitter_id INT NOT NULL,
    assigned_to INT NULL,
    priority ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
    status ENUM('open','in_progress','pending','resolved','closed','cancelled') NOT NULL DEFAULT 'open',
    location VARCHAR(200) NULL,
    attachment VARCHAR(255) NULL,
    due_at DATETIME NULL,
    resolved_at DATETIME NULL,
    closed_at DATETIME NULL,
    escalated TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES ticket_categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (submitter_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);

-- 5. ticket_assignments
CREATE TABLE ticket_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    staff_id INT NOT NULL,
    assigned_by INT NOT NULL,
    note TEXT NULL,
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE RESTRICT
);

-- 6. ticket_comments
CREATE TABLE ticket_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    is_internal TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = staff-only note',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
);

-- 7. ticket_status_logs
CREATE TABLE ticket_status_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    changed_by INT NOT NULL,
    old_status VARCHAR(20) NULL,
    new_status VARCHAR(20) NOT NULL,
    note TEXT NULL,
    changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE RESTRICT
);

-- 8. escalation_logs
CREATE TABLE escalation_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    escalated_by INT NULL COMMENT 'NULL = auto escalation by system',
    escalated_to INT NULL,
    reason TEXT NOT NULL,
    escalated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (escalated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (escalated_to) REFERENCES users(id) ON DELETE SET NULL
);

-- 9. satisfaction_surveys
CREATE TABLE satisfaction_surveys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL UNIQUE,
    submitted_by INT NOT NULL,
    rating TINYINT NOT NULL COMMENT '1-5 stars',
    comment TEXT NULL,
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE RESTRICT
);

-- ============================================================
-- FOREIGN KEY back-reference: departments.manager_id -> users.id
ALTER TABLE departments ADD CONSTRAINT fk_dept_manager FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL;
ALTER TABLE users ADD CONSTRAINT fk_user_dept FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Departments
INSERT INTO departments (id, name, description) VALUES
(1, 'IT Support',       'Xử lý sự cố phần mềm, phần cứng, tài khoản, mạng'),
(2, 'Cơ sở vật chất',   'Điều hòa, điện, nước, bàn ghế, phòng học'),
(3, 'Phòng Đào tạo',    'Thời khóa biểu, đăng ký học phần, điểm số');

-- Users (password = bcrypt of "Password@123")
INSERT INTO users (id, full_name, email, password, role, department_id) VALUES
(1, 'Admin Hệ thống',     'admin@ischool.edu.vn',    '$2y$10$ffY11unMzMdFbZKUW5MByuRbNqWvjhtw9LBWe0QYqzrKSKWxkvDFm', 'admin', NULL),
(2, 'Nguyễn Văn IT',      'it@ischool.edu.vn',       '$2y$10$ffY11unMzMdFbZKUW5MByuRbNqWvjhtw9LBWe0QYqzrKSKWxkvDFm', 'staff', 1),
(3, 'Trần Thị CSVC',      'csvc@ischool.edu.vn',     '$2y$10$ffY11unMzMdFbZKUW5MByuRbNqWvjhtw9LBWe0QYqzrKSKWxkvDFm', 'staff', 2),
(4, 'Lê Văn Sinh Viên',   'sv001@ischool.edu.vn',    '$2y$10$ffY11unMzMdFbZKUW5MByuRbNqWvjhtw9LBWe0QYqzrKSKWxkvDFm', 'user',  NULL),
(5, 'Phạm Thị Giảng Viên','gv001@ischool.edu.vn',   '$2y$10$ffY11unMzMdFbZKUW5MByuRbNqWvjhtw9LBWe0QYqzrKSKWxkvDFm', 'user',  NULL);

-- Thêm staff và users
INSERT INTO users (id, full_name, email, password, role, department_id) VALUES
(6,  'Hoàng Minh Đào tạo', 'daotao@ischool.edu.vn',   '$2y$10$ffY11unMzMdFbZKUW5MByuRbNqWvjhtw9LBWe0QYqzrKSKWxkvDFm', 'staff', 3),
(7,  'Trịnh Văn Hùng',     'sv002@ischool.edu.vn',    '$2y$10$ffY11unMzMdFbZKUW5MByuRbNqWvjhtw9LBWe0QYqzrKSKWxkvDFm', 'user',  NULL),
(8,  'Nguyễn Thị Mai',     'sv003@ischool.edu.vn',    '$2y$10$ffY11unMzMdFbZKUW5MByuRbNqWvjhtw9LBWe0QYqzrKSKWxkvDFm', 'user',  NULL),
(9,  'Đinh Quốc Bảo',      'sv004@ischool.edu.vn',    '$2y$10$ffY11unMzMdFbZKUW5MByuRbNqWvjhtw9LBWe0QYqzrKSKWxkvDFm', 'user',  NULL),
(10, 'Vũ Thị Lan',         'gv002@ischool.edu.vn',    '$2y$10$ffY11unMzMdFbZKUW5MByuRbNqWvjhtw9LBWe0QYqzrKSKWxkvDFm', 'user',  NULL);

-- Update department managers
UPDATE departments SET manager_id = 2 WHERE id = 1;
UPDATE departments SET manager_id = 3 WHERE id = 2;
UPDATE departments SET manager_id = 6 WHERE id = 3;

-- Ticket Categories
INSERT INTO ticket_categories (id, name, department_id, sla_hours, priority_default, keywords) VALUES
(1, 'Sự cố máy tính / phần cứng', 1, 8,  'high',   'máy tính,laptop,màn hình,bàn phím,chuột,phần cứng'),
(2, 'Sự cố phần mềm / tài khoản', 1, 4,  'medium', 'phần mềm,tài khoản,mật khẩu,lms,email,đăng nhập'),
(3, 'Sự cố mạng / wifi',          1, 4,  'high',   'wifi,mạng,internet,kết nối,network'),
(4, 'Điều hòa / quạt',            2, 12, 'medium', 'điều hòa,quạt,nóng,lạnh,nhiệt độ'),
(5, 'Điện / đèn chiếu sáng',      2, 8,  'high',   'điện,đèn,bóng đèn,cúp điện,ổ cắm'),
(6, 'Phòng học / bàn ghế',        2, 24, 'low',    'phòng,bàn,ghế,bảng,vệ sinh'),
(7, 'Thời khóa biểu',             3, 24, 'medium', 'thời khóa biểu,lịch học,phòng học,lịch thi'),
(8, 'Đăng ký học phần',           3, 12, 'medium', 'đăng ký,học phần,tín chỉ,môn học'),
(9, 'Điểm số / kết quả học tập',  3, 24, 'medium', 'điểm,kết quả,bảng điểm,phúc tra');

-- ============================================================
-- TICKETS (15 tickets đa dạng trạng thái)
-- ============================================================
INSERT INTO tickets (ticket_code, title, description, category_id, submitter_id, assigned_to, priority, status, location, due_at, resolved_at, closed_at, escalated) VALUES
-- IT Support tickets
('TK-2026-0001', 'Máy tính phòng B201 không khởi động được',
 'Máy tính số 5 phòng B201 bấm nút nguồn không phản hồi, đèn nguồn không sáng. Đã thử rút cắm lại nhưng vẫn không được.',
 1, 4, 2, 'high', 'in_progress', 'Phòng B201', DATE_ADD(NOW(), INTERVAL 8 HOUR), NULL, NULL, 0),

('TK-2026-0002', 'Không đăng nhập được tài khoản LMS',
 'Em bị quên mật khẩu LMS, bấm quên mật khẩu cũng không nhận được email reset về hộp thư sinh viên.',
 2, 4, 2, 'medium', 'open', NULL, DATE_ADD(NOW(), INTERVAL 4 HOUR), NULL, NULL, 0),

('TK-2026-0003', 'Wifi phòng lab A101 chập chờn, mất kết nối liên tục',
 'Trong buổi thực hành hôm nay wifi phòng A101 liên tục mất kết nối khoảng 10-15 phút/lần, ảnh hưởng đến bài thi thực hành.',
 3, 7, 2, 'high', 'in_progress', 'Phòng Lab A101', DATE_ADD(NOW(), INTERVAL 4 HOUR), NULL, NULL, 0),

('TK-2026-0004', 'Màn hình máy chiếu phòng C101 bị sọc ngang',
 'Máy chiếu phòng C101 hiển thị nhiều sọc ngang màu xanh, khiến bài giảng khó nhìn. Đã thử đổi cáp HDMI nhưng vẫn bị.',
 1, 10, 2, 'medium', 'pending', 'Phòng C101', DATE_ADD(NOW(), INTERVAL 8 HOUR), NULL, NULL, 0),

('TK-2026-0005', 'Tài khoản email sinh viên bị vô hiệu hóa',
 'Email @ischool.edu.vn của em không gửi/nhận được thư từ hôm qua, đăng nhập báo lỗi "Account disabled".',
 2, 8, 2, 'high', 'resolved', NULL, DATE_SUB(NOW(), INTERVAL 2 HOUR),
 DATE_SUB(NOW(), INTERVAL 30 MINUTE), NULL, 0);

-- CSVC tickets
INSERT INTO tickets (ticket_code, title, description, category_id, submitter_id, assigned_to, priority, status, location, due_at, resolved_at, closed_at, escalated) VALUES
('TK-2026-0006', 'Điều hòa phòng C305 không mát',
 'Điều hòa bật nhưng không ra hơi lạnh, cả lớp rất nóng trong buổi học chiều nay. Nhiệt độ ngoài trời 38 độ.',
 4, 5, 3, 'medium', 'resolved', 'Phòng C305', DATE_SUB(NOW(), INTERVAL 1 HOUR),
 DATE_SUB(NOW(), INTERVAL 3 HOUR), NULL, 0),

('TK-2026-0007', 'Bóng đèn phòng D204 bị cháy 3 bóng',
 '3 bóng đèn phía cuối phòng D204 bị cháy hết khiến khu vực đó tối, sinh viên ngồi không thấy bảng.',
 5, 9, 3, 'medium', 'closed', 'Phòng D204', DATE_SUB(NOW(), INTERVAL 5 HOUR),
 DATE_SUB(NOW(), INTERVAL 4 HOUR), DATE_SUB(NOW(), INTERVAL 2 HOUR), 0),

('TK-2026-0008', 'Vệ sinh phòng học E101 chưa sạch',
 'Phòng E101 hôm nay có mùi khó chịu, thùng rác chưa được đổ từ hôm qua, sàn nhà còn nhiều giấy vụn.',
 6, 7, 3, 'low', 'open', 'Phòng E101', DATE_ADD(NOW(), INTERVAL 24 HOUR), NULL, NULL, 0),

('TK-2026-0009', 'Ổ cắm điện bàn số 12 phòng lab B303 hỏng',
 'Ổ cắm số 12 không có điện, sinh viên ngồi vị trí này không cắm được laptop, ảnh hưởng đến buổi thực hành.',
 5, 8, 3, 'high', 'in_progress', 'Phòng Lab B303', DATE_ADD(NOW(), INTERVAL 8 HOUR), NULL, NULL, 0),

('TK-2026-0010', 'Máy lạnh phòng họp tầng 4 chảy nước',
 'Máy lạnh phòng họp P401 bị chảy nước xuống sàn, đã đặt xô hứng nhưng lo ngại trơn trượt và hỏng sàn gỗ.',
 4, 5, 3, 'critical', 'in_progress', 'Phòng họp P401', DATE_ADD(NOW(), INTERVAL 2 HOUR), NULL, NULL, 1);

-- Đào tạo tickets
INSERT INTO tickets (ticket_code, title, description, category_id, submitter_id, assigned_to, priority, status, location, due_at, resolved_at, closed_at, escalated) VALUES
('TK-2026-0011', 'Thời khóa biểu bị trùng phòng học',
 'Học phần INS3064 nhóm 2 và học phần INS2010 nhóm 1 bị xếp cùng phòng B201 cùng khung giờ thứ 3 tiết 1-3.',
 7, 4, 6, 'high', 'pending', NULL, DATE_ADD(NOW(), INTERVAL 24 HOUR), NULL, NULL, 0),

('TK-2026-0012', 'Không đăng ký được học phần Đồ án tốt nghiệp',
 'Sinh viên năm 4 không thấy học phần Đồ án tốt nghiệp trong danh sách đăng ký dù đã đủ điều kiện tích lũy 120 tín chỉ.',
 8, 9, 6, 'high', 'resolved', NULL, DATE_SUB(NOW(), INTERVAL 6 HOUR),
 DATE_SUB(NOW(), INTERVAL 1 HOUR), NULL, 0),

('TK-2026-0013', 'Điểm học phần INS2020 bị thiếu trên hệ thống',
 'Điểm thi cuối kỳ học phần INS2020 học kỳ 2 năm 2025 chưa được cập nhật lên hệ thống dù đã thi từ 3 tuần trước.',
 9, 7, 6, 'medium', 'open', NULL, DATE_ADD(NOW(), INTERVAL 24 HOUR), NULL, NULL, 0),

('TK-2026-0014', 'Lịch thi cuối kỳ môn Toán cao cấp bị thay đổi không thông báo',
 'Lịch thi môn Toán cao cấp trên portal thay đổi từ thứ 6 sang thứ 2 nhưng không có thông báo, sinh viên không biết.',
 7, 8, 6, 'critical', 'resolved', NULL, DATE_SUB(NOW(), INTERVAL 12 HOUR),
 DATE_SUB(NOW(), INTERVAL 8 HOUR), NULL, 0),

('TK-2026-0015', 'Cần xác nhận đã hoàn thành chương trình để xét tốt nghiệp',
 'Em đã hoàn thành toàn bộ học phần nhưng hệ thống vẫn hiển thị "Chưa đủ điều kiện xét tốt nghiệp", nhờ phòng kiểm tra giúp.',
 9, 9, 6, 'high', 'in_progress', NULL, DATE_ADD(NOW(), INTERVAL 48 HOUR), NULL, NULL, 0);

-- ============================================================
-- STATUS LOGS
-- ============================================================
INSERT INTO ticket_status_logs (ticket_id, changed_by, old_status, new_status, note) VALUES
-- TK-2026-0001
(1, 4,  NULL,          'open',        'Ticket được tạo'),
(1, 2,  'open',        'in_progress', 'Đã tiếp nhận, đang kiểm tra phần cứng'),
-- TK-2026-0002
(2, 4,  NULL,          'open',        'Ticket được tạo'),
-- TK-2026-0003
(3, 7,  NULL,          'open',        'Ticket được tạo'),
(3, 2,  'open',        'in_progress', 'Đã tiếp nhận, kiểm tra access point'),
-- TK-2026-0004
(4, 10, NULL,          'open',        'Ticket được tạo'),
(4, 2,  'open',        'in_progress', 'Đã liên hệ nhà cung cấp kiểm tra máy chiếu'),
(4, 2,  'in_progress', 'pending',     'Chờ linh kiện thay thế từ nhà cung cấp'),
-- TK-2026-0005
(5, 8,  NULL,          'open',        'Ticket được tạo'),
(5, 2,  'open',        'in_progress', 'Đã kiểm tra tài khoản trên AD'),
(5, 2,  'in_progress', 'resolved',    'Đã kích hoạt lại tài khoản, nguyên nhân do vi phạm chính sách mật khẩu'),
-- TK-2026-0006
(6, 5,  NULL,          'open',        'Ticket được tạo'),
(6, 3,  'open',        'in_progress', 'Đã tiếp nhận'),
(6, 3,  'in_progress', 'resolved',    'Đã vệ sinh lọc điều hòa, bổ sung gas, hoạt động bình thường'),
-- TK-2026-0007
(7, 9,  NULL,          'open',        'Ticket được tạo'),
(7, 3,  'open',        'in_progress', 'Đã lấy bóng đèn thay thế'),
(7, 3,  'in_progress', 'resolved',    'Đã thay 3 bóng đèn mới'),
(7, 3,  'resolved',    'closed',      'Người dùng xác nhận đã ổn'),
-- TK-2026-0008
(8, 7,  NULL,          'open',        'Ticket được tạo'),
-- TK-2026-0009
(9, 8,  NULL,          'open',        'Ticket được tạo'),
(9, 3,  'open',        'in_progress', 'Đã tiếp nhận, kiểm tra hệ thống điện'),
-- TK-2026-0010
(10, 5, NULL,          'open',        'Ticket được tạo'),
(10, 3, 'open',        'in_progress', 'Đã tiếp nhận khẩn cấp, đang xử lý'),
-- TK-2026-0011
(11, 4, NULL,          'open',        'Ticket được tạo'),
(11, 6, 'open',        'in_progress', 'Đang xác minh lịch phòng'),
(11, 6, 'in_progress', 'pending',     'Chờ xác nhận từ bộ môn về phân công phòng lại'),
-- TK-2026-0012
(12, 9, NULL,          'open',        'Ticket được tạo'),
(12, 6, 'open',        'in_progress', 'Đang kiểm tra điều kiện đăng ký'),
(12, 6, 'in_progress', 'resolved',    'Đã mở đăng ký, lỗi do thiếu cập nhật điểm môn tiên quyết'),
-- TK-2026-0013
(13, 7, NULL,          'open',        'Ticket được tạo'),
-- TK-2026-0014
(14, 8, NULL,          'open',        'Ticket được tạo'),
(14, 6, 'open',        'in_progress', 'Đang xác nhận với giảng viên'),
(14, 6, 'in_progress', 'resolved',    'Đã thông báo lại cho sinh viên qua email và cập nhật portal'),
-- TK-2026-0015
(15, 9, NULL,          'open',        'Ticket được tạo'),
(15, 6, 'open',        'in_progress', 'Đang rà soát bảng điểm tổng hợp');

-- ============================================================
-- ASSIGNMENTS
-- ============================================================
INSERT INTO ticket_assignments (ticket_id, staff_id, assigned_by, note) VALUES
(1,  2, 1, 'Phân công tự động theo danh mục IT'),
(2,  2, 1, 'Phân công tự động theo danh mục IT'),
(3,  2, 1, 'Phân công tự động theo danh mục IT'),
(4,  2, 1, 'Phân công tự động theo danh mục IT'),
(5,  2, 1, 'Phân công tự động theo danh mục IT'),
(6,  3, 1, 'Phân công tự động theo danh mục CSVC'),
(7,  3, 1, 'Phân công tự động theo danh mục CSVC'),
(8,  3, 1, 'Phân công tự động theo danh mục CSVC'),
(9,  3, 1, 'Phân công tự động theo danh mục CSVC'),
(10, 3, 1, 'Phân công khẩn cấp – máy lạnh chảy nước'),
(11, 6, 1, 'Phân công tự động theo danh mục Đào tạo'),
(12, 6, 1, 'Phân công tự động theo danh mục Đào tạo'),
(13, 6, 1, 'Phân công tự động theo danh mục Đào tạo'),
(14, 6, 1, 'Phân công tự động theo danh mục Đào tạo'),
(15, 6, 1, 'Phân công tự động theo danh mục Đào tạo');

-- ============================================================
-- COMMENTS
-- ============================================================
INSERT INTO ticket_comments (ticket_id, user_id, content, is_internal) VALUES
(1, 4,  'Máy này hôm qua vẫn dùng bình thường, sáng nay tới lớp thì không lên được.', 0),
(1, 2,  'Đã kiểm tra, nguồn điện bình thường nhưng mainboard có vấn đề. Đang liên hệ kho lấy linh kiện.', 0),
(1, 2,  'Ghi chú nội bộ: cần đặt mua thêm mainboard H410, kho còn 0 cái.', 1),
(2, 4,  'Email reset mật khẩu em đã kiểm tra cả hòm thư rác rồi nhưng vẫn không thấy.', 0),
(2, 2,  'Bạn thử kiểm tra lại email nhập có đúng không? Email sinh viên là mssv@ischool.edu.vn', 0),
(3, 7,  'Đã thử kết nối lại nhiều lần, báo Connected nhưng không vào được internet.', 0),
(3, 2,  'Đã restart access point phòng A101, nhờ bạn kiểm tra lại xem có kết nối được chưa?', 0),
(3, 7,  'Vẫn chưa được anh ơi, vẫn báo No Internet Access.', 0),
(4, 10, 'Buổi sáng máy vẫn dùng tốt, đến buổi chiều thì bị sọc.', 0),
(4, 2,  'Đã cử kỹ thuật viên kiểm tra, phán đoán là card đồ họa máy chiếu có vấn đề. Chờ báo giá sửa.', 0),
(5, 8,  'Account em bị disable từ hôm qua. Em có deadline nộp báo cáo hôm nay qua email này ạ.', 0),
(5, 2,  'Đã kích hoạt lại. Bạn thử đăng nhập lại xem. Nguyên nhân account bị lock do nhập sai mật khẩu 5 lần.', 0),
(5, 8,  'Đăng nhập được rồi ạ. Cảm ơn anh nhiều!', 0),
(6, 5,  'Nhiệt độ phòng lên 32 độ rồi, sinh viên và giảng viên rất khó chịu.', 0),
(6, 3,  'Đã xử lý xong. Vấn đề do lọc bụi bị tắc và thiếu gas lạnh.', 0),
(10, 5, 'Nước chảy nhiều lắm, sàn trơn rất nguy hiểm.', 0),
(10, 3, 'Đã cử người lên xử lý khẩn cấp. Tạm thời tắt máy lạnh để an toàn, đặt biển cảnh báo trơn trượt.', 0),
(10, 1, 'Ghi chú: cần kiểm tra toàn bộ hệ thống máy lạnh tầng 4 định kỳ tháng tới.', 1),
(11, 4, 'Xung đột này ảnh hưởng đến cả 2 lớp, thầy giáo INS3064 và INS2010 đều không biết.', 0),
(11, 6, 'Đang làm việc với phòng quản lý phòng học để sắp xếp lại. Dự kiến có kết quả trong 24h.', 0),
(12, 9, 'Em đã đủ 120 tín chỉ, điểm trung bình 3.1, không có môn nào dưới 5.', 0),
(12, 6, 'Đã kiểm tra, môn Phương pháp nghiên cứu khoa học em chưa có điểm cuối kỳ. Đã liên hệ giảng viên cập nhật.', 0),
(14, 8, 'Nhiều bạn trong lớp không biết lịch thay đổi, lo không kịp chuẩn bị.', 0),
(14, 6, 'Đã gửi email thông báo đến toàn bộ sinh viên đăng ký môn học và cập nhật portal. Xin lỗi vì sự bất tiện.', 0);

-- ============================================================
-- ESCALATION LOGS (ticket 10 bị escalate do khẩn cấp)
-- ============================================================
INSERT INTO escalation_logs (ticket_id, escalated_by, escalated_to, reason) VALUES
(10, NULL, 1, 'Tự động escalate: máy lạnh chảy nước gây nguy hiểm, cần xử lý khẩn cấp trong 2 giờ');

-- ============================================================
-- SATISFACTION SURVEYS
-- ============================================================
INSERT INTO satisfaction_surveys (ticket_id, submitted_by, rating, comment) VALUES
(6,  5, 5, 'Xử lý rất nhanh, nhân viên nhiệt tình. Cảm ơn!'),
(7,  9, 4, 'Xử lý ổn, hơi lâu một chút nhưng kết quả tốt.'),
(5,  8, 5, 'Phản hồi rất nhanh, giải quyết đúng vấn đề. Rất hài lòng!'),
(12, 9, 4, 'Cảm ơn phòng đào tạo đã hỗ trợ, tuy nhiên mong hệ thống được cập nhật kịp thời hơn.'),
(14, 8, 3, 'Đã giải quyết nhưng việc thay đổi lịch không thông báo trước gây bất tiện rất nhiều.');

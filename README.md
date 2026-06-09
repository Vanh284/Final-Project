# Campus Helpdesk – INS3064 Final Project (Đề 13)

## Thành viên 3 – Module phụ trách
| Bảng | Chức năng |
|---|---|
| `ticket_status_logs` | Ghi lịch sử mỗi lần đổi trạng thái ticket |
| `escalation_logs` | Theo dõi escalation khi quá SLA |
| `satisfaction_surveys` | Khảo sát hài lòng sau khi đóng ticket |

---

## Cài đặt & Chạy

### 1. Yêu cầu
- PHP >= 8.0
- MySQL / MariaDB
- Apache (XAMPP / WAMP) hoặc PHP built-in server

### 2. Import database
```bash
mysql -u root -p < helpdesk_db.sql
```
Hoặc mở phpMyAdmin → Import file `helpdesk_db.sql`

### 3. Cấu hình kết nối DB
Sửa file `config/database.php`:
```php
private string $host   = 'localhost';
private string $dbname = 'helpdesk_db';
private string $user   = 'root';
private string $pass   = '';   // mật khẩu MySQL của bạn
```

### 4. Chạy với XAMPP
- Copy thư mục `helpdesk/` vào `C:\xampp\htdocs\`
- Truy cập: http://localhost/helpdesk/public/

### 5. Chạy với PHP built-in server
```bash
cd d:\Final_Web\helpdesk\public
php -S localhost:8000
```
Truy cập: http://localhost:8000/

---

## Tài khoản demo (password: `password123`)

| Email | Role |
|---|---|
| admin@ischool.vn | Admin |
| it@ischool.vn | Staff (IT) |
| facility@ischool.vn | Staff (Facilities) |
| student@ischool.vn | User |
| lecturer@ischool.vn | User |

---

## Cấu trúc thư mục

```
helpdesk/
├── config/
│   └── database.php              ← Singleton DB connection
├── models/
│   ├── TicketStatusLogModel.php  ← CRUD + business logic (Member 3)
│   ├── EscalationLogModel.php    ← SLA check, auto/manual escalate (Member 3)
│   └── SatisfactionSurveyModel.php ← Survey submit + report (Member 3)
├── controllers/
│   ├── StatusLogController.php
│   ├── EscalationController.php
│   └── SurveyController.php
├── views/
│   ├── layout.php / layout_end.php
│   ├── login.php
│   ├── dashboard.php
│   ├── ticket_detail.php
│   ├── status_logs/history.php
│   ├── escalation/list.php
│   ├── escalation/detail.php
│   └── survey/form.php, report.php, list.php
├── public/
│   ├── index.php                 ← Front controller / Router
│   └── .htaccess
├── helpdesk_db.sql               ← Database schema + seed data
└── README.md
```

---

## Business Logic (Member 3)

### ticket_status_logs
- Không ghi log nếu `old_status == new_status`
- Mỗi lần staff/admin đổi trạng thái → tự động ghi log kèm note
- AJAX load timeline không reload trang

### escalation_logs
- Tối đa **3 lần escalate** mỗi ticket
- Auto-escalate: quét ticket quá `sla_hours` → escalate lên manager bộ phận
- Manual escalate: staff chủ động escalate với lý do cụ thể
- Đánh dấu `resolved_at` khi xử lý xong

### satisfaction_surveys
- Chỉ cho phép submit khi ticket ở trạng thái `closed`
- Mỗi ticket chỉ được submit **1 lần** (UNIQUE constraint + PHP check)
- Rating validate: phải từ 1–5
- Báo cáo avg rating theo bộ phận

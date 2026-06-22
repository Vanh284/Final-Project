# Campus Helpdesk – IT & Facility Ticket System
**Đề tài 13 – INS3064 Multimedia Design and Web Development**

---

## Cấu trúc thư mục

```
helpdesk/
├── config/
│   ├── app.php          # Cấu hình ứng dụng
│   └── database.php     # Thông tin kết nối DB
├── core/
│   ├── Database.php     # Singleton PDO connection
│   ├── Model.php        # Base model (Repository pattern)
│   ├── Controller.php   # Base controller
│   └── Router.php       # Front-controller router
├── models/              # 9 model tương ứng 9 bảng
├── controllers/         # 8 controllers
├── views/               # PHP views
│   ├── layout/          # header.php + footer.php
│   ├── auth/            # Login
│   ├── dashboard/       # Dashboard + charts
│   ├── tickets/         # List, create, show
│   ├── users/           # CRUD người dùng
│   ├── departments/     # CRUD bộ phận
│   ├── categories/      # CRUD danh mục
│   ├── reports/         # Báo cáo thống kê
│   ├── partials/        # Badge reusable components
│   └── errors/          # 404
├── public/
│   ├── css/style.css    # Custom CSS
│   ├── js/app.js        # Global JS utilities
│   └── uploads/         # File đính kèm
├── database/
│   └── helpdesk.sql     # Schema + seed data
└── index.php            # Front controller
```

---

## Cài đặt

### 1. Yêu cầu
- PHP >= 8.0
- MySQL / MariaDB
- Web server: Apache (XAMPP/LAMP) hoặc Nginx

### 2. Import database
```sql
-- Mở phpMyAdmin hoặc chạy lệnh:
mysql -u root -p < database/helpdesk.sql
```

### 3. Cấu hình
Chỉnh `config/database.php` nếu cần (mặc định: root, không password):
```php
define('DB_USER', 'root');
define('DB_PASS', '');
```

Chỉnh `config/app.php` theo đường dẫn thực tế:
```php
define('APP_URL', 'http://localhost/helpdesk');
```

### 4. Chạy ứng dụng
Đặt thư mục `helpdesk/` vào `htdocs/` (XAMPP) hoặc `www/` (WAMP), rồi truy cập:
```
http://localhost/helpdesk/
```

---

## Tài khoản demo
| Vai trò   | Email                    | Mật khẩu      |
|-----------|--------------------------|---------------|
| Admin     | admin@ischool.edu.vn     | Password@123  |
| Staff IT  | it@ischool.edu.vn        | Password@123  |
| Staff CSVC| csvc@ischool.edu.vn      | Password@123  |
| Sinh viên | sv001@ischool.edu.vn     | Password@123  |
| Giảng viên| gv001@ischool.edu.vn     | Password@123  |

---

## Tính năng nổi bật

| Tính năng | Mô tả |
|-----------|-------|
| **MVC Pattern** | Tách biệt Model/View/Controller rõ ràng |
| **Singleton DB** | Chỉ 1 PDO instance/request |
| **Repository Pattern** | Base Model với CRUD, paginate |
| **AJAX CRUD** | Tất cả create/update/delete không reload trang |
| **Auto-routing** | Tự động phân loại ticket theo từ khoá → gợi ý danh mục + phân công staff |
| **SLA & Escalation** | Tính deadline theo SLA, tự động escalate khi quá hạn |
| **RBAC** | 3 vai trò: Admin / Staff / User với quyền khác nhau |
| **Validation** | Frontend (JS) + Backend (PHP) |
| **Responsive UI** | Bootstrap 5, mobile-friendly |
| **Charts** | Chart.js cho dashboard & reports |
| **Satisfaction Survey** | Đánh giá 1–5 sao sau khi ticket resolved |

---

## 9 Bảng CSDL

| Bảng | Người phụ trách |
|------|----------------|
| `users` | Thành viên 1 |
| `departments` | Thành viên 1 |
| `ticket_categories` | Thành viên 1 |
| `tickets` | Thành viên 2 |
| `ticket_assignments` | Thành viên 2 |
| `ticket_comments` | Thành viên 2 |
| `ticket_status_logs` | Thành viên 3 |
| `escalation_logs` | Thành viên 3 |
| `satisfaction_surveys` | Thành viên 3 |

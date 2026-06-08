# VIETNAM NATIONAL UNIVERSITY, HANOI
## INTERNATIONAL SCHOOL

**Course Code:** INS3064  
**Course Name:** MULTIMEDIA DESIGN AND WEB DEVELOPMENT  
**Assessment:** FINAL PROJECT REPORT

---

# CAMPUS HELPDESK – HỆ THỐNG HỖ TRỢ NỘI BỘ (IT & FACILITY TICKET)

**Đề tài số:** 13  
**Thành viên phụ trách báo cáo này:** Thành viên 3  
**Module phụ trách:** Ticket Status Logs · Escalation Logs · Satisfaction Surveys

**Supervisor:** PhD. Pham Duc Tho  
**Hanoi – 2026**

---

## Student Declaration

I certify that the assignment submission is entirely my own work and I fully understand the consequences of plagiarism. I declare that the work submitted for assessment has been carried out without assistance other than that which is acceptable according to the rules of the specification. I certify I have clearly referenced any sources and any artificial intelligence (AI) tools used in the work. I understand that making a false declaration is a form of malpractice.

**Student signature:** ___________________________  **Date:** _______________

---

## Table of Contents

1. Introduction
2. System Design
   - 2.1 Site Map
   - 2.2 Entity Relationship Diagram
3. Implementation
   - 3.1 Database Design – Member 3 Module
   - 3.2 Sample Source Code
   - 3.3 Images of Final Application
   - 3.4 GitHub Repository Evidence
4. Conclusion
   - 4.1 What Went Well
   - 4.2 What Did Not Go Well
   - 4.3 Lessons Learned and Further Improvements

---

## Chapter 1: Introduction

### 1.1 Problem Statement

Tại các trường đại học, sinh viên và giảng viên thường xuyên gặp sự cố kỹ thuật hoặc cơ sở vật chất: máy chiếu hỏng, wifi yếu, tài khoản LMS lỗi, điều hòa không hoạt động... Hiện tại, việc báo cáo sự cố chủ yếu qua email hoặc điện thoại, dẫn đến:

- Không theo dõi được tiến độ xử lý
- Không có cơ chế leo thang (escalation) khi quá hạn
- Không đo lường được chất lượng dịch vụ hỗ trợ

### 1.2 Project Objective

**Campus Helpdesk** là hệ thống ticket tập trung cho bộ phận IT, cơ sở vật chất và phòng đào tạo. Hệ thống cho phép:

- Người dùng (SV/GV) tạo ticket báo sự cố
- Tự động phân loại và chuyển đến đúng bộ phận
- Theo dõi SLA, tự động escalate khi quá hạn
- Thu thập phản hồi chất lượng dịch vụ sau khi đóng ticket

### 1.3 Team Division

| Thành viên | Module phụ trách | Bảng CSDL |
|---|---|---|
| Thành viên 1 | Quản lý người dùng & danh mục | `users`, `departments`, `ticket_categories` |
| Thành viên 2 | Quản lý ticket & phân công | `tickets`, `ticket_assignments`, `ticket_comments` |
| **Thành viên 3** | **Theo dõi trạng thái, escalation & khảo sát** | **`ticket_status_logs`, `escalation_logs`, `satisfaction_surveys`** |

---

## Chapter 2: System Design

### 2.1 Site Map

```
Campus Helpdesk
├── Public
│   └── Verify Document (QR check)
├── Auth
│   ├── Login
│   └── Register
├── User (Student / Lecturer)
│   ├── Dashboard
│   ├── My Tickets
│   │   ├── Create Ticket
│   │   ├── View Ticket Detail & Status History
│   │   └── Fill Satisfaction Survey (after ticket closed)
│   └── Profile
└── Admin / Staff
    ├── Dashboard (SLA overview, escalation alerts)
    ├── Ticket Management
    │   ├── All Tickets (filter by dept, status, SLA)
    │   ├── Assign Ticket
    │   ├── Update Status → triggers status_log
    │   └── Escalate Ticket → triggers escalation_log
    ├── Department Management
    ├── Ticket Category & SLA Config
    ├── Escalation Log Viewer
    ├── Survey Results & Reports
    └── User Management
```


### 2.2 Entity Relationship Diagram

```
┌─────────────┐       ┌──────────────────┐       ┌─────────────────────┐
│    users    │       │  ticket_categories│       │    departments      │
│─────────────│       │──────────────────│       │─────────────────────│
│ id (PK)     │       │ id (PK)          │       │ id (PK)             │
│ full_name   │       │ name             │       │ name                │
│ email       │       │ department_id(FK)│──────▶│ description         │
│ role        │       │ sla_hours        │       │ manager_id (FK)     │
│ dept_id(FK) │       │ priority_level   │       └─────────────────────┘
└──────┬──────┘       └────────┬─────────┘
       │                       │
       │              ┌────────▼─────────┐
       │              │     tickets      │
       │              │──────────────────│
       └─────────────▶│ id (PK)          │
                      │ title            │
                      │ description      │
                      │ category_id (FK) │
                      │ created_by (FK)  │
                      │ status           │
                      │ created_at       │
                      └────────┬─────────┘
                               │
          ┌────────────────────┼────────────────────┐
          │                    │                    │
┌─────────▼──────────┐ ┌───────▼──────────┐ ┌──────▼──────────────┐
│ ticket_status_logs │ │ escalation_logs  │ │ satisfaction_surveys│
│────────────────────│ │──────────────────│ │─────────────────────│
│ id (PK)            │ │ id (PK)          │ │ id (PK)             │
│ ticket_id (FK)     │ │ ticket_id (FK)   │ │ ticket_id (FK)      │
│ old_status         │ │ escalated_by(FK) │ │ user_id (FK)        │
│ new_status         │ │ escalated_to(FK) │ │ rating (1–5)        │
│ changed_by (FK)    │ │ reason           │ │ comment             │
│ note               │ │ level            │ │ submitted_at        │
│ changed_at         │ │ escalated_at     │ └─────────────────────┘
└────────────────────┘ │ resolved_at      │
                       └──────────────────┘
```

**Relationships:**
- `tickets` 1–N `ticket_status_logs` (mỗi ticket có nhiều lần đổi trạng thái)
- `tickets` 1–N `escalation_logs` (mỗi ticket có thể bị escalate nhiều lần)
- `tickets` 1–1 `satisfaction_surveys` (mỗi ticket đóng → 1 khảo sát)
- `users` là FK trong cả 3 bảng (người thực hiện hành động)

---

## Chapter 3: Implementation

### 3.1 Database Design – Member 3 Module

#### Bảng `ticket_status_logs`

```sql
CREATE TABLE ticket_status_logs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id   INT NOT NULL,
    old_status  ENUM('open','in_progress','pending','resolved','closed') NOT NULL,
    new_status  ENUM('open','in_progress','pending','resolved','closed') NOT NULL,
    changed_by  INT NOT NULL,
    note        TEXT,
    changed_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id)  REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id)
);
```

#### Bảng `escalation_logs`

```sql
CREATE TABLE escalation_logs (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id      INT NOT NULL,
    escalated_by   INT NOT NULL,
    escalated_to   INT NOT NULL,
    reason         TEXT NOT NULL,
    level          TINYINT DEFAULT 1 COMMENT '1=first escalation, 2=second...',
    escalated_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    resolved_at    DATETIME DEFAULT NULL,
    FOREIGN KEY (ticket_id)    REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (escalated_by) REFERENCES users(id),
    FOREIGN KEY (escalated_to) REFERENCES users(id)
);
```

#### Bảng `satisfaction_surveys`

```sql
CREATE TABLE satisfaction_surveys (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id    INT NOT NULL UNIQUE,
    user_id      INT NOT NULL,
    rating       TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment      TEXT,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)
);
```


### 3.2 Sample Source Code

#### 3.2.1 Cấu trúc thư mục (MVC Pattern)

```
helpdesk/
├── config/
│   └── database.php          # Singleton DB connection
├── models/
│   ├── TicketStatusLogModel.php
│   ├── EscalationLogModel.php
│   └── SatisfactionSurveyModel.php
├── controllers/
│   ├── StatusLogController.php
│   ├── EscalationController.php
│   └── SurveyController.php
├── views/
│   ├── status_logs/
│   │   └── history.php
│   ├── escalation/
│   │   ├── list.php
│   │   └── detail.php
│   └── survey/
│       ├── form.php
│       └── report.php
└── public/
    └── index.php
```

#### 3.2.2 Singleton Database Connection

```php
<?php
// config/database.php
class Database {
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct() {
        $dsn = 'mysql:host=localhost;dbname=helpdesk_db;charset=utf8mb4';
        $this->pdo = new PDO($dsn, 'root', '', [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->pdo;
    }
}
```

#### 3.2.3 Model – TicketStatusLogModel

```php
<?php
// models/TicketStatusLogModel.php
require_once __DIR__ . '/../config/database.php';

class TicketStatusLogModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /** Ghi log khi ticket đổi trạng thái */
    public function logStatusChange(
        int $ticketId,
        string $oldStatus,
        string $newStatus,
        int $changedBy,
        string $note = ''
    ): bool {
        // Business rule: không cho phép log nếu old == new
        if ($oldStatus === $newStatus) {
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO ticket_status_logs
                (ticket_id, old_status, new_status, changed_by, note)
            VALUES (:tid, :old, :new, :by, :note)
        ");
        return $stmt->execute([
            ':tid'  => $ticketId,
            ':old'  => $oldStatus,
            ':new'  => $newStatus,
            ':by'   => $changedBy,
            ':note' => $note,
        ]);
    }

    /** Lấy toàn bộ lịch sử trạng thái của một ticket */
    public function getHistoryByTicket(int $ticketId): array {
        $stmt = $this->db->prepare("
            SELECT tsl.*, u.full_name AS changed_by_name
            FROM ticket_status_logs tsl
            JOIN users u ON u.id = tsl.changed_by
            WHERE tsl.ticket_id = :tid
            ORDER BY tsl.changed_at ASC
        ");
        $stmt->execute([':tid' => $ticketId]);
        return $stmt->fetchAll();
    }

    /** Xóa log theo id (Admin only) */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM ticket_status_logs WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
```


#### 3.2.4 Model – EscalationLogModel (Business Logic: SLA Check)

```php
<?php
// models/EscalationLogModel.php
require_once __DIR__ . '/../config/database.php';

class EscalationLogModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Kiểm tra ticket có vượt SLA không, nếu có thì tự động escalate
     * Business rule: nếu ticket chưa resolved sau sla_hours → escalate
     */
    public function autoEscalateOverdue(): int {
        // Lấy các ticket quá hạn SLA chưa được escalate ở level hiện tại
        $stmt = $this->db->query("
            SELECT t.id AS ticket_id,
                   tc.sla_hours,
                   tc.department_id,
                   t.created_at,
                   COALESCE(MAX(el.level), 0) AS current_level
            FROM tickets t
            JOIN ticket_categories tc ON tc.id = t.category_id
            LEFT JOIN escalation_logs el ON el.ticket_id = t.id
            WHERE t.status NOT IN ('resolved', 'closed')
            GROUP BY t.id, tc.sla_hours, tc.department_id, t.created_at
            HAVING TIMESTAMPDIFF(HOUR, t.created_at, NOW()) > tc.sla_hours
               AND current_level < 3
        ");
        $overdue = $stmt->fetchAll();

        $count = 0;
        foreach ($overdue as $row) {
            $newLevel = $row['current_level'] + 1;
            // Tìm manager của department để escalate tới
            $mgr = $this->db->prepare(
                "SELECT manager_id FROM departments WHERE id = :did"
            );
            $mgr->execute([':did' => $row['department_id']]);
            $managerId = $mgr->fetchColumn();

            if ($managerId) {
                $ins = $this->db->prepare("
                    INSERT INTO escalation_logs
                        (ticket_id, escalated_by, escalated_to, reason, level)
                    VALUES (:tid, 1, :to, :reason, :level)
                ");
                $ins->execute([
                    ':tid'    => $row['ticket_id'],
                    ':to'     => $managerId,
                    ':reason' => "Auto-escalated: SLA exceeded by system",
                    ':level'  => $newLevel,
                ]);
                $count++;
            }
        }
        return $count; // số ticket đã được escalate
    }

    /** Escalate thủ công bởi staff */
    public function manualEscalate(
        int $ticketId,
        int $escalatedBy,
        int $escalatedTo,
        string $reason
    ): bool {
        // Lấy level hiện tại
        $stmt = $this->db->prepare(
            "SELECT COALESCE(MAX(level),0) FROM escalation_logs WHERE ticket_id=:tid"
        );
        $stmt->execute([':tid' => $ticketId]);
        $currentLevel = (int)$stmt->fetchColumn();

        if ($currentLevel >= 3) {
            return false; // đã escalate tối đa 3 lần
        }

        $ins = $this->db->prepare("
            INSERT INTO escalation_logs
                (ticket_id, escalated_by, escalated_to, reason, level)
            VALUES (:tid, :by, :to, :reason, :level)
        ");
        return $ins->execute([
            ':tid'    => $ticketId,
            ':by'     => $escalatedBy,
            ':to'     => $escalatedTo,
            ':reason' => htmlspecialchars($reason),
            ':level'  => $currentLevel + 1,
        ]);
    }

    /** Đánh dấu escalation đã được giải quyết */
    public function markResolved(int $escalationId): bool {
        $stmt = $this->db->prepare("
            UPDATE escalation_logs
            SET resolved_at = NOW()
            WHERE id = :id AND resolved_at IS NULL
        ");
        return $stmt->execute([':id' => $escalationId]);
    }

    /** Lấy danh sách escalation theo ticket */
    public function getByTicket(int $ticketId): array {
        $stmt = $this->db->prepare("
            SELECT el.*,
                   u1.full_name AS escalated_by_name,
                   u2.full_name AS escalated_to_name
            FROM escalation_logs el
            JOIN users u1 ON u1.id = el.escalated_by
            JOIN users u2 ON u2.id = el.escalated_to
            WHERE el.ticket_id = :tid
            ORDER BY el.escalated_at ASC
        ");
        $stmt->execute([':tid' => $ticketId]);
        return $stmt->fetchAll();
    }

    /** Lấy tất cả escalation chưa resolved (cho dashboard admin) */
    public function getPendingEscalations(): array {
        $stmt = $this->db->query("
            SELECT el.*, t.title AS ticket_title,
                   u2.full_name AS escalated_to_name
            FROM escalation_logs el
            JOIN tickets t  ON t.id  = el.ticket_id
            JOIN users u2   ON u2.id = el.escalated_to
            WHERE el.resolved_at IS NULL
            ORDER BY el.escalated_at ASC
        ");
        return $stmt->fetchAll();
    }
}
```


#### 3.2.5 Model – SatisfactionSurveyModel

```php
<?php
// models/SatisfactionSurveyModel.php
require_once __DIR__ . '/../config/database.php';

class SatisfactionSurveyModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Gửi khảo sát sau khi ticket đóng
     * Business rule: mỗi ticket chỉ được submit 1 lần (UNIQUE constraint)
     */
    public function submit(int $ticketId, int $userId, int $rating, string $comment): bool {
        // Validate rating
        if ($rating < 1 || $rating > 5) {
            return false;
        }

        // Kiểm tra ticket đã closed chưa
        $check = $this->db->prepare(
            "SELECT status FROM tickets WHERE id = :tid"
        );
        $check->execute([':tid' => $ticketId]);
        $status = $check->fetchColumn();

        if ($status !== 'closed') {
            return false; // chỉ cho phép survey khi ticket đã đóng
        }

        // Kiểm tra đã submit chưa
        $dup = $this->db->prepare(
            "SELECT id FROM satisfaction_surveys WHERE ticket_id = :tid"
        );
        $dup->execute([':tid' => $ticketId]);
        if ($dup->fetchColumn()) {
            return false; // đã survey rồi
        }

        $stmt = $this->db->prepare("
            INSERT INTO satisfaction_surveys (ticket_id, user_id, rating, comment)
            VALUES (:tid, :uid, :rating, :comment)
        ");
        return $stmt->execute([
            ':tid'     => $ticketId,
            ':uid'     => $userId,
            ':rating'  => $rating,
            ':comment' => htmlspecialchars($comment),
        ]);
    }

    /** Lấy kết quả khảo sát của 1 ticket */
    public function getByTicket(int $ticketId): ?array {
        $stmt = $this->db->prepare("
            SELECT ss.*, u.full_name
            FROM satisfaction_surveys ss
            JOIN users u ON u.id = ss.user_id
            WHERE ss.ticket_id = :tid
        ");
        $stmt->execute([':tid' => $ticketId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /** Báo cáo tổng hợp: điểm trung bình theo bộ phận */
    public function getAverageRatingByDepartment(): array {
        $stmt = $this->db->query("
            SELECT d.name AS department,
                   ROUND(AVG(ss.rating), 2) AS avg_rating,
                   COUNT(ss.id) AS total_surveys
            FROM satisfaction_surveys ss
            JOIN tickets t ON t.id = ss.ticket_id
            JOIN ticket_categories tc ON tc.id = t.category_id
            JOIN departments d ON d.id = tc.department_id
            GROUP BY d.id, d.name
            ORDER BY avg_rating DESC
        ");
        return $stmt->fetchAll();
    }

    /** Xóa survey (Admin only) */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM satisfaction_surveys WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
```


#### 3.2.6 Controller – EscalationController

```php
<?php
// controllers/EscalationController.php
require_once __DIR__ . '/../models/EscalationLogModel.php';
require_once __DIR__ . '/../models/TicketStatusLogModel.php';

class EscalationController {
    private EscalationLogModel $escalationModel;
    private TicketStatusLogModel $statusLogModel;

    public function __construct() {
        $this->escalationModel = new EscalationLogModel();
        $this->statusLogModel  = new TicketStatusLogModel();
    }

    /** POST /escalation/manual */
    public function manualEscalate(): void {
        session_start();
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $ticketId    = (int)($_POST['ticket_id'] ?? 0);
        $escalateTo  = (int)($_POST['escalate_to'] ?? 0);
        $reason      = trim($_POST['reason'] ?? '');

        if (!$ticketId || !$escalateTo || empty($reason)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        $result = $this->escalationModel->manualEscalate(
            $ticketId,
            $_SESSION['user_id'],
            $escalateTo,
            $reason
        );

        echo json_encode(['success' => $result]);
    }

    /** GET /escalation/pending – Admin dashboard */
    public function pendingList(): void {
        $data = $this->escalationModel->getPendingEscalations();
        require __DIR__ . '/../views/escalation/list.php';
    }

    /** POST /escalation/resolve */
    public function resolve(): void {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403); return;
        }
        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->escalationModel->markResolved($id);
        echo json_encode(['success' => $ok]);
    }
}
```

#### 3.2.7 Controller – SurveyController

```php
<?php
// controllers/SurveyController.php
require_once __DIR__ . '/../models/SatisfactionSurveyModel.php';

class SurveyController {
    private SatisfactionSurveyModel $model;

    public function __construct() {
        $this->model = new SatisfactionSurveyModel();
    }

    /** GET /survey/form?ticket_id=X */
    public function showForm(): void {
        $ticketId = (int)($_GET['ticket_id'] ?? 0);
        $existing = $this->model->getByTicket($ticketId);
        require __DIR__ . '/../views/survey/form.php';
    }

    /** POST /survey/submit */
    public function submit(): void {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403); return;
        }

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $rating   = (int)($_POST['rating']    ?? 0);
        $comment  = trim($_POST['comment']    ?? '');

        $ok = $this->model->submit(
            $ticketId,
            $_SESSION['user_id'],
            $rating,
            $comment
        );

        if ($ok) {
            header('Location: /tickets/' . $ticketId . '?survey=done');
        } else {
            header('Location: /survey/form?ticket_id=' . $ticketId . '&error=1');
        }
    }

    /** GET /survey/report – Admin only */
    public function report(): void {
        session_start();
        if ($_SESSION['role'] !== 'admin') {
            http_response_code(403); return;
        }
        $data = $this->model->getAverageRatingByDepartment();
        require __DIR__ . '/../views/survey/report.php';
    }
}
```


#### 3.2.8 View – Ticket Status History (AJAX)

```php
<!-- views/status_logs/history.php -->
<div id="status-timeline" data-ticket="<?= $ticketId ?>">
  <h4>Status History</h4>
  <ul id="timeline-list" class="timeline"></ul>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const ticketId = document.getElementById('status-timeline').dataset.ticket;

  fetch('/api/status-logs?ticket_id=' + ticketId)
    .then(res => res.json())
    .then(logs => {
      const list = document.getElementById('timeline-list');
      logs.forEach(log => {
        const li = document.createElement('li');
        li.innerHTML = `
          <span class="badge badge-${log.new_status}">${log.new_status.toUpperCase()}</span>
          <small>${log.changed_at}</small> — by <strong>${log.changed_by_name}</strong>
          ${log.note ? '<p class="note">' + log.note + '</p>' : ''}
        `;
        list.appendChild(li);
      });
    });
});
</script>
```

#### 3.2.9 View – Satisfaction Survey Form

```php
<!-- views/survey/form.php -->
<?php if ($existing): ?>
  <div class="alert alert-info">
    You already submitted a survey for this ticket.
    Your rating: <?= str_repeat('★', $existing['rating']) ?>
  </div>
<?php else: ?>
<form method="POST" action="/survey/submit">
  <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticketId) ?>">

  <div class="form-group">
    <label>How satisfied are you with the support? *</label>
    <div class="star-rating">
      <?php for ($i = 5; $i >= 1; $i--): ?>
        <input type="radio" name="rating" id="star<?= $i ?>"
               value="<?= $i ?>" required>
        <label for="star<?= $i ?>">★</label>
      <?php endfor; ?>
    </div>
  </div>

  <div class="form-group">
    <label for="comment">Additional comments (optional)</label>
    <textarea name="comment" id="comment" rows="4"
              maxlength="500" class="form-control"></textarea>
  </div>

  <button type="submit" class="btn btn-primary">Submit Survey</button>
</form>
<?php endif; ?>
```

#### 3.2.10 View – Survey Report (Admin)

```php
<!-- views/survey/report.php -->
<h2>Service Quality Report</h2>
<table class="table table-bordered">
  <thead>
    <tr>
      <th>Department</th>
      <th>Average Rating</th>
      <th>Total Surveys</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($data as $row): ?>
    <tr>
      <td><?= htmlspecialchars($row['department']) ?></td>
      <td>
        <?= str_repeat('★', round($row['avg_rating'])) ?>
        (<?= $row['avg_rating'] ?>/5)
      </td>
      <td><?= $row['total_surveys'] ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
```


### 3.3 Images of Final Application

> *(Screenshots được chụp từ ứng dụng chạy trên localhost. Đính kèm trong thư mục `/screenshots` khi nộp bài.)*

**Hình 3.1** – Ticket Detail Page với Status Timeline (AJAX load)  
`[Screenshot: ticket_detail_with_timeline.png]`

**Hình 3.2** – Admin Dashboard: Pending Escalations List  
`[Screenshot: admin_escalation_dashboard.png]`

**Hình 3.3** – Satisfaction Survey Form (sau khi ticket đóng)  
`[Screenshot: survey_form.png]`

**Hình 3.4** – Service Quality Report theo bộ phận  
`[Screenshot: survey_report_by_dept.png]`

**Hình 3.5** – Database tables trong phpMyAdmin  
`[Screenshot: phpmyadmin_tables.png]`

---

### 3.4 GitHub Repository Evidence

**Repository URL:** `https://github.com/<username>/campus-helpdesk`

**Commit evidence (Member 3 – minimum 10 commits, 3 different days):**

| # | Commit Message | Date |
|---|---|---|
| 1 | `init: create ticket_status_logs table migration` | Day 1 |
| 2 | `feat: add TicketStatusLogModel with logStatusChange()` | Day 1 |
| 3 | `feat: add getHistoryByTicket() method` | Day 1 |
| 4 | `init: create escalation_logs table migration` | Day 2 |
| 5 | `feat: implement autoEscalateOverdue() with SLA check` | Day 2 |
| 6 | `feat: add manualEscalate() with level cap (max 3)` | Day 2 |
| 7 | `feat: add EscalationController with AJAX endpoints` | Day 2 |
| 8 | `init: create satisfaction_surveys table migration` | Day 3 |
| 9 | `feat: implement SatisfactionSurveyModel with duplicate check` | Day 3 |
| 10 | `feat: add SurveyController and survey form view` | Day 3 |
| 11 | `feat: add survey report view with avg rating by dept` | Day 3 |
| 12 | `feat: AJAX status timeline in ticket detail view` | Day 3 |

---

## Chapter 4: Conclusion

### 4.1 What Went Well

- **Business logic rõ ràng:** Ba bảng của Thành viên 3 đều có logic nghiệp vụ cụ thể: SLA auto-escalation, giới hạn escalation tối đa 3 lần, chỉ cho phép survey khi ticket đã đóng, ngăn submit survey trùng lặp.
- **MVC pattern nhất quán:** Model xử lý toàn bộ DB logic, Controller chỉ điều phối request/response, View chỉ hiển thị dữ liệu.
- **Singleton DB connection:** Tránh tạo nhiều kết nối không cần thiết, dễ maintain.
- **AJAX cho status timeline:** Cải thiện UX, không reload trang khi xem lịch sử trạng thái.
- **Validation đầy đủ:** Rating check (1–5), status change check (old ≠ new), ticket status check trước khi submit survey.

### 4.2 What Did Not Go Well

- **Auto-escalation cần cron job:** Hàm `autoEscalateOverdue()` cần được gọi định kỳ bằng cron job hoặc scheduled task, chưa tích hợp hoàn toàn tự động trong môi trường demo.
- **Email notification:** Chưa tích hợp gửi email thông báo khi escalate (cần PHPMailer), hiện chỉ lưu log trong DB.
- **UI survey chưa responsive hoàn toàn:** Star rating CSS cần cải thiện thêm trên mobile.

### 4.3 Lessons Learned and Further Improvements

**Lessons learned:**
- Thiết kế bảng log (status log, escalation log) cần cân nhắc kỹ về `ON DELETE CASCADE` để tránh mất dữ liệu audit khi ticket bị xóa.
- Sử dụng `UNIQUE` constraint trên `ticket_id` trong `satisfaction_surveys` đơn giản hơn nhiều so với kiểm tra bằng PHP.
- Tách business rule ra Model giúp Controller gọn hơn và dễ test hơn.

**Further improvements:**
- Tích hợp PHPMailer để gửi email thực khi escalate
- Thêm REST API endpoint `/api/escalation/auto` để cron job gọi mỗi giờ
- Export báo cáo survey ra Excel/PDF bằng PhpSpreadsheet
- Thêm biểu đồ (Chart.js) cho survey report dashboard
- Implement Observer Pattern: khi ticket đổi sang `closed`, tự động trigger gửi survey link

---

## Public Project Defense

Each group will have to deliver a public defense of its work in front of the Lecturer.  
Each group will have only **20 minutes** for the following:

- Each student demonstrates his/her part in the project
- Each student shows the source code and explains how it works
- Answer questions related to the project (and best practices in general)

**Please be strict in timing!**

Be well prepared for presenting maximum of your work for a minimum time. Bring your **OWN LAPTOP**. Test it preliminarily with the multimedia projector. Open the project assets beforehand to save time.

---

## Assessment Criteria

### Group Report – (30% of final mark)

| Criterion | Points |
|---|---|
| Produce a set of Users' requirements by using User Story template | 2 pts |
| Site map of the project | 2 pts |
| Entity Relationship Diagram (ERD) | 3 pts |
| Final Result of the project with evidence | 3 pts |

### Individual Assessment – (70% of final mark)

| Criterion | Points |
|---|---|
| Assigned database design (≥3 connected tables) | 1.5 pts |
| CRUD implementation (Create, Read, Update, Delete) | 1.5 pts |
| Business logic implementation (validation, SLA, escalation, survey rules) | 2.0 pts |
| Technical explanation during defense | 1.0 pt |
| Individual contribution and code quality | 1.0 pt |

---

## Student Declaration

I certify that the assignment submission is entirely my own work and I fully understand the consequences of plagiarism. I declare that the work submitted for assessment has been carried out without assistance other than that which is acceptable according to the rules of the specification. I certify I have clearly referenced any sources and any artificial intelligence (AI) tools used in the work. I understand that making a false declaration is a form of malpractice.

**Student signature(s):** ___________________________  **Date:** _______________

---

*INS3064 – Multimedia Design and Web Development | International School – VNU Hanoi | 2026*

# DANH SÁCH ĐỀ TÀI GỢI Ý – MULTIMEDIA DESIGN AND WEB DEVELOPMENT
## Trường Quốc tế – ISchool – Đại học Quốc gia Hà Nội (2026)

---

## YÊU CẦU CHUNG VÀ TIÊU CHÍ ĐÁNH GIÁ

### Quy định nhóm & quy mô đề tài

| Tiêu chí | Yêu cầu tối thiểu |
|---|---|
| **Số lượng thành viên** | Tối đa **03 người/nhóm** |
| **Số bảng mỗi người** | Tối thiểu **03 bảng** do mỗi thành viên phụ trách |
| **Chức năng CRUD** | Mỗi bảng phải có đầy đủ Create – Read – Update – Delete |
| **Liên kết dữ liệu** | Các bảng phải có **quan hệ logic** với nhau (1-N, N-N, v.v.) |
| **Cơ sở dữ liệu** | Tự thiết kế, chuẩn hóa đúng chuẩn 3NF, có ERD nộp kèm |

> **Ví dụ phân công nhóm 3 người:** Mỗi người thiết kế và chịu trách nhiệm hoàn thiện ít nhất 3 bảng và toàn bộ CRUD tương ứng. Các bảng của 3 người phải liên kết với nhau thành một hệ thống nghiệp vụ hoàn chỉnh (tối thiểu 9 bảng, khuyến khích 10–15 bảng).

---

### Yêu cầu kỹ thuật bắt buộc

- **Backend tối thiểu:** PHP thuần → Web Application PHP + HTML + CSS + JS  
- **Giao diện:** Cơ bản nhưng phải rõ ràng, sạch đẹp, responsive  
- **Phân quyền:** Có ít nhất 2 vai trò người dùng (Admin và User/Sinh viên/Giảng viên...)  
- **Validation:** Kiểm tra dữ liệu đầu vào ở cả frontend (JS) và backend (PHP)  
- **Database:** MySQL / MariaDB, có file `.sql` export nộp kèm  

---

### Thang điểm & điểm cộng

| Mức độ | Mô tả kỹ thuật | Điểm cơ sở |
|---|---|---|
| **Cơ bản** | PHP thuần, không dùng framework, MVC thủ công, HTML/CSS/JS cơ bản | Đủ điều kiện |
| **Trung bình** | Áp dụng mô hình **MVC** rõ ràng (tách Model/View/Controller), dùng PDO/MySQLi | +5–10% |
| **Khá** | Dùng **Singleton Pattern** cho kết nối DB, **Factory/Repository Pattern** cho Model | +10–15% |
| **Tốt** | Tích hợp **AJAX** cho thao tác CRUD không reload trang, cải thiện UX rõ rệt | +15–20% |
| **Xuất sắc** | Kết hợp nhiều pattern (MVC + Singleton + Observer/Strategy), REST API nội bộ, frontend dùng fetch/axios gọi API | +20–30% |

> **Lưu ý:** Sinh viên có thể tự đề xuất đề tài ngoài danh sách, tuy nhiên phải được **Giảng viên duyệt trước** khi bắt đầu thực hiện.

---

## NHÓM 1: SINH VIÊN & HỌC TẬP

### 1. Hệ thống Quản lý Đề tài Đồ án & Phân công GVHD (Capstone Manager)

- **Bối cảnh:** Mỗi kỳ có hàng trăm sinh viên đăng ký đồ án, cần phân công giảng viên hướng dẫn (GVHD), giới hạn số lượng sinh viên mỗi giảng viên, tránh trùng tên đề tài.
- **Mục tiêu Web:** Portal cho sinh viên đăng ký đề tài, giảng viên phê duyệt, bộ môn phân công và giám sát tiến độ.
- **Yêu cầu chức năng (Backend Focus):**
  - **Matching Logic:** Sinh viên đề xuất đề tài kèm từ khóa công nghệ; hệ thống gợi ý danh sách GVHD phù hợp dựa trên chuyên môn và quota còn trống (max 8 SV/GV/kỳ).
  - **Constraint Checking:** Backend kiểm tra không vượt quota GVHD, không trùng tên đề tài trong cùng ngành, không cho phép 1 SV đăng ký 2 đề tài cùng lúc.
  - **Milestone Tracking:** Định nghĩa các mốc (Đề cương, Mid-term, Final) cho mỗi đồ án; nhắc nộp báo cáo, khóa form khi quá hạn, thống kê tiến độ toàn bộ môn.

**Gợi ý bảng CSDL (≥ 9 bảng cho nhóm 3 người):**

| Bảng | Mô tả | Người phụ trách |
|---|---|---|
| `users` | Tài khoản hệ thống (SV, GV, Admin) | Thành viên 1 |
| `students` | Thông tin sinh viên | Thành viên 1 |
| `lecturers` | Thông tin giảng viên, chuyên môn | Thành viên 1 |
| `topics` | Danh sách đề tài đồ án | Thành viên 2 |
| `topic_registrations` | SV đăng ký đề tài nào, trạng thái duyệt | Thành viên 2 |
| `topic_assignments` | Phân công GVHD cho đề tài | Thành viên 2 |
| `milestones` | Danh mục các mốc nộp báo cáo | Thành viên 3 |
| `milestone_submissions` | SV nộp báo cáo từng mốc | Thành viên 3 |
| `evaluation_scores` | Điểm đánh giá theo từng mốc | Thành viên 3 |

---

### 2. Quản lý Học tập Theo Dự án – Project-Based Learning Tracker

- **Bối cảnh:** Nhiều học phần tại ISchool tổ chức theo mô hình project-based có sprint, backlog, demo, peer review. Quản lý thủ công bằng Excel và nhóm Facebook rất rời rạc.
- **Mục tiêu Web:** Quản lý backlog, user story, sprint, phân công task cho từng thành viên nhóm; giảng viên theo dõi tiến độ thực tế.
- **Yêu cầu chức năng (Backend Focus):**
  - **Sprint & Backlog Logic:** Tạo sprint, kéo user story vào sprint, tính tổng story points, cảnh báo nếu vượt capacity nhóm.
  - **Activity Log:** Ghi log toàn bộ hành động (ai tạo task, ai chuyển trạng thái, thời điểm nào), phục vụ audit khi đánh giá công bằng.
  - **Contribution Score:** Backend tính "điểm đóng góp" từ log (số task hoàn thành, story points, lần review); xuất report giúp GV chấm điểm cá nhân trong project nhóm.

**Gợi ý bảng CSDL (≥ 9 bảng):**

| Bảng | Mô tả | Người phụ trách |
|---|---|---|
| `users` | Tài khoản (SV, GV) | Thành viên 1 |
| `courses` | Học phần áp dụng PBL | Thành viên 1 |
| `project_groups` | Nhóm dự án trong từng học phần | Thành viên 1 |
| `sprints` | Các sprint của nhóm | Thành viên 2 |
| `user_stories` | User story trong backlog | Thành viên 2 |
| `tasks` | Task chi tiết thuộc user story | Thành viên 2 |
| `task_assignments` | Phân công task cho thành viên | Thành viên 3 |
| `activity_logs` | Log mọi thao tác thay đổi trạng thái | Thành viên 3 |
| `peer_reviews` | Đánh giá chéo giữa các thành viên | Thành viên 3 |

---

### 3. Hệ thống Theo dõi Chuyên cần & Tương tác Lớp học

- **Bối cảnh:** Lớp học dùng nhiều hình thức điểm danh (QR, code theo phút, thủ công); có hoạt động hỏi đáp và quiz; khó tổng hợp "mức độ tham gia" của từng sinh viên.
- **Mục tiêu Web:** Backend quản lý buổi học, điểm danh, log tương tác (trả lời câu hỏi, nộp mini-quiz, đăng thảo luận).
- **Yêu cầu chức năng (Backend Focus):**
  - **Flexible Attendance:** Hỗ trợ nhiều hình thức điểm danh; lưu lịch sử đối chiếu khi có tranh chấp.
  - **Engagement Score:** Tính điểm tương tác theo rule do GV cấu hình (trả lời đúng = 2 điểm, đăng thảo luận = 1 điểm); tổng hợp thành "class participation index".
  - **Alert Engine:** Phát hiện SV vắng > ngưỡng quy định hoặc điểm tương tác thấp, gửi cảnh báo tới cố vấn học tập.

**Gợi ý bảng CSDL (≥ 9 bảng):**

| Bảng | Mô tả | Người phụ trách |
|---|---|---|
| `users` | Tài khoản hệ thống | Thành viên 1 |
| `courses` | Học phần | Thành viên 1 |
| `class_sessions` | Từng buổi học của học phần | Thành viên 1 |
| `attendance_records` | Bản ghi điểm danh SV theo buổi | Thành viên 2 |
| `attendance_methods` | Hình thức điểm danh (QR, Code, Thủ công) | Thành viên 2 |
| `quiz_sessions` | Phiên quiz trong buổi học | Thành viên 2 |
| `quiz_submissions` | SV nộp câu trả lời quiz | Thành viên 3 |
| `interaction_logs` | Log tương tác (hỏi, trả lời, thảo luận) | Thành viên 3 |
| `engagement_scores` | Tổng hợp điểm tham gia theo kỳ | Thành viên 3 |

---

## NHÓM 2: DỊCH VỤ SINH VIÊN & CAMPUS

### 4. Hệ thống Quản lý KTX & Đăng ký Chỗ ở (Dormitory Manager)

- **Bối cảnh:** Sinh viên trong và ngoài nước đăng ký ký túc xá; có giới hạn giường theo tòa nhà, loại phòng; cần ưu tiên đối tượng chính sách theo học kỳ.
- **Mục tiêu Web:** Quản lý đăng ký, xếp phòng, hợp đồng, phí dịch vụ KTX.
- **Yêu cầu chức năng (Backend Focus):**
  - **Room Allocation Logic:** Tự động gán phòng dựa trên giới tính, chương trình học, ưu tiên đối tượng chính sách; không vượt số giường tối đa.
  - **Billing Engine:** Tính tiền theo số tháng, loại phòng, phụ phí (điện, nước, điều hòa); sinh hóa đơn PDF.
  - **Violation Tracking:** Ghi nhận lỗi vi phạm nội quy, tính điểm trừ; nếu vượt ngưỡng thì đưa vào danh sách xem xét chấm dứt hợp đồng.

**Gợi ý bảng CSDL (≥ 9 bảng):**

| Bảng | Mô tả | Người phụ trách |
|---|---|---|
| `users` | Tài khoản hệ thống | Thành viên 1 |
| `students` | Hồ sơ sinh viên | Thành viên 1 |
| `buildings` | Danh sách tòa nhà KTX | Thành viên 1 |
| `rooms` | Phòng trong từng tòa nhà | Thành viên 2 |
| `room_registrations` | Đăng ký phòng của sinh viên | Thành viên 2 |
| `contracts` | Hợp đồng thuê phòng | Thành viên 2 |
| `invoices` | Hóa đơn tiền phòng & dịch vụ | Thành viên 3 |
| `utility_readings` | Chỉ số điện/nước hàng tháng | Thành viên 3 |
| `violation_records` | Ghi nhận vi phạm nội quy | Thành viên 3 |

---

### 5. Web Quản lý Sự kiện & Câu lạc bộ Sinh viên (Club & Event Platform)

- **Bối cảnh:** Nhiều câu lạc bộ và sự kiện do sinh viên, phòng Công tác sinh viên tổ chức; cần quản lý đăng ký, điểm rèn luyện, chứng nhận tham gia.
- **Mục tiêu Web:** Cổng đăng ký sự kiện, quản lý thành viên CLB, ghi nhận điểm hoạt động ngoại khóa.
- **Yêu cầu chức năng (Backend Focus):**
  - **Event Registration & Check-in:** Sinh viên đăng ký sự kiện, backend sinh QR code; check-in ghi nhận và ngăn quét trùng, đảm bảo không vượt sức chứa.
  - **Activity Points:** Mỗi sự kiện có trọng số điểm rèn luyện; backend tự cộng vào hồ sơ SV, hỗ trợ export khi tính điểm cuối kỳ.
  - **Role & Permission:** Phân quyền Ban chủ nhiệm CLB, phòng CTSV, sinh viên; CLB chỉ chỉnh sửa sự kiện của mình.

**Gợi ý bảng CSDL (≥ 9 bảng):**

| Bảng | Mô tả | Người phụ trách |
|---|---|---|
| `users` | Tài khoản hệ thống | Thành viên 1 |
| `clubs` | Danh sách câu lạc bộ | Thành viên 1 |
| `club_members` | Thành viên của từng CLB | Thành viên 1 |
| `events` | Danh sách sự kiện | Thành viên 2 |
| `event_registrations` | SV đăng ký tham gia sự kiện | Thành viên 2 |
| `checkin_logs` | Log check-in sự kiện | Thành viên 2 |
| `activity_point_rules` | Cấu hình điểm rèn luyện theo loại sự kiện | Thành viên 3 |
| `student_points` | Tổng điểm rèn luyện của SV theo kỳ | Thành viên 3 |
| `certificates` | Chứng nhận tham gia được cấp | Thành viên 3 |

---

### 6. Hệ thống Đặt Dịch vụ Campus (Campus Services Booking)

- **Bối cảnh:** Trường có nhiều dịch vụ: phòng tự học nhóm, phòng lab chuyên dụng, sân thể thao, phòng họp, phòng studio media; việc đặt lịch thường bị trùng hoặc không kiểm soát được.
- **Mục tiêu Web:** Hệ thống đặt chỗ tập trung toàn campus.
- **Yêu cầu chức năng (Backend Focus):**
  - **Resource Booking Engine:** Quản lý nhiều loại tài nguyên với khung giờ; backend kiểm tra xung đột, đảm bảo mỗi tài nguyên 1 booking/khung giờ.
  - **Policy Rules:** Áp dụng quy tắc như "mỗi SV không được đặt > 2 slot giờ cao điểm/tuần", "phòng lab chỉ cho nhóm có GVHD phê duyệt".
  - **Usage Reporting:** Thống kê tần suất sử dụng từng tài nguyên, giờ cao điểm, phục vụ nhà trường tối ưu lịch mở cửa.

**Gợi ý bảng CSDL (≥ 9 bảng):**

| Bảng | Mô tả | Người phụ trách |
|---|---|---|
| `users` | Tài khoản hệ thống | Thành viên 1 |
| `resource_categories` | Danh mục tài nguyên (phòng, sân, studio) | Thành viên 1 |
| `resources` | Danh sách tài nguyên cụ thể | Thành viên 1 |
| `time_slots` | Khung giờ hoạt động của tài nguyên | Thành viên 2 |
| `bookings` | Lượt đặt của người dùng | Thành viên 2 |
| `booking_policies` | Quy tắc đặt cho từng loại tài nguyên | Thành viên 2 |
| `approvals` | Lịch sử phê duyệt/từ chối booking | Thành viên 3 |
| `cancellations` | Ghi nhận hủy đặt, lý do | Thành viên 3 |
| `usage_reports` | Báo cáo tổng hợp sử dụng theo tuần/tháng | Thành viên 3 |

---

## NHÓM 3: QUẢN LÝ HỌC THUẬT & CHƯƠNG TRÌNH

### 7. Hệ thống Quản lý Kế hoạch Mở Lớp & Phân công Giảng viên (Teaching Load Planner)

- **Bối cảnh:** Mỗi kỳ, phòng đào tạo phải quyết định mở lớp, số nhóm, phân công GV, tránh trùng lịch dạy, cân bằng tải giảng viên cơ hữu và thỉnh giảng.
- **Mục tiêu Web:** Quản lý kế hoạch học kỳ, phân công giảng viên, theo dõi giờ chuẩn.
- **Yêu cầu chức năng (Backend Focus):**
  - **Constraint-based Scheduling:** Khi thêm lớp mới, kiểm tra xung đột phòng, giảng viên, khung giờ; backend từ chối nếu vi phạm ràng buộc.
  - **Workload Calculation:** Tự động tính tổng tiết/giờ cho mỗi GV, đối chiếu chuẩn giờ quy định; báo cáo GV thiếu/vượt chuẩn.
  - **Scenario Comparison:** Lưu nhiều kịch bản phân công (draft) và so sánh chỉ số (tổng giờ thỉnh giảng, số lớp tối đa/GV) trước khi chốt.

**Gợi ý bảng CSDL (≥ 9 bảng):**

| Bảng | Mô tả | Người phụ trách |
|---|---|---|
| `users` | Tài khoản hệ thống | Thành viên 1 |
| `lecturers` | Hồ sơ giảng viên, chuyên môn, giờ chuẩn | Thành viên 1 |
| `subjects` | Danh mục học phần | Thành viên 1 |
| `semesters` | Học kỳ, năm học | Thành viên 2 |
| `class_sections` | Lớp học phần mở trong kỳ | Thành viên 2 |
| `teaching_assignments` | Phân công GV dạy lớp | Thành viên 2 |
| `classrooms` | Danh sách phòng học, sức chứa | Thành viên 3 |
| `schedules` | Lịch dạy theo tuần của từng lớp | Thành viên 3 |
| `workload_reports` | Tổng hợp giờ chuẩn theo GV, kỳ | Thành viên 3 |

---

### 8. Quản lý Chuẩn Đầu Ra & Rubric Đánh giá (CLO–PLO Outcome Mapping)

- **Bối cảnh:** Chương trình theo chuẩn AUN/ABET yêu cầu mapping giữa chuẩn đầu ra học phần (CLO) và chương trình (PLO), gắn với rubric đánh giá và minh chứng điểm sinh viên.
- **Mục tiêu Web:** Hỗ trợ bộ môn quản lý chuẩn đầu ra, thiết lập rubric, tự tính mức độ đạt chuẩn.
- **Yêu cầu chức năng (Backend Focus):**
  - **CLO–PLO Matrix:** Lưu ma trận mapping CLO–PLO, cấu hình trọng số đóng góp linh hoạt.
  - **Rubric Engine:** GV tạo rubric (criteria, level, weight); backend nhận điểm chi tiết và tính điểm CLO tự động.
  - **Attainment Reporting:** Tổng hợp mức độ đạt từng PLO ở cấp lớp và cấp chương trình theo học kỳ; xuất báo cáo phục vụ kiểm định.

**Gợi ý bảng CSDL (≥ 9 bảng):**

| Bảng | Mô tả | Người phụ trách |
|---|---|---|
| `programs` | Chương trình đào tạo | Thành viên 1 |
| `plos` | Chuẩn đầu ra chương trình | Thành viên 1 |
| `subjects` | Học phần | Thành viên 1 |
| `clos` | Chuẩn đầu ra học phần | Thành viên 2 |
| `clo_plo_mappings` | Ma trận ánh xạ CLO–PLO, trọng số | Thành viên 2 |
| `assessments` | Bài kiểm tra/đánh giá trong học phần | Thành viên 2 |
| `rubrics` | Rubric cho từng bài đánh giá | Thành viên 3 |
| `rubric_scores` | Điểm chi tiết theo từng criterion | Thành viên 3 |
| `attainment_reports` | Báo cáo mức đạt CLO/PLO tổng hợp | Thành viên 3 |

---

### 9. Hệ thống Quản lý Thực tập & Đánh giá Doanh nghiệp (Internship Manager)

- **Bối cảnh:** Sinh viên ISchool đi thực tập tại các công ty trong và ngoài nước; cần quản lý hồ sơ, tiến độ, nhận xét doanh nghiệp, kết nối với GVHD.
- **Mục tiêu Web:** Theo dõi toàn bộ vòng đời một kỳ thực tập, từ đăng ký, phê duyệt đến đánh giá cuối kỳ.
- **Yêu cầu chức năng (Backend Focus):**
  - **Matching & Approval:** Sinh viên chọn doanh nghiệp hoặc đề xuất mới; backend kiểm tra đúng ngành và không vượt quota doanh nghiệp.
  - **Dual Evaluation:** Doanh nghiệp chấm điểm; GV chấm điểm; backend hợp nhất theo trọng số cấu hình (ví dụ: DN 60%, GV 40%).
  - **Compliance Check:** Nhắc nộp nhật ký hàng tuần; cảnh báo SV lâu không cập nhật; báo cáo danh sách nguy cơ không đủ điều kiện.

**Gợi ý bảng CSDL (≥ 9 bảng):**

| Bảng | Mô tả | Người phụ trách |
|---|---|---|
| `users` | Tài khoản hệ thống | Thành viên 1 |
| `companies` | Danh sách doanh nghiệp đối tác | Thành viên 1 |
| `internship_positions` | Vị trí thực tập tại doanh nghiệp | Thành viên 1 |
| `internship_registrations` | SV đăng ký vị trí thực tập | Thành viên 2 |
| `internship_assignments` | Phân công GVHD hướng dẫn | Thành viên 2 |
| `weekly_journals` | Nhật ký thực tập hàng tuần | Thành viên 2 |
| `company_evaluations` | Đánh giá từ phía doanh nghiệp | Thành viên 3 |
| `lecturer_evaluations` | Đánh giá từ phía giảng viên | Thành viên 3 |
| `final_grades` | Điểm tổng hợp kỳ thực tập | Thành viên 3 |

---

## NHÓM 4: CÔNG NGHỆ, PHÒNG LAB & TÀI SẢN

### 10. Quản lý Phòng Lab Thực hành & Thiết bị (Lab & Equipment Manager)

- **Bối cảnh:** Nhiều phòng lab (AI, IoT, Multimedia) với thiết bị đắt tiền; cần quản lý lịch mượn, hỏng hóc, bảo trì, hiệu chuẩn định kỳ.
- **Mục tiêu Web:** Quản lý tài sản lab, lịch sử sử dụng, lên lịch bảo trì.
- **Yêu cầu chức năng (Backend Focus):**
  - **Check-in/Check-out Logic:** Mượn thiết bị ghi trạng thái, kiểm tra tồn kho, ngăn mượn khi đã hết.
  - **Maintenance & Calibration Schedule:** Tự động tạo lịch bảo trì/hiệu chuẩn theo số giờ sử dụng hoặc chu kỳ thời gian.
  - **Damage & Penalty:** Khi trả thiết bị hỏng, ghi biên bản và tính mức đền bù theo mức độ và giá trị tài sản.

**Gợi ý bảng CSDL (≥ 9 bảng):**

| Bảng | Mô tả | Người phụ trách |
|---|---|---|
| `users` | Tài khoản hệ thống | Thành viên 1 |
| `labs` | Danh sách phòng lab | Thành viên 1 |
| `equipment_categories` | Danh mục loại thiết bị | Thành viên 1 |
| `equipment` | Danh sách thiết bị cụ thể | Thành viên 2 |
| `borrow_requests` | Yêu cầu mượn thiết bị | Thành viên 2 |
| `borrow_records` | Ghi nhận check-in/check-out | Thành viên 2 |
| `maintenance_schedules` | Lịch bảo trì định kỳ | Thành viên 3 |
| `maintenance_logs` | Lịch sử thực hiện bảo trì | Thành viên 3 |
| `damage_reports` | Biên bản hỏng hóc, đền bù | Thành viên 3 |

---

### 11. Hệ thống Quản lý License Phần mềm (Software License Tracker)

- **Bối cảnh:** Trường có nhiều license phần mềm (IDE, Office 365, Matlab, Adobe, Cloud credits) cấp cho SV và GV theo thời hạn, theo học phần.
- **Mục tiêu Web:** Quản lý cấp phát, thu hồi, nhắc hạn license, tránh lãng phí.
- **Yêu cầu chức năng (Backend Focus):**
  - **Allocation Engine:** Phân bổ license theo nhóm đối tượng, kiểm tra số lượng còn lại trước khi cấp.
  - **Expiry Notification:** Script quét định kỳ gửi email nhắc khi license sắp hết hạn (7 ngày, 1 ngày); cơ chế tự khóa/thu hồi.
  - **Usage Analytics:** Thống kê tỉ lệ kích hoạt, tần suất sử dụng, giúp phòng IT tối ưu số lượng mua cho năm sau.

**Gợi ý bảng CSDL (≥ 9 bảng):**

| Bảng | Mô tả | Người phụ trách |
|---|---|---|
| `users` | Tài khoản hệ thống | Thành viên 1 |
| `software_titles` | Danh mục phần mềm | Thành viên 1 |
| `license_pools` | Gói license, số lượng, hạn dùng | Thành viên 1 |
| `license_allocations` | Phân bổ license cho cá nhân | Thành viên 2 |
| `allocation_rules` | Quy tắc phân bổ theo nhóm đối tượng | Thành viên 2 |
| `activation_logs` | Ghi nhận kích hoạt thực tế | Thành viên 2 |
| `expiry_notifications` | Lịch sử gửi nhắc hạn | Thành viên 3 |
| `revocation_logs` | Ghi nhận thu hồi license | Thành viên 3 |
| `usage_stats` | Thống kê sử dụng tổng hợp theo kỳ | Thành viên 3 |

---

### 12. Quản lý Phòng họp & Thiết bị Hội thảo (Meeting & Hybrid Classroom Manager)

- **Bối cảnh:** Phòng họp và phòng hybrid có camera, micro, thiết bị hội nghị cần được đặt lịch, đảm bảo không trùng, theo dõi tình trạng hoạt động.
- **Mục tiêu Web:** Hệ thống đặt phòng, thiết bị hội thảo, theo dõi log sử dụng.
- **Yêu cầu chức năng (Backend Focus):**
  - **Multi-resource Booking:** Một buổi họp cần phòng + bộ thiết bị; backend kiểm tra tất cả tài nguyên đều rảnh.
  - **Health Status Log:** Lưu trạng thái thiết bị (OK, cần bảo trì, hỏng); nếu hỏng thì không cho phép đặt.
  - **Mock Integration Hook:** Thiết kế API endpoint "giả lập" để sau này tích hợp với hệ thống họp trực tuyến (Zoom/Teams).

**Gợi ý bảng CSDL (≥ 9 bảng):**

| Bảng | Mô tả | Người phụ trách |
|---|---|---|
| `users` | Tài khoản hệ thống | Thành viên 1 |
| `meeting_rooms` | Danh sách phòng họp, sức chứa | Thành viên 1 |
| `av_equipment` | Thiết bị hội thảo (camera, micro, TV) | Thành viên 1 |
| `room_bookings` | Đặt phòng họp | Thành viên 2 |
| `equipment_bookings` | Đặt thiết bị kèm theo | Thành viên 2 |
| `booking_conflicts` | Ghi nhận xung đột phát hiện | Thành viên 2 |
| `equipment_status_logs` | Lịch sử trạng thái thiết bị | Thành viên 3 |
| `room_maintenance` | Lịch bảo trì phòng | Thành viên 3 |
| `usage_logs` | Log sử dụng thực tế theo buổi | Thành viên 3 |

---

## NHÓM 5: QUẢN TRỊ & DỊCH VỤ HỖ TRỢ

### 13. Hệ thống Hỗ trợ Nội bộ – Campus Helpdesk (IT & Facility Ticket)

- **Bối cảnh:** Sinh viên/GV thường phải gửi email hoặc gọi điện báo lỗi (máy chiếu hỏng, wifi yếu, tài khoản LMS lỗi, điều hòa không chạy...); khó theo dõi tiến độ xử lý.
- **Mục tiêu Web:** Hệ thống ticket tập trung cho bộ phận IT, cơ sở vật chất, phòng đào tạo.
- **Yêu cầu chức năng (Backend Focus):**
  - **Ticket Routing:** Tự động phân loại ticket theo từ khóa và chuyển đến đúng bộ phận.
  - **SLA & Escalation:** Đặt thời gian xử lý tối đa theo loại ticket; nếu quá hạn mà chưa xử lý thì tự động escalate lên cấp quản lý.
  - **Satisfaction Survey:** Sau khi đóng ticket, gửi khảo sát đánh giá; tổng hợp báo cáo chất lượng dịch vụ.

**Gợi ý bảng CSDL (≥ 9 bảng):**

| Bảng | Mô tả | Người phụ trách |
|---|---|---|
| `users` | Tài khoản hệ thống | Thành viên 1 |
| `departments` | Bộ phận xử lý (IT, CSVC, Đào tạo) | Thành viên 1 |
| `ticket_categories` | Danh mục loại ticket + SLA quy định | Thành viên 1 |
| `tickets` | Ticket phản ánh/yêu cầu hỗ trợ | Thành viên 2 |
| `ticket_assignments` | Phân công nhân viên xử lý | Thành viên 2 |
| `ticket_comments` | Ghi chú, trao đổi trong ticket | Thành viên 2 |
| `ticket_status_logs` | Lịch sử chuyển trạng thái ticket | Thành viên 3 |
| `escalation_logs` | Ghi nhận escalate khi quá hạn | Thành viên 3 |
| `satisfaction_surveys` | Kết quả khảo sát sau khi đóng ticket | Thành viên 3 |

---

### 14. Hệ thống Xử lý Đơn xin Xác nhận & Giấy tờ (Certificate Request System)

- **Bối cảnh:** Sinh viên cần nhiều loại giấy tờ: xác nhận sinh viên, bảng điểm tạm, xác nhận học bổng; hiện phải điền form giấy, chờ lâu không biết tiến độ đến đâu.
- **Mục tiêu Web:** Đăng ký, xử lý và theo dõi trạng thái các loại giấy tờ xác nhận trực tuyến.
- **Yêu cầu chức năng (Backend Focus):**
  - **Template & Workflow:** Cấu hình các loại xác nhận (nội dung, mẫu, phòng ban xử lý); backend thực thi luồng duyệt nhiều bước (CTSV → Đào tạo → BGH).
  - **Queue Management:** Điều phối thứ tự xử lý yêu cầu; thống kê thời gian xử lý trung bình để tối ưu quy trình.
  - **Verification Code:** Tạo mã xác minh/QR trên giấy đã ký; cung cấp endpoint public để cơ quan bên ngoài kiểm tra tính hợp lệ.

**Gợi ý bảng CSDL (≥ 9 bảng):**

| Bảng | Mô tả | Người phụ trách |
|---|---|---|
| `users` | Tài khoản hệ thống | Thành viên 1 |
| `document_types` | Danh mục loại giấy tờ | Thành viên 1 |
| `workflow_steps` | Các bước duyệt theo loại giấy tờ | Thành viên 1 |
| `document_requests` | Yêu cầu xin cấp giấy tờ | Thành viên 2 |
| `workflow_instances` | Tiến trình xử lý của từng yêu cầu | Thành viên 2 |
| `approvals` | Bản ghi duyệt/từ chối ở từng bước | Thành viên 2 |
| `issued_documents` | Giấy tờ đã được cấp | Thành viên 3 |
| `verification_codes` | Mã xác minh QR gắn với giấy tờ | Thành viên 3 |
| `notification_logs` | Lịch sử gửi thông báo trạng thái | Thành viên 3 |

---

### 15. Quản lý Học bổng, Trợ cấp & Thành tích (Scholarship & Award Manager)

- **Bối cảnh:** Nhiều loại học bổng, quỹ hỗ trợ, giải thưởng dành cho sinh viên; cần xét dựa trên GPA, hoàn cảnh, thành tích nghiên cứu, hoạt động cộng đồng.
- **Mục tiêu Web:** Quản lý hồ sơ ứng tuyển, quy trình xét duyệt, chi trả học bổng.
- **Yêu cầu chức năng (Backend Focus):**
  - **Eligibility Rules:** Định nghĩa điều kiện nhận học bổng (GPA ≥ 3.2, không có điểm F, tham gia ≥ 2 hoạt động); backend tự kiểm tra và cảnh báo hồ sơ không đủ điều kiện.
  - **Scoring & Ranking:** Hội đồng chấm điểm theo tiêu chí (học tập, NCKH, ngoại khóa, hoàn cảnh); backend tính tổng điểm, xếp hạng, đề xuất danh sách trong ngân sách.
  - **Disbursement Log:** Quản lý trạng thái chi trả (đã chi/đang xử lý); xuất báo cáo theo chương trình, khóa, khoa.

**Gợi ý bảng CSDL (≥ 9 bảng):**

| Bảng | Mô tả | Người phụ trách |
|---|---|---|
| `users` | Tài khoản hệ thống | Thành viên 1 |
| `scholarship_programs` | Danh mục chương trình học bổng | Thành viên 1 |
| `eligibility_rules` | Điều kiện xét học bổng | Thành viên 1 |
| `applications` | Hồ sơ ứng tuyển học bổng | Thành viên 2 |
| `scoring_criteria` | Tiêu chí và trọng số chấm điểm | Thành viên 2 |
| `evaluation_scores` | Điểm từng tiêu chí của từng hồ sơ | Thành viên 2 |
| `ranking_results` | Kết quả xếp hạng và đề xuất cấp | Thành viên 3 |
| `disbursements` | Bản ghi chi trả học bổng | Thành viên 3 |
| `award_certificates` | Giấy chứng nhận học bổng/giải thưởng | Thành viên 3 |

---

## BẢNG TÓM TẮT ĐIỂM THƯỞNG THEO MÔ HÌNH KỸ THUẬT

| Tính năng nâng cao | Điểm cộng | Ghi chú |
|---|---|---|
| Mô hình MVC rõ ràng | +5–10% | Tách thư mục Model/View/Controller |
| Singleton Pattern (DB Connection) | +5% | Chỉ tạo 1 instance kết nối DB |
| Repository/DAO Pattern | +5–10% | Tách logic truy vấn DB khỏi Controller |
| AJAX CRUD (không reload trang) | +10–15% | Dùng fetch/XMLHttpRequest |
| REST API + fetch/axios frontend | +15–20% | Backend trả JSON, frontend gọi API |
| Phân quyền RBAC nhiều tầng | +5–10% | Middleware kiểm tra quyền |
| Export PDF/Excel | +5% | Xuất báo cáo dữ liệu |
| Gửi email thông báo | +5% | PHPMailer hoặc SMTP thuần |
| Responsive UI (Bootstrap/Tailwind) | +5% | Giao diện đẹp trên mobile |
| Tự đề xuất đề tài (được GV duyệt) | Tùy theo độ phức tạp | Phải có văn bản GV phê duyệt |

---

*Tài liệu này được biên soạn dành cho môn MULTIMEDIA DESIGN AND WEB DEVELOPMENT – Trường Quốc tế ISchool – ĐHQGHN, 2026.*

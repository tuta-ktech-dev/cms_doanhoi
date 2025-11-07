# Tổng quan hệ thống

Hệ thống Quản lý hoạt động Đoàn – Hội gồm 3 nhóm người dùng chính:

- **Sinh viên** (Mobile App – Flutter): Đăng ký, điểm danh, xem thông tin sự kiện, theo dõi điểm rèn luyện.
- **Đoàn – Hội** (Web Admin): Quản lý, tạo, chỉnh sửa, xuất báo cáo sự kiện.
- **Admin**: Quản trị hệ thống, phân quyền, thêm/xóa tài khoản, quản lý tổ chức.

## Các chức năng chung của hệ thống

- Xác thực người dùng bằng JWT Auth.
- Phân quyền truy cập theo role (STUDENT, LECTURER, ADMIN).
- Quản lý dữ liệu người dùng, sự kiện, đăng ký, điểm danh, điểm rèn luyện.

# Nghiệp vụ chi tiết theo vai trò

## 2.1. Sinh viên

### 1. Đăng ký & đăng nhập

- Sinh viên tạo tài khoản (role mặc định là STUDENT).
- Có thể yêu cầu kích hoạt tài khoản qua email (tùy chọn).
- JWT được cấp sau khi đăng nhập để xác thực cho mọi thao tác trong ứng dụng.

### 2. Xem danh sách sự kiện

- Chỉ hiển thị sự kiện có trạng thái PUBLISHED.
- Có thể lọc theo "Loại sự kiện", "Ngày diễn ra", hoặc "Đoàn – Hội tổ chức".

### 3. Đăng ký / Hủy đăng ký sự kiện

**Hệ thống kiểm tra điều kiện trước khi đăng ký:**

- Chưa hết hạn đăng ký (registration_end_date).
- Sự kiện chưa đủ số lượng tối đa.
- Sinh viên chưa đăng ký sự kiện này trước đó.

**Hủy đăng ký:**

- Chỉ được hủy khi sự kiện chưa bắt đầu (status = PUBLISHED hoặc UPCOMING).
- Cho phép hủy ngay cả khi đã có nhiều người đăng ký (trường hợp sinh viên đăng nhầm).

### 4. Điểm danh bằng QR Code

- Khi sự kiện bắt đầu, giảng viên mở web hiển thị mã QR.
- Backend sinh mã JWT riêng cho sự kiện, tự động đổi mã QR mỗi 20 giây để tránh sinh viên chụp lại mã gửi cho người khác.
- Sinh viên mở app → quét mã QR → app gửi token đến server để xác thực.

**Hệ thống kiểm tra:**

- Token hợp lệ và chưa hết hạn.
- Sinh viên đã đăng ký sự kiện.
- Nếu hợp lệ: cập nhật attendance_status = PRESENT.
- Nếu sai: thông báo "Không hợp lệ / Hết hạn / Chưa đăng ký".

- Trong thời gian sự kiện đang diễn ra, sinh viên có thể mở app để xem trạng thái điểm danh hiện tại (đã điểm danh hoặc chưa).

### 5. Xem thông tin cá nhân và lịch sử hoạt động

- Xem thông tin tài khoản cá nhân (profile).
- Xem danh sách sự kiện đã tham gia, trạng thái điểm danh, thời gian điểm danh, và điểm rèn luyện được cộng.

### 6. Theo dõi điểm rèn luyện

- Mỗi sự kiện có thể có số điểm rèn luyện đi kèm (1, 2, 3 hoặc nhiều hơn).
- Sau khi sinh viên được điểm danh thành công, hệ thống tự động cộng điểm rèn luyện.
- Sinh viên có thể xem tổng điểm rèn luyện tích lũy trong từng học kỳ.
- Cho phép đặt lại điểm rèn luyện (reset) khi bắt đầu học kỳ mới, để tính lại từ đầu.

## 2.2. Đoàn – Hội (Web Admin)

### 1. Quản lý sự kiện

**Thêm mới sự kiện với các thông tin:**

- Tiêu đề, mô tả, thời gian bắt đầu/kết thúc, địa điểm.
- Giới hạn số lượng người đăng ký, hạn đăng ký.
- Trạng thái sự kiện (DRAFT, PUBLISHED, IN_PROGRESS, FINISHED).
- Kinh phí tổ chức (tùy chọn).
- Điểm rèn luyện được cộng cho sinh viên tham gia (tùy chọn, giá trị từ 1 trở lên).

**Các thao tác khác:**

- Sửa thông tin sự kiện.
- Xóa sự kiện (chỉ khi chưa diễn ra).
- Xuất danh sách sinh viên đăng ký và điểm danh ra Excel hoặc PDF.

### 2. Xem danh sách sinh viên đăng ký

Có thể lọc danh sách theo trạng thái:

- **REGISTERED** – đã đăng ký.
- **PRESENT** – đã điểm danh.
- **ABSENT** – không điểm danh.

### 3. Sinh mã QR cho sự kiện

- Backend tạo mã QR chứa token JWT (mã hóa event_id và timestamp).
- Mã QR được đổi tự động sau mỗi 20 giây.
- Giảng viên hoặc cán bộ đoàn – hội chiếu mã này lên màn hình trong suốt thời gian diễn ra sự kiện.
- Sinh viên chỉ có thể quét điểm danh khi mã đang hợp lệ.

## 2.3. Admin (Web)

### 1. Phân quyền người dùng

Admin có thể thay đổi vai trò:

- **STUDENT → LECTURER**: Được quyền tạo sự kiện, điểm danh.
- **LECTURER → ADMIN**: Nếu cần phân cấp quản trị.

### 2. Xóa hoặc khóa người dùng

- Có thể xóa tài khoản khỏi hệ thống.
- Có thể khóa tài khoản tạm thời (status = 0).

### 3. Quản lý Đoàn – Hội

- Thêm mới các tổ chức, chi đoàn, chi hội.
- Gán quyền quản lý cho từng tài khoản giảng viên.

---

> **Lưu ý:** Xem file `CHECKLIST.md` để theo dõi trạng thái triển khai chi tiết của từng chức năng.

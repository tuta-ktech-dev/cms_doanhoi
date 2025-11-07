# Checklist - Trạng thái triển khai

> File này so sánh giữa yêu cầu nghiệp vụ trong `overview.MD` và codebase thực tế để theo dõi tiến độ phát triển.

## Các chức năng chung

- [x] Xác thực người dùng bằng JWT Auth (Sanctum)
- [x] Phân quyền truy cập theo role (STUDENT, UNION_MANAGER, ADMIN)
- [x] Quản lý dữ liệu người dùng, sự kiện, đăng ký, điểm danh, điểm rèn luyện

## 2.1. Sinh viên

### 1. Đăng ký & đăng nhập

- [x] Sinh viên tạo tài khoản (role mặc định là STUDENT)
- [ ] Kích hoạt tài khoản qua email (có field `email_verified_at` nhưng chưa implement logic)
- [x] JWT được cấp sau khi đăng nhập (Sanctum token)

### 2. Xem danh sách sự kiện

- [x] Chỉ hiển thị sự kiện có trạng thái PUBLISHED
- [x] Lọc theo "Ngày diễn ra" (status: upcoming, ongoing, completed)
- [x] Lọc theo "Đoàn – Hội tổ chức" (union_id)
- [ ] Lọc theo "Loại sự kiện" (chưa có field event_type trong model)

### 3. Đăng ký / Hủy đăng ký sự kiện

**Kiểm tra điều kiện đăng ký:**

- [x] Chưa hết hạn đăng ký (registration_deadline)
- [x] Sự kiện chưa đủ số lượng tối đa
- [x] Sinh viên chưa đăng ký sự kiện này trước đó

**Hủy đăng ký:**

- [x] Chỉ được hủy khi sự kiện chưa bắt đầu (start_date > now)
- [x] Cho phép hủy ngay cả khi đã có nhiều người đăng ký

### 4. Điểm danh bằng QR Code

- [x] Backend sinh mã QR chứa token (đã implement API với package `chillerlan/php-qrcode`)
- [x] Mã QR có expiry 5 phút, có thể refresh thủ công (không cần auto-refresh 20s)
- [x] API endpoint để sinh mã QR cho sự kiện (`GET /api/events/{id}/qr-code`)
- [x] API endpoint để quét QR và điểm danh (`POST /api/student/scan-qr`)
- [x] Kiểm tra token hợp lệ và chưa hết hạn (one-time use token)
- [x] Kiểm tra sinh viên đã đăng ký sự kiện
- [x] Tự động cập nhật attendance_status = PRESENT khi quét QR thành công
- [x] Tự động cộng điểm rèn luyện khi điểm danh thành công
- [x] Xem trạng thái điểm danh hiện tại (API `/student/attendance`)

### 5. Xem thông tin cá nhân và lịch sử hoạt động

- [x] Xem thông tin tài khoản cá nhân (profile) - API `/auth/profile`
- [x] Xem danh sách sự kiện đã tham gia - API `/student/registrations`
- [x] Xem trạng thái điểm danh - API `/student/attendance`
- [x] Xem thời gian điểm danh
- [x] Xem điểm rèn luyện được cộng (tính từ attendance)

### 6. Theo dõi điểm rèn luyện

- [x] Mỗi sự kiện có thể có số điểm rèn luyện đi kèm (field `activity_points`)
- [ ] Tự động cộng điểm rèn luyện khi điểm danh thành công (chưa có logic tự động)
- [x] Xem tổng điểm rèn luyện tích lũy - API `/student/statistics`
- [ ] Xem điểm rèn luyện theo từng học kỳ (chưa có field semester)
- [ ] Reset điểm rèn luyện khi bắt đầu học kỳ mới (chưa có chức năng)

## 2.2. Đoàn – Hội (Web Admin)

### 1. Quản lý sự kiện

**Thêm mới sự kiện:**

- [x] Tiêu đề, mô tả, thời gian bắt đầu/kết thúc, địa điểm
- [x] Giới hạn số lượng người đăng ký (max_participants)
- [x] Hạn đăng ký (registration_deadline)
- [x] Trạng thái sự kiện (DRAFT, PUBLISHED, CANCELLED) - thiếu IN_PROGRESS, FINISHED
- [x] Kinh phí tổ chức (đã thêm field `budget` vào model và form)
- [x] Điểm rèn luyện được cộng (activity_points)

**Các thao tác khác:**

- [x] Sửa thông tin sự kiện
- [x] Xóa sự kiện (có logic kiểm tra trong EventResource)
- [x] Xuất danh sách sinh viên đăng ký và điểm danh ra Excel/PDF

### 2. Xem danh sách sinh viên đăng ký

- [x] Lọc danh sách theo trạng thái REGISTERED (approved)
- [x] Lọc danh sách theo trạng thái PRESENT (attendance status)
- [x] Lọc danh sách theo trạng thái ABSENT (attendance status)

### 3. Sinh mã QR cho sự kiện

- [x] Backend tạo mã QR chứa token (đã có API `GET /api/events/{id}/qr-code`)
- [x] Mã QR có expiry 5 phút, có thể refresh thủ công (không cần auto-refresh 20s)
- [x] Web interface để hiển thị QR code cho UNION_MANAGER/ADMIN (Filament page `/events/{id}/qr-code`)

## 2.3. Admin (Web)

### 1. Phân quyền người dùng

- [x] Thay đổi vai trò người dùng (UserResource)
- [x] Quản lý Roles và Permissions (RoleResource, PermissionResource)
- [x] Chuyển STUDENT → UNION_MANAGER (UNION_MANAGER = LECTURER trong overview - có thể đổi role trong UserResource)
- [x] Gán quyền quản lý đoàn hội cho UNION_MANAGER (UnionManagerResource - gán user vào union_managers table)
- [x] Chuyển UNION_MANAGER → ADMIN (có thể đổi role trong UserResource)

### 2. Xóa hoặc khóa người dùng

- [x] Xóa tài khoản khỏi hệ thống (UserResource)
- [x] Khóa tài khoản tạm thời (field `status` trong users table)

### 3. Quản lý Đoàn – Hội

- [x] Thêm mới các tổ chức, chi đoàn, chi hội (UnionResource)
- [x] Gán quyền quản lý cho từng tài khoản giảng viên (UnionManagerResource)

---

## Tóm tắt

### ✅ Đã hoàn thành (Hoàn thiện)

- Authentication & Authorization (JWT/Sanctum)
- Quản lý sự kiện (CRUD)
- Đăng ký/Hủy đăng ký sự kiện
- Xem thông tin cá nhân và lịch sử
- Quản lý người dùng, roles, permissions
- Export báo cáo Excel/PDF
- Thống kê và dashboard

### ⚠️ Cần bổ sung (Thiếu hoặc chưa đầy đủ)

1. **QR Code Attendance System** ✅ (Đã hoàn thành)
   - API sinh mã QR với token (one-time use, expiry 5 phút)
   - API quét QR để điểm danh (`POST /api/student/scan-qr`)
   - Tự động cộng điểm rèn luyện khi điểm danh thành công
   - Web interface hiển thị QR cho UNION_MANAGER/ADMIN (Filament page)
   - Auto-refresh mỗi 4 phút (trước khi hết hạn 5 phút)

2. **Email Activation**
   - Logic gửi email kích hoạt
   - Xác thực email

3. **Event Type/Category**
   - Thêm field `event_type` hoặc `category` vào events table
   - Filter theo loại sự kiện

4. **Activity Points Management**
   - ✅ Tự động cộng điểm khi điểm danh thành công (đã implement trong QR scan)
   - [ ] Quản lý điểm theo học kỳ (semester)
   - [ ] Reset điểm khi bắt đầu học kỳ mới

5. **Event Status**
   - Thêm trạng thái IN_PROGRESS, FINISHED
   - Logic tự động cập nhật trạng thái

6. **Event Budget** ✅ (Đã hoàn thành)
   - Đã thêm field `budget` vào events table
   - Đã thêm vào EventForm và EventsTable

7. **Role Management** ✅ (Đã hoàn thành - chỉ cần làm rõ tên gọi)
   - **Lưu ý:** UNION_MANAGER trong codebase = LECTURER trong overview
   - Đã có đầy đủ chức năng chuyển đổi role: STUDENT ↔ UNION_MANAGER ↔ ADMIN
   - Đã có gán quyền quản lý đoàn hội qua UnionManagerResource

---

## Ghi chú kỹ thuật

### API Endpoints đã có

**Authentication:**
- `POST /api/auth/register` - Đăng ký
- `POST /api/auth/login` - Đăng nhập
- `POST /api/auth/logout` - Đăng xuất
- `GET /api/auth/profile` - Xem profile
- `PUT /api/auth/profile` - Cập nhật profile
- `POST /api/auth/change-password` - Đổi mật khẩu

**Student Events:**
- `GET /api/student/events` - Danh sách sự kiện
- `GET /api/student/events/{id}` - Chi tiết sự kiện
- `POST /api/student/events/{id}/register` - Đăng ký sự kiện
- `DELETE /api/student/events/{id}/unregister` - Hủy đăng ký
- `POST /api/student/scan-qr` - Quét QR code để điểm danh
- `GET /api/student/registrations` - Danh sách đăng ký
- `GET /api/student/attendance` - Danh sách điểm danh
- `GET /api/student/statistics` - Thống kê sinh viên

**QR Code (UNION_MANAGER/ADMIN):**
- `GET /api/events/{id}/qr-code` - Sinh mã QR cho sự kiện

### Packages đã cài đặt

- `chillerlan/php-qrcode` - QR Code generator ✅ (đã sử dụng cho QR attendance)
- `maatwebsite/excel` - Export Excel
- `barryvdh/laravel-dompdf` - Export PDF

### Database Schema

**Events table:**
- Có field `activity_points` (decimal)
- Có field `budget` (decimal) - Kinh phí tổ chức
- Có field `status` (draft, published, cancelled)
- Thiếu field `event_type` hoặc `category`

**Users table:**
- Có field `email_verified_at` (chưa dùng cho activation)
- Có field `status` (để khóa tài khoản)

**Students table:**
- Có field `activity_points` (tổng điểm)
- Thiếu field `semester` để quản lý theo học kỳ

**Union_managers table (pivot):**
- Liên kết User (role = union_manager) với Union cụ thể
- UNION_MANAGER = LECTURER trong overview
- Một user có thể quản lý nhiều union (thông qua bảng này)


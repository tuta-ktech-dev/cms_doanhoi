# Quản lý người dùng

## Loại người dùng

### 1. Admin (Quản trị viên hệ thống)
- **Mô tả**: Có toàn quyền quản lý hệ thống, phân quyền và quản lý tất cả người dùng
- **Thông tin lưu trữ**:
  - ID (định danh duy nhất)
  - Tên đăng nhập
  - Mật khẩu (đã mã hóa)
  - Email
  - Họ và tên
  - Số điện thoại
  - Ngày tạo tài khoản
  - Trạng thái (active/inactive)
  - Ảnh đại diện (tùy chọn)
  - Lần đăng nhập cuối

### 2. Quản lý Đoàn Hội
- **Mô tả**: Quản lý hoạt động của một đoàn hội cụ thể, có quyền tạo và quản lý sự kiện
- **Thông tin lưu trữ**:
  - ID (định danh duy nhất)
  - Tên đăng nhập
  - Mật khẩu (đã mã hóa)
  - Email
  - Họ và tên
  - Số điện thoại
  - Chức vụ trong đoàn hội
  - Đoàn hội quản lý (ID đoàn hội)
  - Ngày tạo tài khoản
  - Trạng thái (active/inactive)
  - Ảnh đại diện (tùy chọn)
  - Quyền hạn cụ thể (JSON/Array)
  - Lần đăng nhập cuối

### 3. Sinh viên
- **Mô tả**: Người dùng thông thường, có thể đăng ký tham gia sự kiện
- **Thông tin lưu trữ**:
  - ID (định danh duy nhất)
  - Mã số sinh viên
  - Tên đăng nhập
  - Mật khẩu (đã mã hóa)
  - Email (trường/cá nhân)
  - Họ và tên
  - Ngày sinh
  - Giới tính
  - Số điện thoại
  - Khoa/Ngành học
  - Lớp
  - Khóa học
  - Ngày tạo tài khoản
  - Trạng thái (active/inactive)
  - Ảnh đại diện (tùy chọn)
  - Điểm hoạt động (tích lũy từ tham gia sự kiện)
  - Lần đăng nhập cuối

## Chức năng quản lý người dùng

### Chức năng chung
1. **Đăng nhập/Đăng xuất**
   - Xác thực người dùng
   - Quản lý phiên đăng nhập
   - Khôi phục mật khẩu

2. **Quản lý thông tin cá nhân**
   - Xem thông tin cá nhân
   - Cập nhật thông tin cá nhân
   - Thay đổi mật khẩu
   - Cập nhật ảnh đại diện

### Chức năng dành cho Admin
1. **Quản lý tài khoản**
   - Tạo tài khoản mới (tất cả các loại)
   - Xem danh sách tất cả người dùng
   - Tìm kiếm người dùng (theo tên, email, mã số, v.v.)
   - Khóa/Mở khóa tài khoản
   - Xóa tài khoản
   - Reset mật khẩu cho người dùng

2. **Phân quyền**
   - Tạo vai trò (role) mới
   - Gán quyền cho vai trò
   - Gán vai trò cho người dùng
   - Chỉnh sửa quyền của vai trò
   - Xem danh sách vai trò và quyền

3. **Quản lý đoàn hội**
   - Tạo đoàn hội mới
   - Chỉ định quản lý đoàn hội
   - Chỉnh sửa thông tin đoàn hội
   - Xóa đoàn hội

### Chức năng dành cho Quản lý Đoàn Hội
1. **Quản lý thành viên đoàn hội**
   - Xem danh sách thành viên
   - Thêm thành viên vào đoàn hội
   - Gán chức vụ cho thành viên
   - Xóa thành viên khỏi đoàn hội

2. **Quản lý đăng ký sự kiện**
   - Xem danh sách sinh viên đăng ký sự kiện
   - Duyệt/Từ chối đăng ký
   - Điểm danh sinh viên tham gia

## Cấu trúc dữ liệu API

### API Đăng nhập
```json
// Request
{
  "username": "string",
  "password": "string"
}

// Response
{
  "success": true,
  "data": {
    "token": "string",
    "user": {
      "id": "string",
      "username": "string",
      "fullName": "string",
      "email": "string",
      "role": "string",
      "permissions": ["string"],
      "avatar": "string"
    }
  }
}
```

### API Lấy thông tin người dùng
```json
// Response
{
  "success": true,
  "data": {
    "id": "string",
    "username": "string",
    "fullName": "string",
    "email": "string",
    "phone": "string",
    "role": "string",
    "permissions": ["string"],
    "avatar": "string",
    "createdAt": "date",
    "lastLogin": "date",
    "status": "string",
    // Thông tin bổ sung dựa theo loại người dùng
    "studentInfo": {
      "studentId": "string",
      "faculty": "string",
      "class": "string",
      "course": "string",
      "activityPoints": "number"
    }
  }
}
```

### API Tạo người dùng mới
```json
// Request
{
  "username": "string",
  "password": "string",
  "email": "string",
  "fullName": "string",
  "phone": "string",
  "role": "string",
  "status": "string",
  // Thông tin bổ sung dựa theo loại người dùng
  "studentInfo": {
    "studentId": "string",
    "faculty": "string",
    "class": "string",
    "course": "string"
  },
  "unionInfo": {
    "unionId": "string",
    "position": "string"
  }
}

// Response
{
  "success": true,
  "data": {
    "id": "string",
    "username": "string",
    "email": "string",
    "role": "string",
    "createdAt": "date"
  }
}
```

## Lưu ý thiết kế

1. **Bảo mật**:
   - Mật khẩu phải được mã hóa (bcrypt/Argon2)
   - Sử dụng JWT cho xác thực API
   - Kiểm tra quyền hạn trước mỗi thao tác

2. **Hiệu suất**:
   - Phân trang kết quả truy vấn người dùng
   - Cache thông tin người dùng thường xuyên truy cập
   - Tối ưu hóa truy vấn database

3. **Mở rộng**:
   - Thiết kế hệ thống quyền linh hoạt
   - Hỗ trợ đăng nhập qua mạng xã hội/SSO trong tương lai
   - Chuẩn bị cho việc mở rộng loại người dùng mới

# 📱 API Documentation - CMS Đoàn Hội

## 🌐 Base URL
```
http://localhost:8000/api
```

## 🔐 Authentication
API sử dụng **Bearer Token** authentication với Laravel Sanctum.

### Lấy Token
```bash
POST /api/auth/login
```

### Sử dụng Token
```bash
Authorization: Bearer {your_token}
```

## 📋 API Endpoints

### 🔑 Authentication Endpoints

#### 1. Đăng ký sinh viên
```http
POST /api/auth/register
```

**Request Body:**
```json
{
  "full_name": "Nguyễn Văn A",
  "email": "student@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "student_id": "SV001",
  "class": "CNTT01",
  "faculty": "Công nghệ thông tin",
  "course": "2024",
  "date_of_birth": "2000-01-01",
  "gender": "male",
  "phone": "0123456789"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Đăng ký thành công",
  "data": {
    "user": {
      "id": 1,
      "full_name": "Nguyễn Văn A",
      "email": "student@example.com",
      "role": "student"
    },
    "student": {
      "student_id": "SV001",
      "class": "CNTT01",
      "faculty": "Công nghệ thông tin",
      "course": "2024",
      "date_of_birth": "2000-01-01T00:00:00.000000Z",
      "gender": "male",
      "phone": "0123456789"
    },
    "token": "1|abc123..."
  }
}
```

#### 2. Đăng nhập
```http
POST /api/auth/login
```

**Request Body:**
```json
{
  "email": "student@example.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Đăng nhập thành công",
  "data": {
    "user": {
      "id": 1,
      "full_name": "Nguyễn Văn A",
      "email": "student@example.com",
      "role": "student"
    },
    "student": {
      "student_id": "SV001",
      "class": "CNTT01",
      "faculty": "Công nghệ thông tin",
      "course": "2024",
      "date_of_birth": "2000-01-01T00:00:00.000000Z",
      "gender": "male",
      "phone": "0123456789"
    },
    "token": "2|def456..."
  }
}
```

#### 3. Đăng xuất
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Đăng xuất thành công"
}
```

#### 4. Lấy thông tin profile
```http
GET /api/auth/profile
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "full_name": "Nguyễn Văn A",
      "email": "student@example.com",
      "role": "student",
      "created_at": "2025-09-20T02:02:33.000000Z"
    },
    "student": {
      "student_id": "SV001",
      "class": "CNTT01",
      "faculty": "Công nghệ thông tin",
      "course": "2024",
      "date_of_birth": "2000-01-01T00:00:00.000000Z",
      "gender": "male",
      "phone": "0123456789",
      "created_at": "2025-09-20T02:02:33.000000Z"
    }
  }
}
```

#### 5. Cập nhật profile
```http
PUT /api/auth/profile
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "full_name": "Nguyễn Văn A Updated",
  "phone": "0987654321",
  "class": "CNTT02",
  "faculty": "Công nghệ thông tin",
  "course": "2024"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Cập nhật thông tin thành công",
  "data": {
    "user": {
      "id": 1,
      "full_name": "Nguyễn Văn A Updated",
      "email": "student@example.com",
      "role": "student"
    },
    "student": {
      "student_id": "SV001",
      "class": "CNTT02",
      "faculty": "Công nghệ thông tin",
      "course": "2024",
      "date_of_birth": "2000-01-01T00:00:00.000000Z",
      "gender": "male",
      "phone": "0987654321"
    }
  }
}
```

#### 6. Đổi mật khẩu
```http
POST /api/auth/change-password
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "current_password": "oldpassword123",
  "new_password": "newpassword123",
  "new_password_confirmation": "newpassword123"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Đổi mật khẩu thành công. Vui lòng đăng nhập lại."
}
```

### 🏛️ Union Endpoints

#### 7. Lấy danh sách đoàn hội
```http
GET /api/unions
Authorization: Bearer {token}
```

**Query Parameters:**
- `status` (optional): Lọc theo trạng thái đoàn hội (`active` hoặc `inactive`)

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Đoàn Thanh niên",
      "description": "Đoàn Thanh niên Cộng sản Hồ Chí Minh",
      "logo_url": "https://example.com/storage/logos/doan.png",
      "status": "active"
    },
    {
      "id": 2,
      "name": "Hội Sinh viên",
      "description": "Hội Sinh viên Việt Nam",
      "logo_url": "https://example.com/storage/logos/hsv.png",
      "status": "active"
    }
  ]
}
```

**Example Request:**
```bash
# Lấy tất cả đoàn hội
curl -X GET "http://localhost:8000/api/unions" \
  -H "Authorization: Bearer {token}"

# Lấy chỉ đoàn hội đang hoạt động
curl -X GET "http://localhost:8000/api/unions?status=active" \
  -H "Authorization: Bearer {token}"
```

### 🔔 Notification Endpoints

#### 8. Lấy danh sách thông báo
```http
GET /api/student/notifications
Authorization: Bearer {token}
```

**Query Parameters:**
- `type` (optional): Lọc theo loại thông báo (`registration_success`, `unregistration_success`, `attendance_success`)
- `read` (optional): Lọc theo trạng thái đọc (`true` hoặc `false`)
- `page` (optional): Số trang (mặc định: 1)
- `per_page` (optional): Số item mỗi trang (mặc định: 15)

**Response (200):**
```json
{
  "success": true,
  "data": {
    "notifications": [
      {
        "id": 1,
        "type": "registration_success",
        "title": "Đăng ký thành công",
        "message": "Bạn đã đăng ký thành công sự kiện: Hội thảo Công nghệ",
        "data": {
          "event_id": 1,
          "event_title": "Hội thảo Công nghệ",
          "event_start_date": "2025-12-01 09:00:00",
          "event_location": "Hội trường A"
        },
        "is_read": false,
        "read_at": null,
        "created_at": "2025-11-21T10:30:00.000000Z"
      },
      {
        "id": 2,
        "type": "attendance_success",
        "title": "Điểm danh thành công",
        "message": "Bạn đã điểm danh thành công sự kiện: Hội thảo Công nghệ. Bạn nhận được 5 điểm hoạt động.",
        "data": {
          "event_id": 1,
          "event_title": "Hội thảo Công nghệ",
          "activity_points": 5
        },
        "is_read": true,
        "read_at": "2025-11-21T11:00:00.000000Z",
        "created_at": "2025-11-21T10:45:00.000000Z"
      },
      {
        "id": 3,
        "type": "new_event",
        "title": "Sự kiện mới",
        "message": "Có sự kiện mới: Workshop Lập trình. Bắt đầu vào 25/11/2025 14:00",
        "data": {
          "event_id": 2,
          "event_title": "Workshop Lập trình",
          "event_description": "Workshop về lập trình web với Laravel",
          "event_start_date": "2025-11-25 14:00:00",
          "event_end_date": "2025-11-25 17:00:00",
          "event_location": "Phòng Lab A",
          "event_image_url": "https://example.com/storage/events/workshop.jpg",
          "activity_points": 3,
          "union_id": 1,
          "union_name": "Đoàn Thanh niên"
        },
        "is_read": false,
        "read_at": null,
        "created_at": "2025-11-21T12:00:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 3,
      "per_page": 15,
      "total": 42
    },
    "unread_count": 5
  }
}
```

**Example Request:**
```bash
# Lấy tất cả thông báo
curl -X GET "http://localhost:8000/api/student/notifications" \
  -H "Authorization: Bearer {token}"

# Lấy thông báo chưa đọc
curl -X GET "http://localhost:8000/api/student/notifications?read=false" \
  -H "Authorization: Bearer {token}"

# Lấy thông báo đăng ký thành công
curl -X GET "http://localhost:8000/api/student/notifications?type=registration_success" \
  -H "Authorization: Bearer {token}"

# Lấy thông báo sự kiện mới
curl -X GET "http://localhost:8000/api/student/notifications?type=new_event" \
  -H "Authorization: Bearer {token}"
```

#### 9. Đánh dấu thông báo đã đọc
```http
PUT /api/student/notifications/{id}/read
Authorization: Bearer {token}
```

**Path Parameters:**
- `id`: ID của thông báo

**Response (200):**
```json
{
  "success": true,
  "message": "Đã đánh dấu đọc"
}
```

**Example Request:**
```bash
curl -X PUT "http://localhost:8000/api/student/notifications/1/read" \
  -H "Authorization: Bearer {token}"
```

#### 10. Đánh dấu tất cả thông báo đã đọc
```http
PUT /api/student/notifications/read-all
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Đã đánh dấu tất cả thông báo là đã đọc",
  "data": {
    "marked_count": 5
  }
}
```

**Example Request:**
```bash
curl -X PUT "http://localhost:8000/api/student/notifications/read-all" \
  -H "Authorization: Bearer {token}"
```

**Lưu ý:** Thông báo sẽ tự động được tạo khi:
- Có sự kiện mới được publish (gửi cho tất cả sinh viên)
- Đăng ký sự kiện thành công
- Hủy đăng ký sự kiện thành công
- Điểm danh sự kiện thành công

### 🧪 Test Endpoints

#### 11. Test API
```http
GET /api/test
```

**Response (200):**
```json
{
  "message": "API is working!",
  "timestamp": "2025-09-20T02:02:21.524428Z"
}
```

## 📊 Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Response data
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    // Validation errors (if any)
  }
}
```

## 🔢 HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | OK - Request successful |
| 201 | Created - Resource created successfully |
| 400 | Bad Request - Invalid request data |
| 401 | Unauthorized - Authentication required |
| 403 | Forbidden - Access denied |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation failed |
| 500 | Internal Server Error - Server error |

## 🛡️ Error Handling

### Validation Errors (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### Authentication Errors (401)
```json
{
  "success": false,
  "message": "Email hoặc mật khẩu không đúng"
}
```

### Authorization Errors (403)
```json
{
  "success": false,
  "message": "Tài khoản không phải là sinh viên"
}
```

## 🧪 Testing với cURL

### 1. Test API Health
```bash
curl -X GET http://localhost:8000/api/test
```

### 2. Đăng ký sinh viên
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "full_name": "Nguyễn Văn A",
    "email": "student@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "student_id": "SV001",
    "class": "CNTT01",
    "faculty": "Công nghệ thông tin",
    "course": "2024",
    "date_of_birth": "2000-01-01",
    "gender": "male"
  }'
```

### 3. Đăng nhập
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "student@example.com",
    "password": "password123"
  }'
```

### 4. Lấy profile (với token)
```bash
curl -X GET http://localhost:8000/api/auth/profile \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### 5. Đăng xuất
```bash
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 📱 Mobile App Integration

### Flutter Example
```dart
// Login
final response = await http.post(
  Uri.parse('http://localhost:8000/api/auth/login'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({
    'email': 'student@example.com',
    'password': 'password123',
  }),
);

// Store token
final data = jsonDecode(response.body);
final token = data['data']['token'];
```

### React Native Example
```javascript
// Login
const login = async (email, password) => {
  const response = await fetch('http://localhost:8000/api/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ email, password }),
  });
  
  const data = await response.json();
  return data;
};
```

## 🔧 Swagger UI

Truy cập Swagger UI để test API trực tiếp:
```
http://localhost:8000/api/documentation
```

## 📞 Support

Nếu có vấn đề với API:
1. Kiểm tra logs trong `storage/logs/`
2. Sử dụng Swagger UI để test
3. Tạo issue trên GitHub
4. Liên hệ team phát triển

---

**API Documentation v1.0** - CMS Đoàn Hội 📱

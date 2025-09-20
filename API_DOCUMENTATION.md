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

### 🧪 Test Endpoints

#### 7. Test API
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

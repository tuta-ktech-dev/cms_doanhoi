# Mobile Attendance System - QR Code Integration (Flutter)

## Tổng quan

Hướng dẫn tích hợp hệ thống điểm danh QR code cho ứng dụng Flutter. Tài liệu tập trung vào interface, API calls và data flow cho việc implement QR attendance feature.

## 1. Prerequisites (Điều kiện tiên quyết)

### 1.1 Authentication Setup
- User đã đăng nhập thành công với Bearer token
- User role phải là "student"
- Token được lưu trong secure storage

### 1.2 Permissions Required
- Camera permission để scan QR code
- User đã đăng ký và được duyệt tham gia sự kiện

### 1.3 Dependencies Needed
```yaml
dependencies:
  qr_code_scanner: ^1.0.1
  http: ^1.1.0
  shared_preferences: ^2.2.2
  permission_handler: ^11.0.1
```

## 2. QR Code Scanning Interface

### 2.1 QR Scanner Screen

**Interface Requirements:**
- Full screen camera view với overlay guide
- Green border overlay cho QR code detection
- Text instructions: "Đặt camera vào khung QR code"
- Loading overlay khi đang xử lý
- Error handling UI cho invalid QR codes

**Input:**
- Camera stream từ device
- QR code data (JSON string)

**Output:**
- Parsed QR data object hoặc error message
- Navigation to success/error screen

### 2.2 QR Data Structure

**Expected Input Format:**
```json
{
  "token": "abc123def456ghi789jkl012mno345pqr678stu901vwx234yz",
  "event_id": 1,
  "expires_at": "2024-12-15T09:30:00.000000Z"
}
```

**Validation Rules:**
- `token`: Required, string, length = 32 characters
- `event_id`: Required, integer > 0
- `expires_at`: Required, valid ISO8601 datetime string
- Token chưa expired (expires_at > current time)
- Token còn ít nhất 5 giây trước khi expire

### 2.3 Camera Permission Interface

**Permission Request Flow:**
1. Check current permission status
2. If denied → Show explanation dialog
3. Request permission from user
4. Handle granted/denied/permanently_denied states
5. Navigate to settings if permanently denied

**Error Handling:**
- Permission denied → Show message "Cần quyền camera để quét QR"
- Permission permanently denied → Show message với button "Mở Settings"

## 3. Attendance API Integration

### 3.1 Scan QR API Interface

**Endpoint:** `POST /api/scan-qr`

**Request Interface:**
```dart
class ScanQRRequest {
  final String token;

  ScanQRRequest({required this.token});

  Map<String, dynamic> toJson() => {
    'token': token,
  };
}
```

**Request Headers:**
```
Authorization: Bearer {user_token}
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "token": "abc123def456ghi789jkl012mno345pqr678stu901vwx234yz"
}
```

### 3.2 API Response Models

**Success Response (200):**
```dart
class AttendanceResponse {
  final bool success;
  final String message;
  final AttendanceData data;

  AttendanceResponse({
    required this.success,
    required this.message,
    required this.data,
  });
}

class AttendanceData {
  final Attendance attendance;
  final Event event;
  final int activityPointsEarned;

  AttendanceData({
    required this.attendance,
    required this.event,
    required this.activityPointsEarned,
  });
}

class Attendance {
  final int id;
  final String status; // "present"
  final String attendedAt;

  Attendance({
    required this.id,
    required this.status,
    required this.attendedAt,
  });
}

class Event {
  final int id;
  final String title;
  final int activityPoints;

  Event({
    required this.id,
    required this.title,
    required this.activityPoints,
  });
}
```

**Response JSON:**
```json
{
  "success": true,
  "message": "Điểm danh thành công",
  "data": {
    "attendance": {
      "id": 456,
      "status": "present",
      "attended_at": "2024-12-15 09:15:00"
    },
    "event": {
      "id": 1,
      "title": "Hội thảo Công nghệ 4.0",
      "activity_points": 5
    },
    "activity_points_earned": 5
  }
}
```

### 3.3 Error Response Models

**Error Response Interface:**
```dart
class ErrorResponse {
  final bool success;
  final String message;

  ErrorResponse({
    required this.success,
    required this.message,
  });
}
```

**Common Error Cases:**

1. **Invalid Token (400):**
```json
{
  "success": false,
  "message": "Token không hợp lệ hoặc đã hết hạn"
}
```

2. **Not Student (403):**
```json
{
  "success": false,
  "message": "Chỉ sinh viên mới có thể điểm danh"
}
```

3. **Not Registered (400):**
```json
{
  "success": false,
  "message": "Bạn chưa đăng ký sự kiện này hoặc đăng ký chưa được duyệt"
}
```

4. **Already Attended (400):**
```json
{
  "success": false,
  "message": "Bạn đã điểm danh rồi"
}
```

5. **Event Ended (400):**
```json
{
  "success": false,
  "message": "Sự kiện đã kết thúc"
}
```

### 3.4 Service Layer Interface

**Attendance Service Interface:**
```dart
abstract class AttendanceService {
  Future<AttendanceResponse> scanQR(String token);
  Future<bool> validateToken(String token);
  Future<void> updateLocalProfile(int pointsEarned);
  Future<void> cacheAttendanceData(AttendanceData data);
}

class AttendanceServiceImpl implements AttendanceService {
  final HttpClient _httpClient;
  final LocalStorage _localStorage;

  AttendanceServiceImpl(this._httpClient, this._localStorage);

  @override
  Future<AttendanceResponse> scanQR(String token) async {
    // Implementation logic
  }

  @override
  Future<bool> validateToken(String token) async {
    // Implementation logic
  }

  @override
  Future<void> updateLocalProfile(int pointsEarned) async {
    // Implementation logic
  }

  @override
  Future<void> cacheAttendanceData(AttendanceData data) async {
    // Implementation logic
  }
}
```

### 3.5 Data Flow Logic

**Input Processing Flow:**
1. Receive QR scan result (String)
2. Parse JSON to QRData object
3. Validate QR data structure and expiration
4. Extract token for API call
5. Send to attendance service
6. Handle response and update UI state

**Success Flow:**
1. API returns success response
2. Parse response to AttendanceData
3. Update local user profile (+activity points)
4. Cache attendance data locally
5. Show success screen with details
6. Navigate back to event list

**Error Flow:**
1. API returns error response
2. Show error message to user
3. Log error for debugging
4. Allow retry or cancel operation

## 4. UI State Management

### 4.1 Screen States

**Attendance Screen States:**
```dart
enum AttendanceScreenState {
  scanning,      // Đang quét QR
  processing,    // Đang xử lý API call
  success,       // Điểm danh thành công
  error,         // Có lỗi xảy ra
  permissionDenied, // Camera permission bị từ chối
}
```

**State Data Models:**
```dart
class AttendanceScreenData {
  final AttendanceScreenState state;
  final String? errorMessage;
  final AttendanceData? successData;
  final bool hasPermission;

  AttendanceScreenData({
    required this.state,
    this.errorMessage,
    this.successData,
    required this.hasPermission,
  });
}
```
# ⚡ Quick Start - CMS Đoàn Hội

## 🚀 Setup nhanh với SQLite (5 phút)

### 1. Clone và cài đặt
```bash
git clone <repository-url>
cd cms_doanhoi/src
composer install
```

### 2. Cấu hình nhanh
```bash
# Copy env file
cp .env.example .env

# Generate key
php artisan key:generate

# Tạo SQLite database
touch database/database.sqlite
```

### 3. Cấu hình SQLite trong .env
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/your/project/database/database.sqlite
```

**Hoặc sử dụng đường dẫn tương đối:**
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

### 4. Chạy migrations và seed
```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
```

### 5. Chạy server
```bash
php artisan serve
```

## 🎯 Truy cập ứng dụng

- **Website**: http://localhost:8000
- **Admin Panel**: http://localhost:8000/admin
- **API Documentation**: http://localhost:8000/api/documentation

## 👤 Tài khoản mặc định

### Admin
- Email: `admin@example.com`
- Password: `password`

### Union Manager  
- Email: `union@example.com`
- Password: `password`

## 🧪 Test API nhanh

```bash
# Test API health
curl http://localhost:8000/api/test

# Đăng ký sinh viên
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "Test Student",
    "email": "test@example.com",
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

## 🔧 Troubleshooting

### Lỗi database không tìm thấy
```bash
# Kiểm tra file database có tồn tại không
ls -la database/database.sqlite

# Nếu không có, tạo lại
touch database/database.sqlite
```

### Lỗi permission
```bash
chmod 664 database/database.sqlite
chmod 775 database/
```

### Reset database
```bash
rm database/database.sqlite
touch database/database.sqlite
php artisan migrate:fresh --seed
```

## 📱 Features có sẵn

✅ **Admin Panel** - Quản lý toàn bộ hệ thống  
✅ **Student Management** - Quản lý sinh viên  
✅ **Event Management** - Quản lý sự kiện  
✅ **Union Management** - Quản lý đoàn hội  
✅ **Registration System** - Hệ thống đăng ký  
✅ **Attendance Tracking** - Theo dõi điểm danh  
✅ **Statistics & Reports** - Thống kê và báo cáo  
✅ **Excel Export** - Xuất dữ liệu Excel  
✅ **API Authentication** - API với Swagger docs  
✅ **Role & Permission** - Hệ thống phân quyền  

## 🎉 Hoàn thành!

Bây giờ bạn có thể:
1. Truy cập admin panel để quản lý
2. Test API với Swagger UI
3. Đăng ký sinh viên mới
4. Tạo sự kiện và quản lý

**Chúc bạn sử dụng vui vẻ! 🚀**

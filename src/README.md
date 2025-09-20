# CMS Đoàn Hội - Student Management System

Hệ thống quản lý sinh viên và sự kiện đoàn hội được xây dựng với Laravel 11 và Filament PHP.

## 🚀 Features

### 👥 User Management
- **Admin Panel**: Quản lý toàn bộ hệ thống
- **Union Manager**: Quản lý đoàn hội và sự kiện
- **Student**: Đăng ký và tham gia sự kiện

### 📅 Event Management
- Tạo và quản lý sự kiện
- Đăng ký tham gia sự kiện
- Điểm danh và theo dõi tham gia
- Tính điểm rèn luyện tự động

### 📊 Statistics & Reports
- Thống kê chi tiết sự kiện
- Báo cáo điểm danh
- Xuất dữ liệu Excel
- Dashboard với biểu đồ

### 🔐 API Authentication
- RESTful API với Laravel Sanctum
- Swagger documentation
- Token-based authentication
- Student registration và login

## 🛠️ Technology Stack

- **Backend**: Laravel 11, PHP 8.2+
- **Admin Panel**: Filament PHP 3
- **Database**: MySQL/SQLite
- **API**: Laravel Sanctum
- **Documentation**: Swagger UI
- **Frontend**: Blade templates, Alpine.js

## 📋 Requirements

- PHP 8.2 or higher
- Composer
- MySQL 5.7+ or SQLite
- Node.js & NPM (for assets)

## 🚀 Installation

### 1. Clone Repository
```bash
git clone <repository-url>
cd cms_doanhoi/src
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Configuration (SQLite)
```bash
# Tạo file database SQLite
touch database/database.sqlite
```

Cập nhật file `.env`:
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
```

### 5. Database Migration & Seeding
```bash
php artisan migrate
php artisan db:seed
```

### 6. Storage Link
```bash
php artisan storage:link
```

### 7. Build Assets
```bash
npm run build
```

## 🏃‍♂️ Running the Application

### Development Server
```bash
php artisan serve
```
Truy cập: `http://localhost:8000`

### Admin Panel
- URL: `http://localhost:8000/admin`
- Default Admin: `admin@example.com` / `password`

### API Documentation
- Swagger UI: `http://localhost:8000/api/documentation`
- API Base URL: `http://localhost:8000/api`

## 📱 API Endpoints

### Authentication
- `POST /api/auth/register` - Đăng ký sinh viên
- `POST /api/auth/login` - Đăng nhập
- `POST /api/auth/logout` - Đăng xuất
- `GET /api/auth/profile` - Thông tin profile
- `PUT /api/auth/profile` - Cập nhật profile
- `POST /api/auth/change-password` - Đổi mật khẩu

### Test
- `GET /api/test` - Kiểm tra API

## 🗄️ Database Structure

### Core Tables
- `users` - Thông tin người dùng
- `students` - Thông tin sinh viên
- `unions` - Đoàn hội
- `union_managers` - Quản lý đoàn hội
- `events` - Sự kiện
- `event_registrations` - Đăng ký sự kiện
- `event_attendance` - Điểm danh
- `event_comments` - Bình luận sự kiện

### Permission System
- `roles` - Vai trò
- `permissions` - Quyền hạn
- `user_roles` - Gán vai trò cho user
- `role_permissions` - Gán quyền cho vai trò

## 👤 Default Users

### Admin
- Email: `admin@example.com`
- Password: `password`
- Role: Admin

### Union Manager
- Email: `union@example.com`
- Password: `password`
- Role: Union Manager

## 🔧 Development Commands

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Generate Swagger Documentation
```bash
php artisan l5-swagger:generate
```

### Run Tests
```bash
php artisan test
```

### Database Reset
```bash
php artisan migrate:fresh --seed
```

## 📊 Features Overview

### Admin Features
- Quản lý sinh viên, đoàn hội, sự kiện
- Thống kê và báo cáo
- Xuất dữ liệu Excel
- Quản lý quyền hạn

### Union Manager Features
- Quản lý sự kiện của đoàn hội
- Duyệt đăng ký sinh viên
- Điểm danh sự kiện
- Thống kê đoàn hội

### Student Features
- Xem danh sách sự kiện
- Đăng ký tham gia sự kiện
- Xem lịch sử tham gia
- Theo dõi điểm rèn luyện

## 🚀 Deployment

### Production Setup
1. Cập nhật `.env` với production settings
2. Set `APP_ENV=production`
3. Set `APP_DEBUG=false`
4. Chạy `php artisan config:cache`
5. Chạy `php artisan route:cache`
6. Chạy `php artisan view:cache`

### Web Server Configuration
- Apache/Nginx với PHP-FPM
- SSL certificate
- Database optimization

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## 📝 License

This project is licensed under the MIT License.

## 📞 Support

Nếu có vấn đề, vui lòng tạo issue trên GitHub hoặc liên hệ team phát triển.

---

**CMS Đoàn Hội** - Hệ thống quản lý sinh viên và sự kiện đoàn hội hiện đại 🎓
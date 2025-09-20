# 🚀 Hướng dẫn cài đặt CMS Đoàn Hội

## 📋 Yêu cầu hệ thống

### Minimum Requirements
- **PHP**: 8.2 hoặc cao hơn
- **Composer**: 2.0+
- **Database**: MySQL 5.7+ hoặc SQLite
- **Web Server**: Apache/Nginx
- **Memory**: 512MB RAM (minimum)
- **Storage**: 100MB free space

### Recommended
- **PHP**: 8.3
- **Memory**: 1GB RAM
- **Database**: MySQL 8.0+
- **Node.js**: 18+ (cho build assets)

## 🔧 Cài đặt từng bước

### Bước 1: Clone Repository
```bash
git clone <repository-url>
cd cms_doanhoi/src
```

### Bước 2: Cài đặt Dependencies
```bash
# Cài đặt PHP packages
composer install

# Cài đặt Node.js packages
npm install
```

### Bước 3: Cấu hình Environment
```bash
# Copy file environment
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Bước 4: Cấu hình Database

#### Option 1: MySQL
Cập nhật file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cms_doanhoi
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Tạo database:
```sql
CREATE DATABASE cms_doanhoi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Option 2: SQLite (Development)
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
```

Tạo file database:
```bash
touch database/database.sqlite
```

### Bước 5: Chạy Migrations
```bash
# Chạy migrations
php artisan migrate

# Seed dữ liệu mẫu
php artisan db:seed
```

### Bước 6: Cấu hình Storage
```bash
# Tạo symbolic link cho storage
php artisan storage:link
```

### Bước 7: Build Assets
```bash
# Build assets cho production
npm run build

# Hoặc watch cho development
npm run dev
```

## 🏃‍♂️ Chạy ứng dụng

### Development Mode
```bash
php artisan serve
```
Truy cập: `http://localhost:8000`

### Production Mode
Cấu hình web server (Apache/Nginx) để point đến thư mục `public/`

## 🔐 Tài khoản mặc định

### Admin
- **Email**: `admin@example.com`
- **Password**: `password`
- **Quyền**: Full access

### Union Manager
- **Email**: `union@example.com`
- **Password**: `password`
- **Quyền**: Quản lý đoàn hội

## 📱 API Testing

### Swagger UI
Truy cập: `http://localhost:8000/api/documentation`

### Test API với cURL
```bash
# Test API health
curl -X GET http://localhost:8000/api/test

# Đăng ký sinh viên
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
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

## 🐛 Troubleshooting

### Lỗi thường gặp

#### 1. Permission denied
```bash
# Fix storage permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

#### 2. Composer memory limit
```bash
# Tăng memory limit
php -d memory_limit=2G /usr/local/bin/composer install
```

#### 3. Database connection failed
- Kiểm tra thông tin database trong `.env`
- Đảm bảo database server đang chạy
- Kiểm tra user có quyền truy cập database

#### 4. Class not found
```bash
# Clear cache và regenerate autoload
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

#### 5. Storage link failed
```bash
# Xóa link cũ và tạo lại
rm public/storage
php artisan storage:link
```

### Debug Mode
Để debug, set trong `.env`:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

## 🔧 Development Tools

### Useful Commands
```bash
# Clear all cache
php artisan optimize:clear

# Generate Swagger docs
php artisan l5-swagger:generate

# Run tests
php artisan test

# Reset database
php artisan migrate:fresh --seed

# Check routes
php artisan route:list

# Check config
php artisan config:show
```

### IDE Setup
- **VS Code**: Cài extension Laravel Extension Pack
- **PHPStorm**: Enable Laravel plugin
- **Sublime**: Cài Laravel snippets

## 📊 Performance Optimization

### Production Optimizations
```bash
# Cache config
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

### Database Optimization
- Enable query caching
- Add indexes cho các cột thường query
- Optimize database configuration

## 🚀 Deployment

### Server Requirements
- PHP 8.2+ với extensions: BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PCRE, PDO, Tokenizer, XML
- Web server (Apache/Nginx)
- Database server (MySQL/PostgreSQL)
- SSL certificate (recommended)

### Deployment Steps
1. Upload code lên server
2. Cài đặt dependencies: `composer install --no-dev --optimize-autoloader`
3. Cấu hình `.env` cho production
4. Chạy migrations: `php artisan migrate --force`
5. Cache optimization: `php artisan optimize`
6. Cấu hình web server
7. Setup SSL certificate
8. Configure cron jobs (nếu cần)

## 📞 Support

Nếu gặp vấn đề trong quá trình cài đặt:
1. Kiểm tra logs trong `storage/logs/`
2. Tạo issue trên GitHub
3. Liên hệ team phát triển

---

**Chúc bạn cài đặt thành công! 🎉**

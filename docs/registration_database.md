# Cấu trúc Database cho Quản lý Đăng ký

## Bảng Event_Registrations (Đăng ký sự kiện)

```sql
CREATE TABLE `event_registrations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `event_id` BIGINT UNSIGNED NOT NULL,
  `student_id` BIGINT UNSIGNED NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
  `registration_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_by` BIGINT UNSIGNED NULL,
  `approved_at` TIMESTAMP NULL,
  `rejection_reason` TEXT NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  UNIQUE KEY `uk_event_student` (`event_id`, `student_id`),
  INDEX `idx_registrations_status` (`status`),
  INDEX `idx_registrations_event` (`event_id`),
  INDEX `idx_registrations_student` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Bảng Registration_Logs (Nhật ký đăng ký)

```sql
CREATE TABLE `registration_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `registration_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `action` VARCHAR(50) NOT NULL,
  `old_status` ENUM('pending', 'approved', 'rejected', 'cancelled') NULL,
  `new_status` ENUM('pending', 'approved', 'rejected', 'cancelled') NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`registration_id`) REFERENCES `event_registrations` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  INDEX `idx_registration_logs_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Bảng Registration_Notifications (Thông báo đăng ký)

```sql
CREATE TABLE `registration_notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `registration_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `is_read` BOOLEAN NOT NULL DEFAULT FALSE,
  `read_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`registration_id`) REFERENCES `event_registrations` (`id`) ON DELETE CASCADE,
  INDEX `idx_notifications_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Mối quan hệ và Lưu ý

1. **Quan hệ đăng ký và sự kiện**:
   - Mỗi đăng ký thuộc về một sự kiện (foreign key `event_id` trong bảng `event_registrations`)
   - Mỗi đăng ký thuộc về một sinh viên (foreign key `student_id` trong bảng `event_registrations`)
   - Mỗi sinh viên chỉ có thể đăng ký một lần cho mỗi sự kiện (unique key `uk_event_student`)

2. **Trạng thái đăng ký**:
   - `pending`: Đang chờ duyệt
   - `approved`: Đã duyệt
   - `rejected`: Từ chối
   - `cancelled`: Đã hủy (do sinh viên hoặc người quản lý)

3. **Nhật ký đăng ký**:
   - Ghi lại mọi thay đổi trạng thái của đăng ký
   - Lưu thông tin người thực hiện thay đổi
   - Hỗ trợ theo dõi lịch sử và kiểm tra

4. **Thông báo đăng ký**:
   - Gửi thông báo cho sinh viên khi trạng thái đăng ký thay đổi
   - Theo dõi việc sinh viên đã đọc thông báo hay chưa

5. **Hiệu suất**:
   - Đã tạo các index cho các cột thường xuyên tìm kiếm
   - Sử dụng các kiểu dữ liệu phù hợp cho từng trường

## Các truy vấn thường dùng

### 1. Lấy danh sách đăng ký theo sự kiện và trạng thái

```sql
SELECT er.*, s.student_id as student_code, u.full_name, u.email, u.phone
FROM event_registrations er
JOIN students s ON er.student_id = s.id
JOIN users u ON s.user_id = u.id
WHERE er.event_id = ? AND er.status = ?
ORDER BY er.registration_time DESC;
```

### 2. Thống kê số lượng đăng ký theo trạng thái cho một sự kiện

```sql
SELECT status, COUNT(*) as count
FROM event_registrations
WHERE event_id = ?
GROUP BY status;
```

### 3. Cập nhật trạng thái đăng ký

```sql
UPDATE event_registrations
SET status = ?, 
    approved_by = ?, 
    approved_at = CURRENT_TIMESTAMP,
    rejection_reason = ?,
    notes = ?,
    updated_at = CURRENT_TIMESTAMP
WHERE id = ?;
```

### 4. Thêm nhật ký khi cập nhật trạng thái

```sql
INSERT INTO registration_logs 
(registration_id, user_id, action, old_status, new_status, notes)
VALUES (?, ?, 'update_status', ?, ?, ?);
```

### 5. Gửi thông báo khi cập nhật trạng thái

```sql
INSERT INTO registration_notifications 
(registration_id, title, content)
VALUES (?, ?, ?);
```

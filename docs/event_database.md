# Cấu trúc Database cho Quản lý Sự kiện

## Bảng Events (Sự kiện)

```sql
CREATE TABLE `events` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `content` LONGTEXT NULL,
  `start_time` DATETIME NOT NULL,
  `end_time` DATETIME NOT NULL,
  `location` VARCHAR(255) NULL,
  `union_id` BIGINT UNSIGNED NOT NULL,
  `created_by` BIGINT UNSIGNED NOT NULL,
  `activity_points` DECIMAL(5,2) NULL DEFAULT 0,
  `status` ENUM('upcoming', 'ongoing', 'completed', 'cancelled') NOT NULL DEFAULT 'upcoming',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`union_id`) REFERENCES `unions` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  INDEX `idx_events_status` (`status`),
  INDEX `idx_events_start_time` (`start_time`),
  INDEX `idx_events_union` (`union_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Bảng Event_Registrations (Đăng ký sự kiện)

```sql
CREATE TABLE `event_registrations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `event_id` BIGINT UNSIGNED NOT NULL,
  `student_id` BIGINT UNSIGNED NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `registration_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_by` BIGINT UNSIGNED NULL,
  `approved_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  UNIQUE KEY `uk_event_student` (`event_id`, `student_id`),
  INDEX `idx_registrations_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Bảng Event_Attendances (Điểm danh sự kiện)

```sql
CREATE TABLE `event_attendances` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `event_id` BIGINT UNSIGNED NOT NULL,
  `student_id` BIGINT UNSIGNED NOT NULL,
  `check_in_time` TIMESTAMP NULL,
  `check_in_by` BIGINT UNSIGNED NULL,
  `points_awarded` DECIMAL(5,2) NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`check_in_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  UNIQUE KEY `uk_event_student_attendance` (`event_id`, `student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Bảng Event_Comments (Bình luận sự kiện)

```sql
CREATE TABLE `event_comments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `event_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `parent_id` BIGINT UNSIGNED NULL,
  `content` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`parent_id`) REFERENCES `event_comments` (`id`) ON DELETE SET NULL,
  INDEX `idx_comments_parent` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Mối quan hệ và Lưu ý

1. **Quan hệ sự kiện và đoàn hội**:
   - Mỗi sự kiện thuộc về một đoàn hội (foreign key `union_id` trong bảng `events`)
   - Người tạo sự kiện là một người dùng trong hệ thống (foreign key `created_by` trong bảng `events`)

2. **Đăng ký và điểm danh**:
   - Mỗi sinh viên chỉ có thể đăng ký một lần cho mỗi sự kiện (unique key `uk_event_student`)
   - Điểm danh được ghi lại khi sinh viên tham gia sự kiện
   - Điểm rèn luyện được cộng dựa trên việc tham gia sự kiện

3. **Bình luận**:
   - Bình luận có thể phân cấp (parent_id cho phép trả lời bình luận)
   - Người dùng có thể là sinh viên hoặc quản lý đoàn hội

4. **Hiệu suất**:
   - Đã tạo các index cho các cột thường xuyên tìm kiếm
   - Sử dụng các kiểu dữ liệu phù hợp cho từng trường

5. **Lưu trữ nội dung**:
   - Nội dung sự kiện được lưu dưới dạng HTML (LONGTEXT)

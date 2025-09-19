<?php

namespace App\Enums;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case UNION_MANAGER = 'union_manager';
    case STUDENT = 'student';
    
    /**
     * Lấy mô tả cho role
     */
    public function description(): string
    {
        return match($this) {
            self::ADMIN => 'Quản trị viên hệ thống',
            self::UNION_MANAGER => 'Quản lý đoàn hội',
            self::STUDENT => 'Sinh viên',
        };
    }
    
    /**
     * Lấy danh sách tất cả các role dưới dạng mảng key-value
     */
    public static function toArray(): array
    {
        return [
            self::ADMIN->value => self::ADMIN->description(),
            self::UNION_MANAGER->value => self::UNION_MANAGER->description(),
            self::STUDENT->value => self::STUDENT->description(),
        ];
    }
}

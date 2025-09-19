<?php

namespace App\Enums;

enum PermissionEnum: string
{
    // Quyền quản lý người dùng
    case VIEW_USERS = 'view_users';
    case CREATE_USERS = 'create_users';
    case EDIT_USERS = 'edit_users';
    case DELETE_USERS = 'delete_users';
    
    // Quyền quản lý đoàn hội
    case VIEW_UNIONS = 'view_unions';
    case CREATE_UNIONS = 'create_unions';
    case EDIT_UNIONS = 'edit_unions';
    case DELETE_UNIONS = 'delete_unions';
    
    // Quyền quản lý sự kiện
    case VIEW_EVENTS = 'view_events';
    case CREATE_EVENTS = 'create_events';
    case EDIT_EVENTS = 'edit_events';
    case DELETE_EVENTS = 'delete_events';
    
    // Quyền quản lý đăng ký
    case VIEW_REGISTRATIONS = 'view_registrations';
    case APPROVE_REGISTRATIONS = 'approve_registrations';
    case REJECT_REGISTRATIONS = 'reject_registrations';
    
    // Quyền quản lý vai trò và phân quyền
    case VIEW_ROLES = 'view_roles';
    case CREATE_ROLES = 'create_roles';
    case EDIT_ROLES = 'edit_roles';
    case DELETE_ROLES = 'delete_roles';
    
    /**
     * Lấy mô tả cho permission
     */
    public function description(): string
    {
        return match($this) {
            // Quyền quản lý người dùng
            self::VIEW_USERS => 'Xem danh sách người dùng',
            self::CREATE_USERS => 'Tạo người dùng mới',
            self::EDIT_USERS => 'Chỉnh sửa người dùng',
            self::DELETE_USERS => 'Xóa người dùng',
            
            // Quyền quản lý đoàn hội
            self::VIEW_UNIONS => 'Xem danh sách đoàn hội',
            self::CREATE_UNIONS => 'Tạo đoàn hội mới',
            self::EDIT_UNIONS => 'Chỉnh sửa đoàn hội',
            self::DELETE_UNIONS => 'Xóa đoàn hội',
            
            // Quyền quản lý sự kiện
            self::VIEW_EVENTS => 'Xem danh sách sự kiện',
            self::CREATE_EVENTS => 'Tạo sự kiện mới',
            self::EDIT_EVENTS => 'Chỉnh sửa sự kiện',
            self::DELETE_EVENTS => 'Xóa sự kiện',
            
            // Quyền quản lý đăng ký
            self::VIEW_REGISTRATIONS => 'Xem danh sách đăng ký',
            self::APPROVE_REGISTRATIONS => 'Duyệt đăng ký',
            self::REJECT_REGISTRATIONS => 'Từ chối đăng ký',
            
            // Quyền quản lý vai trò và phân quyền
            self::VIEW_ROLES => 'Xem danh sách vai trò',
            self::CREATE_ROLES => 'Tạo vai trò mới',
            self::EDIT_ROLES => 'Chỉnh sửa vai trò',
            self::DELETE_ROLES => 'Xóa vai trò',
        };
    }
    
    /**
     * Lấy danh sách tất cả các permission dưới dạng mảng key-value
     */
    public static function toArray(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->value] = $case->description();
        }
        return $result;
    }
    
    /**
     * Lấy danh sách quyền cho Admin
     */
    public static function getAdminPermissions(): array
    {
        return self::cases();
    }
    
    /**
     * Lấy danh sách quyền cho Union Manager
     */
    public static function getUnionManagerPermissions(): array
    {
        return [
            // Chỉ xem được sự kiện của đoàn hội mình quản lý
            self::VIEW_EVENTS,
            self::CREATE_EVENTS,
            self::EDIT_EVENTS,
            // Chỉ xem được đăng ký của sự kiện mình tạo
            self::VIEW_REGISTRATIONS,
            self::APPROVE_REGISTRATIONS,
            self::REJECT_REGISTRATIONS,
        ];
    }
    
    /**
     * Lấy danh sách quyền cho Student
     */
    public static function getStudentPermissions(): array
    {
        return [
            self::VIEW_EVENTS,
        ];
    }
}

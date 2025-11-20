<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\Login;

class CustomLogin extends Login
{
    public function getHeading(): string
    {
        return 'Đăng nhập hệ thống';
    }

    public function getSubheading(): string
    {
        return 'CMS Đoàn Hội - Trường Đại học Tài nguyên và Môi trường TP.HCM';
    }

    public function getLogoUrl(): ?string
    {
        return 'https://hcmunre.edu.vn/upload/elfinder/Trang%20GioiThieu/Logo-truong-hcmunre.png';
    }
}

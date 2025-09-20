<?php

namespace App\Filament\Resources\Students;

use App\Enums\PermissionEnum;
use App\Filament\Resources\Students\Pages\ListStudents;
use App\Filament\Resources\Students\Pages\ViewStudent;
use App\Filament\Resources\Students\Tables\StudentsTable;
use App\Models\Student;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;
    
    protected static ?string $navigationLabel = 'Sinh viên';
    
    protected static ?string $modelLabel = 'Sinh viên';
    
    protected static ?string $pluralModelLabel = 'Quản lý sinh viên';
    
    public static function getNavigationGroup(): ?string
    {
        return 'Quản lý hệ thống';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return StudentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudents::route('/'),
            'view' => ViewStudent::route('/{record}'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return false; // Sinh viên được tạo thông qua User
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
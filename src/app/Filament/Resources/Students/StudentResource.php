<?php

namespace App\Filament\Resources\Students;

use App\Enums\PermissionEnum;
use App\Filament\Resources\Students\Pages\CreateStudent;
use App\Filament\Resources\Students\Pages\EditStudent;
use App\Filament\Resources\Students\Pages\ListStudents;
use App\Filament\Resources\Students\Schemas\StudentForm;
use App\Filament\Resources\Students\Tables\StudentsTable;
use App\Models\Student;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;
    
    public static function getNavigationGroup(): ?string
    {
        return 'Quản lý người dùng';
    }

    public static function form(Schema $schema): Schema
    {
        return StudentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudents::route('/'),
            'create' => CreateStudent::route('/create'),
            'edit' => EditStudent::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission(PermissionEnum::VIEW_USERS->value) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission(PermissionEnum::CREATE_USERS->value) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission(PermissionEnum::EDIT_USERS->value) ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission(PermissionEnum::DELETE_USERS->value) ?? false;
    }
}

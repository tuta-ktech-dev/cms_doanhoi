<?php

namespace App\Filament\Resources\Unions\RelationManagers;

use App\Models\User;
use App\Models\UnionManager;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ManagersRelationManager extends RelationManager
{
    protected static string $relationship = 'managers';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Người quản lý')
                    ->options(User::where('role', 'union_manager')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Select::make('position')
                    ->label('Chức vụ')
                    ->options([
                        'Chủ nhiệm' => 'Chủ nhiệm',
                        'Phó chủ nhiệm' => 'Phó chủ nhiệm',
                        'Thư ký' => 'Thư ký',
                        'Thành viên' => 'Thành viên',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                ImageColumn::make('user.avatar')
                    ->label('Ảnh đại diện')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->user->name ?? 'Unknown'))
                    ->size(40),
                TextColumn::make('user.name')
                    ->label('Tên')
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('position')
                    ->label('Chức vụ')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

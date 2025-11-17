<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Models\Union;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('union_id')
                    ->label('Đoàn hội')
                    ->options(function () {
                        $user = auth()->user();
                        if ($user->isAdmin()) {
                            return Union::all()->pluck('name', 'id');
                        }
                        if ($user->isUnionManager()) {
                            $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
                            return Union::whereIn('id', $userUnionIds)->pluck('name', 'id');
                        }
                        return [];
                    })
                    ->required()
                    ->searchable(),

                TextInput::make('title')
                    ->label('Tiêu đề')
                    ->required()
                    ->maxLength(255),

                TextInput::make('description')
                    ->label('Mô tả ngắn')
                    ->maxLength(500),

                FileUpload::make('image')
                    ->label('Hình ảnh')
                    ->image()
                    ->imageEditor()
                    ->directory('events')
                    ->visibility('public')
                    ->maxSize(2048) // 2MB
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('16:9'),

                RichEditor::make('content')
                    ->label('Nội dung chi tiết')
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'link',
                        'bulletList',
                        'orderedList',
                        'h2',
                        'h3',
                        'blockquote',
                    ])
                    ->columnSpanFull()
                    ->extraInputAttributes(['style' => 'min-height: 40rem; max-height: 50vh; overflow-y: auto;']),



                DateTimePicker::make('start_date')
                    ->label('Ngày bắt đầu')
                    ->required()
                    ->native(false),

                DateTimePicker::make('end_date')
                    ->label('Ngày kết thúc')
                    ->required()
                    ->native(false)
                    ->after('start_date'),

                TextInput::make('location')
                    ->label('Địa điểm')
                    ->maxLength(255),

                TextInput::make('max_participants')
                    ->label('Số lượng tối đa')
                    ->numeric()
                    ->minValue(1),

                TextInput::make('activity_points')
                    ->label('Điểm rèn luyện')
                    ->numeric()
                    ->step(0.1)
                    ->minValue(0)
                    ->default(0),

                TextInput::make('budget')
                    ->label('Kinh phí tổ chức')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0)
                    ->prefix('₫')
                    ->placeholder('Nhập kinh phí (VND)'),

                Select::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'draft' => 'Bản nháp',
                        'published' => 'Đã xuất bản',
                        'cancelled' => 'Đã hủy',
                    ])
                    ->default('draft')
                    ->required(),

                Toggle::make('is_registration_open')
                    ->label('Mở đăng ký')
                    ->default(true),

                DateTimePicker::make('registration_deadline')
                    ->label('Hạn đăng ký')
                    ->native(false),
            ]);
    }
}

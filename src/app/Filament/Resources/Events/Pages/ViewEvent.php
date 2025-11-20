<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEvent extends ViewRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $canShowQR = $user && ($user->isUnionManager() || $user->isAdmin());

        $actions = [
            EditAction::make(),
        ];

        if ($canShowQR) {
            // Check if UNION_MANAGER manages this event's union
            if ($user->isUnionManager()) {
                $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
                if (in_array($this->record->union_id, $userUnionIds)) {
                    $actions[] = Action::make('qrCode')
                        ->label('QR Code Điểm Danh')
                        ->icon('heroicon-o-qr-code')
                        ->url(fn() => EventResource::getUrl('qr-code', ['record' => $this->record]))
                        ->openUrlInNewTab();
                }
            } else {
                // Admin can always see QR code
                $actions[] = Action::make('qrCode')
                    ->label('QR Code Điểm Danh')
                    ->icon('heroicon-o-qr-code')
                    ->url(fn() => EventResource::getUrl('qr-code', ['record' => $this->record]))
                    ->openUrlInNewTab();
            }
        }

        return $actions;
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }
}

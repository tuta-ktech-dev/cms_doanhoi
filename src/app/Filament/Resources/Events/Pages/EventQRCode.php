<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;

class EventQRCode extends Page
{
    protected static string $resource = EventResource::class;

    protected string $view = 'filament.resources.events.pages.event-qr-code';

    public $event;
    public $qrCodeUrl = null;
    public $expiresAt = null;
    public $error = null;

    public function mount($record): void
    {
        $this->event = \App\Models\Event::findOrFail($record);
        
        // Check permission
        $user = auth()->user();
        if ($user->isUnionManager()) {
            $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
            if (!in_array($this->event->union_id, $userUnionIds)) {
                abort(403, 'Không có quyền xem QR code của sự kiện này');
            }
        }

        $this->loadQRCode();
    }

    public function loadQRCode(): void
    {
        try {
            $controller = new \App\Http\Controllers\Api\QRCodeController();
            $request = new \Illuminate\Http\Request();
            $request->setUserResolver(fn () => auth()->user());
            
            $response = $controller->generateQR($request, $this->event->id);
            $data = json_decode($response->getContent(), true);

            if ($data['success'] ?? false) {
                $this->qrCodeUrl = $data['data']['qr_code_url'] ?? null;
                $this->expiresAt = $data['data']['expires_at'] ?? null;
                $this->error = null;
            } else {
                $this->error = $data['message'] ?? 'Không thể tạo QR code';
            }
        } catch (\Exception $e) {
            $this->error = 'Lỗi: ' . $e->getMessage();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Làm mới QR Code')
                ->icon('heroicon-o-arrow-path')
                ->action('loadQRCode'),
        ];
    }

    public function getTitle(): string
    {
        return 'QR Code Điểm Danh - ' . $this->event->title;
    }
}


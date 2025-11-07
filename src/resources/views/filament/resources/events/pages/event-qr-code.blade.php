<x-filament-panels::page>
    <style>
        .qr-code-image {
            min-width: 500px;
            width: 500px;
            height: 500px;
            object-fit: contain;
        }

        @media (max-width: 500px) {
            .qr-code-image {
                min-width: 100%;
                width: 100%;
                height: auto;
            }
        }

        .countdown-warning {
            color: #ef4444 !important;
        }
    </style>

    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; background: linear-gradient(to bottom right, #eff6ff, #ffffff, #faf5ff); padding: 24px;">
        <div style="width: 100%; max-width: 672px; display: flex; flex-direction: column; gap: 24px;">
            <!-- Header -->
            <div style="text-align: center; display: flex; flex-direction: column; gap: 8px;">
                <h1 style="font-size: 30px; font-weight: 700; color: #111827; margin-bottom: 4px;">{{ $this->event->title }}</h1>
                <p style="color: #4b5563; font-size: 18px;">Quét mã QR để điểm danh</p>
            </div>

            @if($this->error)
            <div style="background-color: #fef2f2; border-left: 4px solid #f87171; border-radius: 8px; padding: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <div style="display: flex; align-items: center;">
                    <svg style="width: 20px; height: 20px; color: #f87171; margin-right: 8px;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <p style="color: #b91c1c; font-weight: 500;">{{ $this->error }}</p>
                </div>
            </div>
            @endif

            @if($this->qrCodeUrl)
            <!-- QR Code Card -->
            <div style="background-color: #ffffff; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); padding: 32px; border: 1px solid #f3f4f6;">
                <div style="display: flex; flex-direction: column; align-items: center; gap: 24px;">
                    <!-- QR Code Image -->
                    <div style="background-color: #ffffff; padding: 16px; border-radius: 12px; box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06); border: 2px solid #f3f4f6;">
                        <img src="{{ $this->qrCodeUrl }}"
                            alt="QR Code"
                            class="qr-code-image"
                            style="margin: 0 auto; transition: all 0.3s ease; display: block;" />
                    </div>

                    <!-- Countdown Timer -->
                    @if($this->expiresAt)
                    <div style="width: 100%; max-width: 448px;">
                        <div style="background: linear-gradient(to right, #3b82f6, #9333ea); border-radius: 12px; padding: 16px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
                            <div style="text-align: center; color: #ffffff;">
                                <p style="font-size: 14px; font-weight: 500; margin-bottom: 8px; opacity: 0.9;">Mã QR sẽ hết hạn sau</p>
                                <div id="countdown" style="font-size: 36px; font-weight: 700; margin-bottom: 4px; color: #ffffff;">
                                    <span id="countdown-seconds">30</span>s
                                </div>
                                <p style="font-size: 12px; opacity: 0.75;">
                                    Hết hạn: {{ \Carbon\Carbon::parse($this->expiresAt)->format('H:i:s') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Info Card -->
            <div style="background: linear-gradient(to right, #eff6ff, #eef2ff); border: 1px solid #bfdbfe; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <div style="flex: 1;">
                        <h3 style="color: #1e3a8a; font-weight: 600; margin-bottom: 4px;">Hướng dẫn sử dụng</h3>
                        <p style="color: #1e40af; font-size: 14px; line-height: 1.75;">
                            Sinh viên mở ứng dụng, quét mã QR này để điểm danh.
                            <span style="font-weight: 500;">Mỗi mã QR chỉ sử dụng được một lần</span> và sẽ tự động làm mới sau 30 giây.
                        </p>
                    </div>
                </div>
            </div>
            @else
            <!-- Loading State -->
            <div style="background-color: #ffffff; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); padding: 48px; text-align: center; border: 1px solid #f3f4f6;">
                <div style="display: flex; flex-direction: column; align-items: center; gap: 16px;">
                    <div style="animation: spin 1s linear infinite; border-radius: 50%; height: 48px; width: 48px; border: 2px solid #3b82f6; border-top-color: transparent;"></div>
                    <p style="color: #4b5563; font-size: 18px; font-weight: 500;">Đang tạo mã QR...</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <style>
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>

    @script
    <script>
        let refreshTimer = null;
        let countdownTimer = null;

        function updateCountdown() {
            const expiresAtValue = $wire.get('expiresAt');
            if (!expiresAtValue) return;

            const expiresAt = new Date(expiresAtValue);
            const now = new Date();
            const timeUntilExpiry = Math.max(0, Math.floor((expiresAt.getTime() - now.getTime()) / 1000));

            const countdownElement = document.getElementById('countdown-seconds');
            const countdownContainer = document.getElementById('countdown');
            if (countdownElement && countdownContainer) {
                countdownElement.textContent = timeUntilExpiry;

                // Add warning class when time is low
                if (timeUntilExpiry <= 10) {
                    countdownContainer.classList.add('countdown-warning');
                } else {
                    countdownContainer.classList.remove('countdown-warning');
                }
            }

            if (timeUntilExpiry > 0) {
                countdownTimer = setTimeout(updateCountdown, 1000);
            }
        }

        function scheduleRefresh() {
            // Clear existing timer
            if (refreshTimer) {
                clearTimeout(refreshTimer);
            }

            // Get current expiresAt from Livewire component
            const expiresAtValue = $wire.get('expiresAt');

            if (expiresAtValue) {
                const expiresAt = new Date(expiresAtValue);
                const now = new Date();
                const timeUntilExpiry = expiresAt.getTime() - now.getTime();

                // Refresh 5 seconds before expiry (since QR only lasts 30s)
                const refreshTime = Math.max(timeUntilExpiry - 5000, 1000); // At least 1 second

                if (refreshTime > 0) {
                    refreshTimer = setTimeout(() => {
                        $wire.loadQRCode().then(() => {
                            // After refresh, schedule next refresh
                            scheduleRefresh();
                            updateCountdown(); // Restart countdown
                        });
                    }, refreshTime);
                } else {
                    // Already expired or about to expire, refresh immediately
                    $wire.loadQRCode().then(() => {
                        // After refresh, schedule next refresh
                        scheduleRefresh();
                        updateCountdown(); // Restart countdown
                    });
                }
            } else {
                // Fallback: refresh every 25 seconds if no expiry time
                refreshTimer = setTimeout(() => {
                    $wire.loadQRCode().then(() => {
                        scheduleRefresh();
                        updateCountdown();
                    });
                }, 25000);
            }
        }

        // Start countdown if expiresAt exists
        const hasExpiresAt = $wire.get('expiresAt');
        if (hasExpiresAt) {
            updateCountdown();
        }

        // Start scheduling refresh
        scheduleRefresh();

        // Also listen for Livewire updates to reschedule
        Livewire.hook('message.processed', (message, component) => {
            if (component.__instance?.id === $wire.__instance?.id) {
                // Component updated, reschedule refresh and countdown
                setTimeout(() => {
                    scheduleRefresh();
                    const newExpiresAt = $wire.get('expiresAt');
                    if (newExpiresAt) {
                        updateCountdown();
                    }
                }, 500); // Wait 500ms for data to update
            }
        });
    </script>
    @endscript
</x-filament-panels::page>
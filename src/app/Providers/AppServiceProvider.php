<?php

namespace App\Providers;

use App\Models\Event;
use App\Observers\EventObserver;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Đăng ký RolePermissionServiceProvider
        $this->app->register(RolePermissionServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Đăng ký Event Observer
        Event::observe(EventObserver::class);

        // Custom Login Page với ảnh background
        FilamentView::registerRenderHook(
            'panels::auth.login.form.after',
            fn(): \Illuminate\Contracts\View\View => view('filament.login_extra')
        );
    }
}

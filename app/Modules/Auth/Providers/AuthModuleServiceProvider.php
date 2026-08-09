<?php

namespace Modules\Auth\Providers;

use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Auth\Notifications\Channels\LogWhatsappGateway;
use Modules\Auth\Notifications\Channels\PenyediaLayananWhatsappGateway;
use Modules\Auth\Notifications\Channels\WhatsappChannel;
use Modules\Auth\Notifications\Contracts\WhatsappGatewayInterface;

class AuthModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Ganti binding ini ke provider WA final (mis. FonnteWhatsappGateway)
        // saat sudah ditentukan — tidak ada kode lain yang perlu diubah.
        $this->app->bind(WhatsappGatewayInterface::class, PenyediaLayananWhatsappGateway::class);
    }

    public function boot(): void
    {
        $this->registerGates();
        $this->registerWhatsappNotificationChannel();
        $this->registerRoutes();
    }

    /**
     * superadmin di-bypass total dari SEMUA pengecekan can()/@can — tidak
     * perlu di-assign permission apa pun secara eksplisit, termasuk
     * permission yang belum dibuat sekalipun. Role lain (guru, kepala
     * sekolah, bendahara, dst) tetap butuh permission eksplisit (mis.
     * "panel.access" untuk sekadar bisa login ke panel ini).
     */
    private function registerGates(): void
    {
        Gate::before(function ($user, string $ability) {
            return $user->hasRole('superadmin') ? true : null;
        });
    }

    private function registerWhatsappNotificationChannel(): void
    {
        $this->app->make(ChannelManager::class)->extend('whatsapp', function ($app) {
            return $app->make(WhatsappChannel::class);
        });
    }

    private function registerRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__.'/../web.php');

        Route::prefix('api')
            ->middleware('api')
            ->group(__DIR__.'/../api.php');
    }
}

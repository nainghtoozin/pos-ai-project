<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Blade::directive('formatNumber', function ($expression) {
            return "<?php echo \\App\\Helpers\\NumberFormatter::format($expression); ?>";
        });
    }
}

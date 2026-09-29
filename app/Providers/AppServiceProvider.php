<?php

namespace App\Providers;

use App\Models\Judge;
use App\Models\Program;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::directive('malayalamFont', function ($expression) {
            return "<?php echo preg_match('/[\\x{0D00}-\\x{0D7F}]/u', (string)({$expression})) ? 'font-anek' : 'font-sora'; ?>";
        });

        Blade::if('malayalam', function ($text) {
            return preg_match('/[\x{0D00}-\x{0D7F}]/u', (string) $text);
        });

        // Ensure newly migrated columns and credentials exist dynamically
        try {
            if (! $this->app->runningInConsole() || $this->app->runningUnitTests()) {
                Program::ensureSchema();
                Judge::ensureCredentials();
            }
        } catch (\Throwable) {
        }
    }
}

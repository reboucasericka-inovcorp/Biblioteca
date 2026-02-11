<?php

namespace App\Providers;

use Illuminate\Foundation\Vite;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('testing')) {
            $this->app->singleton(Vite::class, function () {
                return new class extends Vite
                {
                    public function __invoke($entrypoints, $buildDirectory = null)
                    {
                        return new HtmlString('');
                    }

                    public function __call($method, $parameters)
                    {
                        return '';
                    }

                    public function __toString(): string
                    {
                        return '';
                    }

                    public function useIntegrityKey($key)
                    {
                        return $this;
                    }

                    public function useBuildDirectory($path)
                    {
                        return $this;
                    }

                    public function useHotFile($path)
                    {
                        return $this;
                    }

                    public function withEntryPoints($entryPoints)
                    {
                        return $this;
                    }
                };
            });
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
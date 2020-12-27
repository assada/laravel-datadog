<?php

declare(strict_types=1);

namespace AirSlate\Datadog\ServiceProviders;

use AirSlate\Datadog\Components\ComponentInterface;
use AirSlate\Datadog\Services\CounterManager;
use AirSlate\Datadog\Services\TimerManager;
use Illuminate\Config\Repository;
use Illuminate\Support\ServiceProvider;

class ComponentsProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            $this->configPath(),
            'datadog'
        );
    }

    public function boot(): void
    {
        /** @var Repository $config */
        $this->app->singleton(TimerManager::class);
        $this->app->singleton(CounterManager::class);

        $components = $this->app->get('config')->get('datadog.components');

        /** @var string $componentItem */
        foreach ($components as $componentItem) {
            $component = $this->app->make($componentItem);
            if (! $component instanceof ComponentInterface) {
                throw new \RuntimeException('Component must have ComponentInterface');
            }

            $component->register();
        }
    }

    /**
     * Return config path.
     *
     * @return string
     */
    protected function configPath(): string
    {
        return __DIR__ . '/../../config/datadog.php';
    }
}

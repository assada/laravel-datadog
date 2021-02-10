<?php

declare(strict_types=1);

namespace AirSlate\Datadog\ServiceProviders;

use AirSlate\Datadog\Components\ComponentInterface;
use AirSlate\Datadog\Services\CounterManager;
use AirSlate\Datadog\Services\TimerManager;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class ComponentsProvider extends ServiceProvider
{
    /**
     * @throws BindingResolutionException
     */
    public function register(): void
    {
        $this->app->singleton(TimerManager::class);
        $this->app->singleton(CounterManager::class);

        /** @var string[] $components */
        $components = $this->app->get('config')->get('datadog.components.all');

        $this->registerComponents($components);

        if ($this->app->runningInConsole()) {
            $consoleComponents = $this->app->get('config')->get('datadog.components.console');
            $this->registerComponents($consoleComponents);
        } else {
            $httpComponents = $this->app->get('config')->get('datadog.components.http');
            $this->registerComponents($httpComponents);
        }
    }

    /**
     * @param string[] $components
     * @throws BindingResolutionException
     */
    private function registerComponents(array $components): void
    {
        foreach ($components as $componentClass) {
            $component = $this->app->make($componentClass);

            if (! $component instanceof ComponentInterface) {
                throw new RuntimeException('Component must have ComponentInterface');
            }

            $component->register();
        }
    }
}

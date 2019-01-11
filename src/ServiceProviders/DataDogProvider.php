<?php

namespace AirSlate\Datadog\ServiceProviders;

use AirSlate\Datadog\Services\Datadog;
use Illuminate\Container\Container;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;

class DataDogProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                \dirname($this->configPath()) => $this->app->make('path.config')], 'datadog');
        }
        $this->mergeConfigFrom($this->configPath(), 'datadog');
    }

    public function register(): void
    {
        $this->app->singleton(Datadog::class, function ($app) {
            $config = $app->get('config')->get('datadog');
            return new Datadog([
                'host' => $config['statsd_server'] ?? '172.17.0.1',
                'port' => $config['statsd_port'] ?? 8125,
            ]);
        });
        
        $this->registerRouteMatchedListener();
    }

    /**
     * Return config path.
     *
     * @return string
     */
    private function configPath(): string
    {
        return \dirname(__DIR__) . '/config/datadog.php';
    }

    private function registerRouteMatchedListener(): void
    {
        $this->app->make('router')->matched(function (RouteMatched $matched) {
            $operationName = sprintf(
                '%s/%s/%s',
                strtoupper($matched->request->getScheme()),
                $matched->request->method(),
                $matched->route->uri
            );

            $config = Container::getInstance()->get('config');

            /** @var Datadog $datadog */
            $datadog = Container::getInstance()->make(Datadog::class);
            $datadog->addTag('url', $operationName);
            if (isset($config['application'])) {
                $datadog->addTag('app', $config['application']);
            }
            return $operationName;
        });
    }
}

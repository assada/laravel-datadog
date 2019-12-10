<?php
declare(strict_types=1);

namespace AirSlate\Datadog\ServiceProviders;

use AirSlate\Datadog\Http\Middleware\DatadogMiddleware;
use AirSlate\Datadog\Services\Datadog;
use AirSlate\Datadog\Services\QueueJobMeter;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * Class DatadogProvider
 *
 * @package AirSlate\Datadog\ServiceProviders
 */
class DatadogProvider extends ServiceProvider
{
    /**
     * @throws BindingResolutionException
     */
    public function register(): void
    {
        $this->mergeConfigFrom($this->configPath(), 'datadog');

        $config = $this->app->get('config')->get('datadog');

        $datadog = new Datadog([
            'host' => $config['statsd_server'] ?? '172.17.0.1',
            'port' => $config['statsd_port'] ?? 8125,
        ]);

        $this->app->instance(Datadog::class, $datadog);
        $this->app->singleton(QueueJobMeter::class, function (): QueueJobMeter {
            return new QueueJobMeter();
        });
        $this->app->when(DatadogMiddleware::class)
                  ->needs('$namespace')
                  ->give($config['application_namespace'] ?? 'unknown');

        $datadog->addTag('app', strtolower(
            preg_replace('/\s+/', '-', $config['application_name'] ?? 'unknown')
        ));
        if ($config['statsd_env'] !== null) {
            $datadog->addTag('env', $config['statsd_env']);
        }

        $this->registerRouteMatchedListener($datadog);
    }

    /**
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole() && function_exists('config_path')) {
            $this->publishes([$this->configPath() => config_path('datadog.php')], 'datadog');
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

    /**
     * @param Datadog $datadog
     *
     * @throws BindingResolutionException
     */
    protected function registerRouteMatchedListener(Datadog $datadog): void
    {
        $this->app->make('router')->matched(function (RouteMatched $matched) use ($datadog) {
            $datadog->addTag('url', $matched->route->uri);
        });
    }
}

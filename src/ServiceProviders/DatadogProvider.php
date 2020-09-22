<?php

declare(strict_types=1);

namespace AirSlate\Datadog\ServiceProviders;

use AirSlate\Datadog\Http\Middleware\DatadogMiddleware;
use AirSlate\Datadog\Services\DatabaseQueryCounter;
use AirSlate\Datadog\Services\Datadog;
use AirSlate\Datadog\Services\QueueJobMeter;
use AirSlate\Datadog\Tag\Tag;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;

/**
 * Class DatadogProvider
 *
 * @package AirSlate\Datadog\ServiceProviders
 */
class DatadogProvider extends ServiceProvider
{
    public const DATADOG_TAG = 'datadog.tag';

    /**
     * @throws BindingResolutionException
     */
    public function register(): void
    {
        $this->mergeConfigFrom($this->configPath(), 'datadog');

        /** @var Repository $config */
        $config = $this->app->get('config');

        $this->app->singleton(QueueJobMeter::class, static function (): QueueJobMeter {
            return new QueueJobMeter();
        });

        $this->app->singleton(DatabaseQueryCounter::class, static function (): DatabaseQueryCounter {
            return new DatabaseQueryCounter();
        });

        $this->app->when(DatadogMiddleware::class)
                  ->needs('$namespace')
                  ->give($config->get('datadog.application_namespace', 'unknown'));

        $this->app->singleton(Datadog::class, function () use ($config) {
            $datadog = new Datadog(
                [
                    'host' => $config->get('datadog.statsd_server', '172.17.0.1'),
                    'port' => $config->get('datadog.statsd_port', 8125),
                    'global_tags' => $config->get('datadog.global_tags', []),
                ]
            );

            $datadog->addTag('app', strtolower(
                preg_replace(
                    '/\s+/',
                    '-',
                    $config->get('datadog.application_name', 'unknown')
                )
            ));

            if ($config->get('datadog.statsd_env') !== null) {
                $datadog->addTag('env', $config->get('datadog.statsd_env'));
            }

            foreach ($this->app->tagged(self::DATADOG_TAG) as $tag) {
                if (!($tag instanceof Tag)) {
                    continue;
                }

                $datadog->addTag($tag->getKey(), $tag->getValue());
            }

            return $datadog;
        });
    }

    /**
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $this->registerRouteMatchedListener($this->app->make(Datadog::class));

        if (function_exists('config_path') && $this->app->runningInConsole()) {
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
        $this->app->make('router')->matched(static function (RouteMatched $matched) use ($datadog) {
            $datadog->addTag('url', $matched->route->uri);
        });
    }
}

<?php

declare(strict_types=1);

namespace AirSlate\Datadog\Components;

use AirSlate\Datadog\Services\ClassShortener;
use AirSlate\Datadog\Services\Datadog;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;

abstract class ComponentAbstract implements ComponentInterface
{
    /** @var Application */
    protected $app;

    /** @var ClassShortener */
    protected $classShortener;

    /** @var Datadog */
    protected $statsd;

    /** @var string */
    private $namespace;

    /** @var Dispatcher */
    protected $dispatcher;

    public function __construct(
        Application $application,
        ClassShortener $classShortener,
        Datadog $datadog,
        Dispatcher $dispatcher
    ) {
        $this->app = $application;
        $this->classShortener = $classShortener;
        $this->statsd = $datadog;
        $this->namespace = $this->app->get('config')
            ->get('datadog.application_namespace', 'unknown');
        $this->dispatcher = $dispatcher;
    }

    abstract public function register(): void;

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    protected function getStat(string $stat): string
    {
        return $this->getNamespace() . '.' . $stat;
    }

    protected function getShortName(string $className): string
    {
        return $this->classShortener->shorten($className);
    }

    protected function listen(string $event, \Closure $callback)
    {
        $this->dispatcher->listen($event, $callback);
    }
}

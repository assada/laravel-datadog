<?php

declare(strict_types=1);

namespace AirSlate\Datadog\Services;

/**
 * Class ClassShortener
 *
 * @package AirSlate\Datadog\Services
 */
class ClassShortener
{
    /**
     * @param string $className
     *
     * @return string
     *
     * @throws \ReflectionException
     */
    public function shorten(string $className)
    {
        if (class_exists($className)) {
            return (new \ReflectionClass($className))->getShortName();
        }
        return $className;
    }
}

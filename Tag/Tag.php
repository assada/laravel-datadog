<?php
declare(strict_types=1);

namespace AirSlate\Datadog\Tag;


/**
 * Class Tag
 *
 * @package AirSlate\Datadog\Tag
 */
class Tag
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var mixed
     */
    private $value;

    /**
     * Tag constructor.
     *
     * @param string $key
     * @param mixed $value
     */
    public function __construct(string $key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}

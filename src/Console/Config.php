<?php

namespace Parsnick\Steak\Console;

class Config
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * Import an array of configuration values.
     *
     * @param array $config
     * @return $this
     */
    public function import($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get a configuration value.
     *
     * @param string $key
     * @param null $default
     * @return null
     */
    function get($key, $default = null)
    {
        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }
}
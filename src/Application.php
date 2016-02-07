<?php

namespace Parsnick\Steak;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Symfony\Component\Console\Application as SymfonyApplication;

class Application
{
    /**
     * @var SymfonyApplication
     */
    protected $symfony;

    /**
     * @var Container
     */
    protected $container;

    /**
     * Create a new steak application.
     *
     * @param Container $container
     * @param string $version
     */
    public function __construct(Container $container, $version)
    {
        $this->symfony = new SymfonyApplication('Steak', $version);
        $this->container = $container;

        $this->container->singleton(Config::class, Repository::class);
    }

    /**
     * Register an array of commands.
     *
     * @param array $commands
     * @return $this
     */
    public function commands(array $commands)
    {
        array_walk($commands, function ($commandClass) {

            $command = $this->container->make($commandClass);

            $command->setContainer($this->container);

            $this->symfony->add($command);

        });

        return $this;
    }

    /**
     * Run the application.
     *
     * @return int
     * @throws \Exception
     */
    public function run()
    {
        return $this->symfony->run();
    }
}
<?php

namespace Parsnick\Steak;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Parsnick\Steak\Publishers\CompileBlade;
use Parsnick\Steak\Publishers\SkipExcluded;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Yaml\Yaml;

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
     * @param string $version
     * @param Container $container
     */
    public function __construct($version, Container $container = null)
    {
        $this->symfony = new SymfonyApplication('Steak', $version);
        $this->container = $container ?: new Container();

        $this->registerDefaultBindings($this->container);
    }

    /**
     * @param Container $app
     */
    protected function registerDefaultBindings(Container $app)
    {
        $app->singleton('files', Filesystem::class);

        $app->singleton('config', function ($app) {

            $config = new Repository([
                'pipeline' => [
                    'skip:_*',
                    'blade',
                ],
                'source' => 'source',
                'output' => 'build',
            ]);

            $option = (new ArgvInput())->getParameterOption(['--config', '-c']);

            if ( ! $option && $app['files']->exists('steak.yml')) {
                $option = 'steak.yml';
            }

            foreach (explode(',', $option) as $file) {
                $config->set(Yaml::parse($app['files']->get($file)));
            }

            return $config;
        });

        $app->bind(Builder::class, function ($app) {
            return new Builder($app, $app['config']->get('pipeline'));
        });

        $app->bind('skip', SkipExcluded::class);
        $app->bind('blade', CompileBlade::class);
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
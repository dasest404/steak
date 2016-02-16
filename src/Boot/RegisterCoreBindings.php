<?php

namespace Parsnick\Steak\Boot;

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Parsnick\Steak\Build\Publishers\Compile;
use Parsnick\Steak\Build\Publishers\Skip;
use Parsnick\Steak\Build\Builder;

class RegisterCoreBindings implements Bootable
{
    /**
     * Set up required container bindings.
     *
     * @param Container $app
     */
    public function boot(Container $app)
    {
        $app->singleton('files', Filesystem::class);

        $app->singleton('events', function ($app) {
            return new Dispatcher($app);
        });

        $app->bind(Builder::class, function ($app) {
            return new Builder($app, $app['config']['build.pipeline']);
        });

        $app->bind(Factory::class, function ($app)
        {
            $engineResolver = $app->make(EngineResolver::class);

            $viewFinder = $app->make(FileViewFinder::class, [
                $app['files'],
                [
                    $app['config']['source.directory']
                ]
            ]);

            return new Factory($engineResolver, $viewFinder, $app['events']);
        });

        $this->bindAliasesForPipeline($app);
    }

    /**
     * Add shorthand for pipeline handlers.
     *
     * Allows the simpler "<name>:<args>" style when defining the build pipeline,
     * as opposed to "<FQCN>:<args>"
     *
     * @param Container $app
     */
    protected function bindAliasesForPipeline(Container $app)
    {
        $app->bind('skip', Skip::class);
        $app->bind('compile', Compile::class);
    }
}

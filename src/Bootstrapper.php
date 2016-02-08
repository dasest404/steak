<?php

namespace Parsnick\Steak;

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Parsnick\Steak\Publishers\CompileBlade;
use Parsnick\Steak\Publishers\SkipExcluded;

class Bootstrapper
{
    public function bootstrap(Container $app)
    {
        $app->singleton('files', Filesystem::class);

        $app->bind(Builder::class, function ($app) {
            return new Builder($app, $app['config']['pipeline']);
        });

        $app->bind('skip', SkipExcluded::class);
        $app->bind('blade', CompileBlade::class);

        $app->bind(Factory::class, function ($app) {
            return new Factory(
                $app->make(EngineResolver::class),
                new FileViewFinder($app['files'], [$app['config']['source']]),
                new Dispatcher($app)
            );
        });

        $app->bind(EngineResolver::class, function ($app) {

            $resolver = new EngineResolver();

            $resolver->register('php', function () {
                return new PhpEngine();
            });

            $resolver->register('blade', function () use ($app) {

                $cacheDir = $app['config']['cache'];

                if ( ! $app['files']->exists($cacheDir)) {
                    $app['files']->makeDirectory($cacheDir, 0755, true);
                }

                return new CompilerEngine(
                    new BladeCompiler($app['files'], $cacheDir)
                );
            });

            return $resolver;
        });
    }
}
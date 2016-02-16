<?php

namespace Parsnick\Steak\Boot;

use Illuminate\Container\Container;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use org\bovigo\vfs\vfsStream;
use Parsnick\Steak\View\Compilers\Markdown;

class ConfigureViewEngines implements Bootable
{
    /**
     * Set up the various view engines.
     *
     * @param Container $app
     */
    public function boot(Container $app)
    {
        $app->afterResolving(function (Factory $factory, $app) {

            $factory->addExtension('php', 'php', function () {
                return new PhpEngine();
            });

            $factory->addExtension('blade.php', 'blade', function () use ($app) {
                return new CompilerEngine($app->make(BladeCompiler::class));
            });

            $factory->addExtension('md', 'markdown', function () use ($app) {
                return new CompilerEngine($app->make(Markdown::class));
            });

        });

        $app->when(BladeCompiler::class)
            ->needs('$cachePath')
            ->give(vfsStream::setup('root/.blade')->url());
    }
}

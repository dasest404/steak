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
    /**
     * Set up the default bindings.
     *
     * @param Container $app
     */
    public function bootstrap(Container $app)
    {
        $app->singleton('files', Filesystem::class);

        $app->bind(Builder::class, function ($app) {
            return new Builder($app, $app['config']['build.pipeline']);
        });

        $app->bind('skip', SkipExcluded::class);
        $app->bind('blade', CompileBlade::class);

        $app->bind(Factory::class, function ($app) {
            return new Factory(
                $app->make(EngineResolver::class),
                new FileViewFinder($app['files'], [$app['config']['source.directory']]),
                new Dispatcher($app)
            );
        });

        $app->bind('compilers.blade', function ($app) {

            $cacheDir = $app['config']['build.cache'];

            if ( ! $app['files']->exists($cacheDir)) {
                $app['files']->makeDirectory($cacheDir, 0755, true);
            }

            return new BladeCompiler($app['files'], $cacheDir);
        });

        $app->bind(EngineResolver::class, function ($app) {

            $resolver = new EngineResolver();

            $resolver->register('php', function () {
                return new PhpEngine();
            });

            $resolver->register('blade', function () use ($app) {
                return new CompilerEngine($app['compilers.blade']);
            });

            return $resolver;
        });

        $app->afterResolving('compilers.blade', function (BladeCompiler $compiler) {

            $compiler->directive('highlight', function ($expression) {
                if (is_null($expression)) {
                    $expression = "('php')";
                }
                return "<?php \$__env->startSection{$expression}; ?>";
            });

            $compiler->directive('endhighlight', function () {
                return "<?php \$last = \$__env->stopSection(); echo \$__highlighter->highlight(\$__env->yieldContent(\$last), \$last); ?>";
            });

        });

        $app->afterResolving(function (Factory $factory, $app) {
            $factory->share('__highlighter', $app->make(Highlighter::class));
        });
    }
}

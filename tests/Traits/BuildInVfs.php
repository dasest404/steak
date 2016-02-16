<?php

namespace Parsnick\Steak\Tests\Traits;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Parsnick\Steak\Build\Builder;
use Parsnick\Steak\Build\Publishers\Compile;
use Parsnick\Steak\Build\Publishers\Skip;

trait BuildInVfs
{
    /**
     * @var vfsStreamDirectory
     */
    protected $fs;

    /** @before */
    function create_virtual_filesystem()
    {
        $this->fs = vfsStream::setup('test', null, [
            'source' => [],
            'build' => [],
            '.cache' => [],
        ]);
    }

    /**
     * Create the given file structure in the virtual source dir.
     *
     * @param array $structure
     * @return vfsStreamDirectory
     */
    protected function createSource($structure)
    {
        return vfsStream::create([
            'source' => $this->parseStructure($structure),
        ]);
    }

    /**
     * @param array $array
     * @param string $defaultValue
     * @return mixed
     */
    protected function parseStructure(array $array, $defaultValue = '')
    {
        foreach ($array as $key => $value) {
            if (is_numeric($key) && is_string($value)) {
                $array[$value] = $defaultValue;
                unset($array[$key]);
            }
            if (is_array($value)) {
                $array[$key] = $this->parseStructure($value);
            }
        }

        return $array;
    }

    /**
     * Run the build command.
     *
     * @return bool
     */
    protected function build()
    {
        $container = new Container();

        $container->singleton('files', Filesystem::class);

        $container->bind(Factory::class, function ($app) {
            return new Factory(
                $app->make(EngineResolver::class),
                new FileViewFinder($app['files'], ['test/source']),
                new Dispatcher($app)
            );
        });

        $container->afterResolving(function (Factory $factory, $app) {

            $factory->addExtension('php', 'php', function () {
                return new PhpEngine();
            });

            $factory->addExtension('blade.php', 'blade', function () use ($app) {
                return new CompilerEngine(new BladeCompiler($app['files'], vfsStream::url('test/.cache')));
            });

        });

        $builder = new Builder($container, [Skip::class.':_*', Compile::class]);

        return $builder->build(
            vfsStream::url('test/source'),
            vfsStream::url('test/build')
        );
    }

    /**
     * Assert the file structure exists in the test filesystem.
     *
     * @param array $structure
     * @param string $prefix
     */
    protected function seeGenerated(array $structure, $prefix = '.')
    {
        foreach ($this->parseStructure($structure) as $name => $value) {
            if (is_array($value)) {
                $this->seeGenerated($value, "$prefix/$name");
            } else {
                $url = vfsStream::url("test/build/$prefix/$name");
                assertFileExists($url);

                if ($value) {
                    assertStringMatchesFormat($value, file_get_contents($url));
                }
            }
        }
    }

    /**
     * Assert the file structure does not exist in the test filesystem.
     *
     * @param array $structure
     * @param string $prefix
     */
    protected function dontSeeGenerated(array $structure, $prefix = '')
    {
        foreach ($this->parseStructure($structure) as $key => $item) {
            if (is_array($item)) {
                $this->dontSeeGenerated($item, "$prefix/$key");
            } else {
                $url = vfsStream::url("test/build/$prefix/$key");
                assertFileNotExists($url);

                if ($item) {
                    assertStringMatchesFormat($item, file_get_contents($url));
                }
            }
        }
    }
}

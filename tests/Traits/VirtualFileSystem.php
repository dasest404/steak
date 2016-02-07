<?php

namespace Parsnick\Steak\Tests\Traits;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Parsnick\Steak\Builder;
use Parsnick\Steak\Config;

trait VirtualFileSystem
{
    /**
     * @var vfsStreamDirectory
     */
    protected $fs;

    /** @before */
    function create_virtual_filesystem()
    {
        $this->fs = vfsStream::setup('root');
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

        $builder = new Builder($container);

        return $builder->build(
            vfsStream::url('root/source'),
            vfsStream::url('root/build')
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
                $url = vfsStream::url("root/build/$prefix/$name");
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
                $url = vfsStream::url("root/build/$prefix/$key");
                assertFileNotExists($url);

                if ($item) {
                    assertStringMatchesFormat($item, file_get_contents($url));
                }
            }
        }
    }
}
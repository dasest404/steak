<?php

namespace Parsnick\Steak\Tests;

use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Parsnick\Steak\Builder;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @before */
    function create_virtual_filesystem()
    {
        vfsStream::setup('root/source');
    }

    /** @test */
    function it_compiles_blades_files_to_html()
    {
        $this->createSource([
            'index.blade.php' => '',
        ]);

        $this->build();

        $this->seeGenerated([
            'index.html',
        ]);
    }

    /** @test */
    function it_reproduces_the_source_directory_structure()
    {
        $this->createSource([
            'index.blade.php',
            'api.blade.php',
            'tutorials' => [
                '01-intro.blade.php',
                'advanced' => [
                    'index.blade.php',
                ],
            ],
        ]);

        $this->build();

        $this->seeGenerated([
            'index.html',
            'api.html',
            'tutorials' => [
                '01-intro.html',
                'advanced' => [
                    'index.html',
                ],
            ],
        ]);
    }

    /** @test */
    function it_skips_files_and_folders_with_underscore_prefix()
    {
        $this->createSource([
            'index.blade.php',
            '_promobox.blade.php',
            '_includes' => [
                'sidebar.blade.php',
                'footer.blade.php',
            ],
            'tutorials' => [
                '_splash.blade.php',
            ],
        ]);

        $this->build();

        $this->seeGenerated([
            'index.html',
        ]);
        $this->dontSeeGenerated([
            '_promobox.html',
            'promobox.html',
            '_includes' => [
                'sidebar.blade.php',
                'footer.blade.php',
            ],
            'tutorials' => [
                '_splash.html',
            ]
        ]);
    }

    /** test */
    function it_publishes_php_files_without_compiling_from_blade()
    {
        $this->createSource([
            'index.php' => '<?php echo "hello";',
        ]);

        $this->build();

        $this->seeGenerated([
            'index.php' => 'hello',
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

        $builder = $container->make(Builder::class);

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
                $this->assertFileExists($url);

                if ($value) {
                    $this->assertStringMatchesFormat($value, file_get_contents($url));
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
                $this->assertFileNotExists($url);

                if ($item) {
                    $this->assertStringMatchesFormat($item, file_get_contents($url));
                }
            }
        }
    }

}
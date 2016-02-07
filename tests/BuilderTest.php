<?php

namespace Parsnick\Steak\Tests;

use Parsnick\Steak\Tests\Traits\VirtualFileSystem;

class BuilderTest extends TestCase
{
    use VirtualFileSystem;

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
                'sidebar.html',
                'footer.html',
            ],
            'tutorials' => [
                '_splash.html',
            ]
        ]);
    }

    /** @todo */
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
}
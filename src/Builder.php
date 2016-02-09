<?php

namespace Parsnick\Steak;

use Illuminate\Container\Container;
use Illuminate\Pipeline\Pipeline;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Builder
{
    /**
     * @var Container
     */
    protected $app;

    /**
     * @var array
     */
    protected $publishers = [];

    /**
     * Create a new Builder instance.
     *
     * @param Container $app
     * @param array $publishers
     */
    public function __construct(Container $app, array $publishers = [])
    {
        $this->app = $app;
        $this->publishers = $publishers;
    }

    /**
     * Build the site.
     *
     * @param string|array $sources
     * @param string $outputDir
     * @return bool
     */
    public function build($sources, $outputDir)
    {
        if (is_string($sources)) {
            $sources = Finder::create()->depth('< 1')->in($sources);
        }

        foreach ($sources as $file) {
            $this->publish($this->makeSource($file, $outputDir));
        };
    }

    /**
     * Publish a file / directory.
     *
     * @param Source $source
     * @return bool|mixed
     */
    protected function publish(Source $source)
    {
        return (new Pipeline($this->app))
            ->send($source)
            ->via('publish')
            ->through($this->publishers)
            ->then(function (Source $source) {
                if ($source->isDir()) {
                    $this->build($source->getPathname(), $source->getOutputPathname());
                }
            });
    }

    /**
     * @param $file
     * @param $outputDir
     * @return Source
     */
    protected function makeSource($file, $outputDir)
    {
        if (is_string($file)) {
            $sourceRoot = realpath($this->app['config']['source']);

            $relative = preg_replace('#^'.preg_quote($sourceRoot).'/#', '', realpath($file));

            $file = new SplFileInfo($file, dirname($relative), $relative);
        }

        return new Source(
            $file->getPathname(),
            $file->getRelativePath(),
            $file->getRelativePathname(),
            $outputDir.DIRECTORY_SEPARATOR.$file->getRelativePathname()
        );
    }
}

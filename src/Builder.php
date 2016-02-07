<?php

namespace Parsnick\Steak;

use Illuminate\Container\Container;
use Illuminate\Pipeline\Pipeline;
use Symfony\Component\Finder\Finder;

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
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
        $this->publishers = [
            Publishers\SkipUnderscored::class,
            Publishers\CompileBlade::class,
        ];
    }

    /**
     * Build the site.
     *
     * @param string $sourceDir
     * @param string $outputDir
     * @return bool
     */
    public function build($sourceDir, $outputDir)
    {
        foreach (Finder::create()->in($sourceDir) as $file) {
            $this->publish(Source::extend($file, $outputDir));
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
}

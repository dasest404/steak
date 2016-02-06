<?php

namespace Parsnick\Steak;

use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Pipeline\Pipeline;
use Parsnick\Steak\Publishers\SkipUnderscored;
use Symfony\Component\Finder\SplFileInfo;

class Builder
{
    /**
     * @var Container
     */
    protected $app;

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var array
     */
    protected $publishers = [];

    /**
     * Create a new Builder instance.
     *
     * @param Container $app
     * @param Filesystem $files
     */
    public function __construct(Container $app, Filesystem $files)
    {
        $this->app = $app;
        $this->files = $files;
        $this->publishers = [
            SkipUnderscored::class,
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
        $sources = $this->files->allFiles($sourceDir);

        return array_walk($sources, function ($file) use ($outputDir) {
            $this->publish(new File($file, $outputDir));
        });
    }

    /**
     * Publish a file.
     *
     * @param File $file
     * @return bool
     */
    protected function publish(File $file)
    {
        return (new Pipeline($this->app))
            ->send($file)
            ->via('publish')
            ->through($this->publishers)
            ->then(function (File $file) {
                return $this->write($file->output->getPathname(), $file->contents);
            });
    }

    /**
     * Write file contents, creating any necessary subdirectories.
     *
     * @param string $destination
     * @param string $content
     * @return bool
     */
    protected function write($destination, $content)
    {
        $directory = dirname($destination);

        if ( ! $this->files->exists($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        return (bool) $this->files->put($destination, $content);
    }
}

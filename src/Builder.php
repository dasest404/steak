<?php

namespace Parsnick\Steak;

use FilesystemIterator;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Pipeline\Pipeline;
use SplFileInfo;

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
     * @param string $sourceDir
     * @param string $outputDir
     * @return bool
     */
    public function build($sourceDir, $outputDir)
    {
        $sourceList = $this->findSources($sourceDir, $outputDir);

        return array_walk($sourceList, [$this, 'publish']);
    }

    /**
     * Create array of Sources from the given input directory.
     *
     * @param string $searchIn
     * @param string $outputTo
     * @return array
     */
    protected function findSources($searchIn, $outputTo)
    {
        $files = iterator_to_array(new FilesystemIterator($searchIn));

        return array_map(function (SplFileInfo $file) use ($outputTo) {

            return $this->makeSource($file, $outputTo);

        }, $files);
    }

    /**
     * Create a Source from the given file and output dir.
     *
     * @param SplFileInfo $file
     * @param string $outputDir
     * @return Source
     */
    public function makeSource(SplFileInfo $file, $outputDir)
    {
        return new Source($file->getPathname(), $outputDir . DIRECTORY_SEPARATOR . $file->getFilename());
    }

    /**
     * Publish a file / directory.
     *
     * @param Source $source
     * @return bool|mixed
     */
    public function publish(Source $source)
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
     * Remove the contents of a directory, but not the directory itself.
     *
     * @param string $dir
     * @param bool $preserveGit
     * @return bool
     */
    public function clean($dir, $preserveGit = false)
    {
        $filesystem = new Filesystem();

        if ( ! $filesystem->exists($dir)) {
            return $filesystem->makeDirectory($dir, 0755, true);
        }

        foreach (new FilesystemIterator($dir) as $file) {
            if ($preserveGit && $file->getFilename() == '.git') {
                continue;
            }
            if ($file->isDir() && ! $file->isLink()) {
                $filesystem->deleteDirectory($file);
            } else {
                $filesystem->delete($file);
            }
        }

        return true;
    }
}

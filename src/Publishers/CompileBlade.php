<?php

namespace Parsnick\Steak\Publishers;

use Closure;
use Illuminate\Filesystem\Filesystem;
use Parsnick\Steak\Source;

class CompileBlade implements Publisher
{
    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new CompileBlade publisher.
     *
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Publish a source file and/or pass to $next.
     *
     * @param Source $source
     * @param Closure $next
     * @return mixed
     */
    public function publish(Source $source, Closure $next)
    {
        if ($this->isBlade($source)) {
            $this->write(
                $source->getOutputPathname(['.blade.php' => '.html']),
                $source->getContents()
            );
        }

        $next($source);
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

    /**
     * @param Source $source
     * @return bool
     */
    protected function isBlade(Source $source)
    {
        return ends_with($source->getFilename(), '.blade.php');
    }
}
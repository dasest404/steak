<?php

namespace Parsnick\Steak\Publishers;

use Closure;
use Illuminate\Filesystem\Filesystem;
use Parsnick\Steak\Source;

abstract class Writer
{
    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new Writer publisher.
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
    abstract public function publish(Source $source, Closure $next);


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
<?php

namespace Parsnick\Steak\Build\Publishers;

use Closure;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Engines\EngineInterface;
use Illuminate\View\Factory;
use Illuminate\View\View;
use Parsnick\Steak\Source;

class Compile
{
    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var EngineInterface
     */
    protected $factory;

    /**
     * Create a new Compile publisher.
     *
     * @param Filesystem $files
     * @param Factory $factory
     */
    public function __construct(Filesystem $files, Factory $factory)
    {
        $this->files = $files;
        $this->factory = $factory;
    }

    /**
     * Publish a source file and/or pass to $next.
     *
     * @param Source $source
     * @param Closure $next
     * @param array $extensions
     * @return mixed
     */
    public function handle(Source $source, Closure $next, ...$extensions)
    {
        if ($this->isPublishable($source, $extensions)) {

            $view = new View(
                $this->factory,
                $this->factory->getEngineFromPath($source->getPathname()),
                $this->files->get($source->getPathname()),
                $source->getPathname()
            );

            $source->changeExtension(['.blade.php' => '.html'])
                   ->changeExtension(['.php' => '.html']);

            $this->write(
                $source->getOutputPathname(),
                $view->render()
            );

        }

        $next($source);
    }

    /**
     * Check if this Source should be published by PHP (or left for gulp)
     *
     * @param Source $source
     * @param array $extensions
     * @return bool
     */
    protected function isPublishable(Source $source, array $extensions = ['php'])
    {
        return in_array($source->getExtension(), $extensions);
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

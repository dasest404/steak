<?php

namespace Parsnick\Steak\Publishers;

use Closure;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Engines\EngineInterface;
use Illuminate\View\Factory;
use Illuminate\View\View;
use Parsnick\Steak\Source;

class CompileBlade extends Writer
{
    /**
     * @var EngineInterface
     */
    protected $factory;

    /**
     * Create a new CompileBlade publisher.
     *
     * @param Filesystem $files
     * @param Factory $factory
     */
    public function __construct(Filesystem $files, Factory $factory)
    {
        $this->factory = $factory;

        parent::__construct($files);
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
        if ($this->isRenderable($source)) {

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
     * @param Source $source
     * @return bool
     */
    protected function isRenderable(Source $source)
    {
        return $source->getExtension() === 'php';
    }
}

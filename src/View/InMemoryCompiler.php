<?php

namespace Parsnick\Steak\View;

use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\Compiler;
use org\bovigo\vfs\vfsStream;

abstract class InMemoryCompiler extends Compiler
{
    /**
     * Create a new compiler instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        $virtualCacheDir = uniqid('.cache-');

        vfsStream::setup('root', null, [$virtualCacheDir => []]);

        parent::__construct($files, vfsStream::url('root/' . $virtualCacheDir));
    }

    /**
     * Compile the view at the given path.
     *
     * @param  string  $path
     * @return void
     */
    public function compile($path)
    {
        $contents = $this->compileString($this->files->get($path));

        $this->files->put($this->getCompiledPath($path), $contents);
    }

    /**
     * @param string $string
     * @return string
     */
    abstract public function compileString($string);
}

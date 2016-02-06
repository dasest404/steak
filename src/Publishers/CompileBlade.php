<?php

namespace Parsnick\Steak\Publishers;

use Closure;
use Parsnick\Steak\File;

class CompileBlade implements Publisher
{
    /**
     * Publish a source file and/or pass to $next.
     *
     * @param File $file
     * @param Closure $next
     * @return mixed
     */
    public function publish(File $file, Closure $next)
    {
        $file->changeExtension('html', 'blade.php');

        $next($file);
    }
}
<?php

namespace Parsnick\Steak\Publishers;

use Closure;
use Parsnick\Steak\File;

class SkipUnderscored implements  Publisher
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
        $sourcePath = $file->source->getRelativePathname();

        if ( ! $this->startsWithUnderscore($sourcePath)) {
            $next($file);
        }
    }

    /**
     * Test if a file should be ignored.
     *
     * @param string $path
     * @return bool
     */
    protected function startsWithUnderscore($path)
    {
        return starts_with($path, '_') || str_contains($path, DIRECTORY_SEPARATOR.'_');
    }

}
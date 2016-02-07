<?php

namespace Parsnick\Steak\Publishers;

use Closure;
use Parsnick\Steak\Source;

class SkipExcluded
{
    /**
     * Publish a source file and/or pass to $next.
     *
     * @param Source $source
     * @param Closure $next
     * @param array $excluded
     * @return mixed
     */
    public function publish(Source $source, Closure $next, ...$excluded)
    {
        foreach ($excluded as $pattern) {

            $value = str_contains($pattern, DIRECTORY_SEPARATOR)
                   ? $source->getPathname()
                   : $source->getFilename();

            if (str_is($pattern, $value)) {
                return;
            }
        }

        $next($source);
    }
}
<?php

namespace Parsnick\Steak\Publishers;

use Closure;
use Parsnick\Steak\Source;

class SkipUnderscored
{
    /**
     * Publish a source file and/or pass to $next.
     *
     * @param Source $source
     * @param Closure $next
     */
    public function publish(Source $source, Closure $next)
    {
        if ( ! starts_with($source->getFilename(), '_')) {
            $next($source);
        }
    }
}
<?php

namespace Parsnick\Steak\Publishers;

use Closure;
use Parsnick\Steak\Source;

class SkipUnderscored implements  Publisher
{
    /**
     * Publish a source file and/or pass to $next.
     *
     * @param Source $source
     * @param Closure $next
     * @return mixed
     */
    public function publish(Source $source, Closure $next)
    {
        if ( ! starts_with($source->getFilename(), '_')) {
            $next($source);
        }
    }
}
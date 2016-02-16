<?php

namespace Parsnick\Steak\Boot;

use Illuminate\Container\Container;

interface Bootable
{
    /**
     * Boot.
     *
     * @param Container $app
     * @return void
     */
    public function boot(Container $app);
}

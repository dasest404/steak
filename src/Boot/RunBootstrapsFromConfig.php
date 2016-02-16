<?php

namespace Parsnick\Steak\Boot;

use Illuminate\Container\Container;

class RunBootstrapsFromConfig implements Bootable
{
    /**
     * Call any other bootstraps defined by config.
     *
     * @param Container $app
     */
    public function boot(Container $app)
    {
        $toBoot = (array) $app['config']->get('bootstrap', []);

        foreach ($toBoot as $bootstrapClass) {
            $app->make($bootstrapClass)->boot($app);
        }
    }
}

<?php

namespace Memcache\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Memcache\Silex\MemcacheWrapper;

/**
 *   MemecacheServiceProvider
 *   @author Jérôme Mahuet <gcc@statigr.am>
 */
class MemcacheServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Application $app)
    {
        $app['memcache'] = $app->share(function () use ($app) {
                return new MemcacheWrapper($app['memcache.servers']);
            });
    }

    /**
     * {@inheritDoc}
     */
    // @codeCoverageIgnoreStart
    public function boot(Application $app)
    {
    }
    // @codeCoverageIgnoreEnd
}

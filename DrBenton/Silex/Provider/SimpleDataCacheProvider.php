<?php

namespace DrBenton\Silex\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Inspiration fo this Provider comes from : http://www.syndicatetheory.com/labs/using-zend_db-with-silex
 */
class SimpleDataCacheProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {

        /**
         * @return \DrBenton\Component\SimpleCache\Adapter
         * @throws \InvalidArgumentException
         */
        $app['scache'] = $app->share(function() use ($app) {

            if (! isset($app['scache.type'])) {
                $app['scache.type'] = 'file';
            }

            if (! in_array($app['scache.type'], array('file'/*, 'db'*/))) {
                throw \InvalidArgumentException('Cache type "'.$app['scache.type'].'" unknown !');
            }

            switch ($app['scache.type']) {

                case 'file':
                    if (! isset($app['scache.data_folder_path'])) {
                        throw \InvalidArgumentException('For "file" cache you have to provide a "$app[\'scache.data_folder_path\']" parameter!');
                    }
                    $cacheManager = new \DrBenton\Component\SimpleCache\Adapter\File($app['scache.data_folder_path']);
                    break;

                /*case 'db':
                    $cacheManager = new \DrBenton\Component\SimpleCache\Adapter\Db();
                    break;*/

            }

            $cacheManager->debug = $app['debug'];

            if (isset($app['monolog'])) {
                $cacheManager->setLogger($app['monolog']);
            }

            return $cacheManager;

        });

    }
}

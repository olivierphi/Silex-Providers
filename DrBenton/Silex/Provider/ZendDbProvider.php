<?php

namespace DrBenton\Silex\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Inspiration fo this Provider comes from : http://www.syndicatetheory.com/labs/using-zend_db-with-silex
 */
class ZendDbProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {

        /**
         * @return \Zend_Db_Adapter_Abstract
         * @throws \InvalidArgumentException
         * @throws \Zend_Db_Exception
         */
        $app['zend.db'] = $app->share(function() use ($app) {

            // Unfortunately, ZF is not "full PSR-0" and needs include path fine tuning :-/
            if (false === strpos(get_include_path(), $app['zend.class_path'])) {
                set_include_path(implode(PATH_SEPARATOR, array(
                    $app['zend.class_path'],
                    get_include_path(),
                )));
            }

            $profilerEnabled = (isset($app['zend.db.profiler.enabled'])) ? (boolean) $app['zend.db.profiler.enabled'] : false ;

            // DB creation setup
            $dbCreationParams = array();
            if (isset($app['zend.db.host']))
                $dbCreationParams['host'] = $app['zend.db.host'];
            if (isset($app['zend.db.dbname']))
                $dbCreationParams['dbname'] = $app['zend.db.dbname'];
            if (isset($app['zend.db.username']))
                $dbCreationParams['username'] = $app['zend.db.username'];
            if (isset($app['zend.db.password']))
                $dbCreationParams['password'] = $app['zend.db.password'];
            $dbCreationParams['profiler'] = $profilerEnabled;

            // Zend_Db_Adapter_Abstract creation !
            $db = \Zend_Db::factory($app['zend.db.adapter'], $dbCreationParams );


            if ($profilerEnabled) {

                // Let's log DB queries, thanks to Zend_Db_Profiler
                $profiler = new \Zend_Db_Profiler();
                $db->setProfiler($profiler);
                $profiler->setEnabled(true);

                $app['zend.db.profiler'] = $profiler;

                if (isset($app['zend.db.profiler.log_file_path'])) {

                    // At the end of every Request, we will write DB queries profiling to a log file
                    $app->after(function (\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response) use ($app, $profiler) {

                        $logFilePath = $app['zend.db.profiler.log_file_path'];

                        $logContent = '';
                        $queries = $profiler->getQueryProfiles();
                        if (is_array($queries) && sizeof($queries) > 1) {

                            foreach($queries as $currentQueryProfile) {

                                $logContent .= '> ' . $currentQueryProfile->getQuery() . PHP_EOL;
                                $logContent .= '>> Params: ' . json_encode($currentQueryProfile->getQueryParams()). PHP_EOL;
                                $logContent .= '>> Duration: ' . $currentQueryProfile->getElapsedSecs() . PHP_EOL;
                                $logContent .=  PHP_EOL;

                            }

                            if (strlen($logContent) > 0) {

                                $logContent = 'Profile "'.date('Y-m-d H:i:s').'" (' . (sizeof($queries)-1) . ' queries) :' .
                                    PHP_EOL . $logContent . '-------------------------' . PHP_EOL ;

                                file_put_contents( $logFilePath, $logContent, FILE_APPEND );

                            }

                        }


                    });

                }

            }


            return $db;

        });

    }


    public function boot(Application $app) {

    }

}

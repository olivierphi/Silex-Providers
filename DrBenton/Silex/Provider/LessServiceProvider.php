<?php

namespace DrBenton\Silex\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

use DrBenton\Component\LessCompiler;

class LessServiceProvider implements ServiceProviderInterface
{
    
    
    public function register(Application $app)
    {
        
        if (! isset($app['less.node_path'])) {
            $app['less.node_path'] = '/usr/bin/node';
        }
        
        $compiler = new LessCompiler();
        $compiler->debug = $app['debug'];

        if (isset($app['monolog'])) {
            $compiler->setLogger($app['monolog']);
        }
        if (isset($app['less.enabled'])) {
            $compiler->enabled = (boolean) $app['less.enabled'];
        }
        if (isset($app['less.compress'])) {
            $compiler->compress = (boolean) $app['less.compress'];
        }
        if (isset($app['less.node_path'])) {
            $compiler->nodePath = (string) $app['less.node_path'];
        }
        if (isset($app['less.node_less_module_path'])) {
            $compiler->lessModulePath = (string) $app['less.node_less_module_path'];
        }
        if (isset($app['less.tmp_folder'])) {
            $compiler->tmpFolder = (string) $app['less.tmp_folder'];
        }
        if (isset($app['less.force_compilation'])) {
            $compiler->forceCompilation = (boolean) $app['less.force_compilation'];
        }

        if (isset($app['twig'])) {

            $app->before( function() use ($app, $compiler) {

                $twigExtensionCompilationClosure = function() use ($app, $compiler) {
                    call_user_func_array( array($compiler, 'compile'), func_get_args() );
                    return '';
                };
                $twigExtension = new \DrBenton\Twig\Extension\LessCompilerExtension ($twigExtensionCompilationClosure);
                $app['twig']->addExtension($twigExtension);
                
                if (isset($app['less.web_files_folder_path'])) {
                    $twigExtension->setWebFilesFolderPath($app['less.web_files_folder_path']);
                }

            });

        }
        

        $app['less'] = $app->share( function() use($compiler) {
            
            return $compiler;
            
        });
            
    }


    public function boot(Application $app) {

    }
    
    
}
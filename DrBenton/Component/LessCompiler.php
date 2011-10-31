<?php

namespace DrBenton\Component;


class LessCompiler
{
    
    /**
     *
     * @var boolean
     */
    public $enabled = true;
    
    /**
     *
     * @var boolean
     */
    public $debug;
    /**
     *
     * @var string
     */
    public $nodePath = '/usr/bin/node';
    /**
     *
     * @var string
     */
    public $lessModulePath = 'less';
    /**
     *
     * @var boolean
     */
    public $compress = false;
    /**
     * If 'false', the "sys_get_temp_dir()" PHP function will be used.
     * @var string|false
     */
    public $tmpFolder = false;
    /**
     * If set to 'true', the LESS to CSS compilation will occur even if the last
     * modification date of the LESS file is the same or older than the CSS last
     * modification date.
     * @var boolean
     */
    public $forceCompilation = false;
    
    /**
     *
     * @var \Monolog\Logger 
     */
    protected $_logger;
    
    /**
     * The compilation is made only if the CSS file is older than the LESS one
     * @param string $lessFilePath
     * @param string $cssOutputFilePath 
     * @param boolean true when it compiles, false otherwise
     */
    public function compile ($lessFilePath, $cssOutputFilePath)
    {
        
        if (! $this->enabled) {
            return false;
        }
        
        if (! $this->forceCompilation)
        {
            
            $sourceLastM = filemtime($lessFilePath);
            $targetLastM = (is_file($cssOutputFilePath)) ? filemtime($cssOutputFilePath) : 0 ;

            if (! is_null($this->_logger) && $this->debug) {
                $this->_logger->addDebug('Compilation of "'.$lessFilePath.'" (last change : '.$sourceLastM.') to "'.$cssOutputFilePath.'" (last change : '.$targetLastM.')...');
            }

            if (is_file($cssOutputFilePath) && $sourceLastM > $targetLastM)
            {
                if (! is_null($this->_logger) && $this->debug) {
                    $this->_logger->addDebug('Compilation skipped.');
                }
                return false;
            }
            
        }
        
        
        // Most of the following code comes from Assetic Project
        // @see https://github.com/kriswallsmith
        // @see https://github.com/kriswallsmith/assetic/blob/master/src/Assetic/Filter/LessFilter.php
        
        static $format = <<<'EOF'
var less = require('%s');
var sys = require('sys');

new(less.Parser)().parse(%s, function(e, tree) {
    if (e) {
        less.writeError(e);
        process.exit(2);
    }

    try {
        sys.print(tree.toCSS(%s));
    } catch (e) {
        less.writeError(e);
        process.exit(3);
    }
});
EOF;
        
        // tree options
        $treeOptions = array();
        $treeOptions['compress'] = $this->compress;
        
        // source LESS code
        $lessCode = file_get_contents($lessFilePath);
        
        // Temp file build
        $targetFolder = (false === $this->tmpFolder) ? sys_get_temp_dir() : $this->tmpFolder ;
        $tmpNodeFilePath = tempnam($targetFolder, 'less_compiler');
        file_put_contents($tmpNodeFilePath, sprintf($format,
            $this->lessModulePath,
            json_encode($lessCode),
            json_encode($treeOptions)
        ));
        
        // Exec string build
        $execStr = escapeshellcmd($this->nodePath).' ';
        $execStr .= escapeshellarg($tmpNodeFilePath).' ';
        $execStr .= '2>&1';//err output -> std output
        
        // Go! Go! Go!
        $cwd = getcwd();
        chdir( dirname($lessFilePath) );//this way, @imported LESS file are findable :-)
        if (! is_null($this->_logger) && $this->debug) {
            $this->_logger->addDebug('Compilation : $execStr='.$execStr);
        }
        $outputCss = shell_exec( $execStr );
        chdir( $cwd );//back to previous working directory
        
        // Temp file destruction
        unlink($tmpNodeFilePath);

        // Finish...
        //if (0 < $code) {
        //    throw new \RuntimeException($outputCss);
        //}

        file_put_contents($cssOutputFilePath, $outputCss);
        $touchResult = touch($cssOutputFilePath, $sourceLastM );
        
        
        if (! is_null($this->_logger) && $this->debug) {
            $this->_logger->addDebug('Compilation done. ("touch()" success :'.( (int) $touchResult ));
        }
        
        return true;
        
    }
    
    
    /**
     *
     * @param \Monolog\Logger $logger 
     */
    public function setLogger (\Monolog\Logger $logger)
    {
        $this->_logger = $logger;
    }
    
    
    
}
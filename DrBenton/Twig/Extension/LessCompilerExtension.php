<?php

namespace DrBenton\Twig\Extension;

class LessCompilerExtension extends \Twig_Extension {

    /**
     *
     * @var \Closure
     */
    protected $_lessCompilationClosure;
    /**
     *
     * @var string
     */
    protected $_webFilesFolderPath = '';
    
    /**
     * The given Closure must accept the following 2 params :
     *  - the LESS input file path
     *  - the CSS output file path
     * 
     * @param \Closure $lessCompilationClosure 
     */
    public function __construct(\Closure $lessCompilationClosure) {
        $this->_lessCompilationClosure = & $lessCompilationClosure;
    }

    
    /**
     * {@inheritdoc}
     */
    public function getFunctions () {

        return array(
            'compileLess'   => new \Twig_Function_Method($this, 'compileLess'),
            'less'          => new \Twig_Function_Method($this, 'less'),
        );
    }
    
    
    /**
     *
     * @param string $webFilesFolderPath 
     */
    public function setWebFilesFolderPath ($webFilesFolderPath)
    {
        $this->_webFilesFolderPath = $webFilesFolderPath;
    }

    
    /**
     * Compiles he given LESS file path to the given CSS file path.
     * @param string $lessInputPath
     * @param string $cssOutputPath
     * @return boolean 
     */
    public function compileLess($lessInputPath, $cssOutputPath) {
        
        return call_user_func( $this->_lessCompilationClosure, $lessInputPath, $cssOutputPath );
        
    }

    /**
     * Compiles he given LESS file path to a CSS file in the same folder, with
     * the same name, with the ".css" extension instead of ".less".<br/>
     * The web public files folder path is prepended to the files paths. You have
     * to set it with the #setWebFilesFolderPath() method before using this "less" 
     * Twig function.<br/>
     * Returns the CSS file path.
     * @param string $lessInputPath
     * @return string 
     */
    public function less($lessInputPath) {
        
        $cssOutputPath = preg_replace('/\.less$/i', '.css', $lessInputPath);
        
        $fullLessInputPath = $this->_webFilesFolderPath . $lessInputPath;
        $fullCssOutputPath = $this->_webFilesFolderPath . $cssOutputPath;
        
        call_user_func( $this->_lessCompilationClosure, $fullLessInputPath, $fullCssOutputPath );
        
        return $cssOutputPath;
        
    }
    
    /**
    * {@inheritdoc}
    */
    public function getName() {
        return 'less';
    }    

}

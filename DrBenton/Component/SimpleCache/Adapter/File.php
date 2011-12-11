<?php

namespace DrBenton\Component\SimpleCache\Adapter;

use DrBenton\Component\SimpleCache\Adapter;

class File extends Adapter
{

    /**
     * @var bool
     */
    public $serializeAuto = true;

    /**
     * @var string
     */
    protected $_filesFolderPath;

    /**
     * @param string $dataFilesFolderPath
     * @throw \Exception
     */
    function __construct( $dataFilesFolderPath ) {

        if (! is_dir($dataFilesFolderPath)) {
            throw new \Exception('Files cache folder "'.$dataFilesFolderPath.'" not found !');
        }

        $this->_filesFolderPath = $dataFilesFolderPath;

    }


    /**
     * @param string $key
     * @return boolean
     */
    function has($key)
    {
        return file_exists($this->_getFilePath($key));
    }

    /**
     * @param string $key
     * @return mixed
     */
    function get($key)
    {
        if ( !$this->has($key)) {
            return false;
        }

        $data = file_get_contents($this->_getFilePath($key));

        if ($this->serializeAuto) {
            return unserialize($data);
        } else {
            return $data;
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @throw \Exception
     */
    function set($key, $value)
    {
        if ($this->serializeAuto) {
            $value = serialize($value);
        } else if (! is_scalar($value)) {
            throw new \Exception('Value is not a scalar, and "serializeAuto" is off !');
        }

        $targetFilePath = $this->_getFilePath($key);
        file_put_contents($targetFilePath, $value);

        if ($this->debug && !is_null($this->_logger)) {
            $this->_logger->addDebug('Data "'.$key.'" saved in "'.$targetFilePath.'" by "file" Adapter.');
        }
    }

    /**
     * @param string $key
     * @return bool
     */
    function clear($key)
    {
        if ( !$this->has($key)) {
            return false;
        }
        unlink($this->_getFilePath($key));
        return true;
    }


    /**
     * @param string $key
     * @return string
     */
    protected function _getFilePath($key)
    {
        return $this->_filesFolderPath . '/' . $this->_normalizeKey($key) . '.data';
    }

}
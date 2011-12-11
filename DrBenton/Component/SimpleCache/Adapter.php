<?php

namespace DrBenton\Component\SimpleCache;

abstract class Adapter
{

    /**
     *
     * @var boolean
     */
    public $debug;

    /**
     *
     * @var \Monolog\Logger
     */
    protected $_logger;

    /**
     * @abstract
     * @param string $key
     * @return boolean
     */
    abstract public function has($key);

    /**
     * @abstract
     * @param string $key
     * @return mixed
     */
    abstract public function get($key);

    /**
     * @abstract
     * @param string $key
     * @param mixed $value
     */
    abstract public function set($key, $value);

    /**
     * @abstract
     * @param string $key
     * @return bool
     */
    abstract public function clear($key);

    /**
     *
     * @param \Monolog\Logger $logger
     */
    public function setLogger (\Monolog\Logger $logger)
    {
        $this->_logger = $logger;
    }

    /**
     * @param string $key
     * @return string
     */
    protected function _normalizeKey($key) {

        $key = trim( strtolower((string) $key) );
        $key = preg_replace('/[^a-z0-9\s-]/', '', $key);
        $key = preg_replace('/[\s-]+/', '-', $key);
        return $key;

    }

}
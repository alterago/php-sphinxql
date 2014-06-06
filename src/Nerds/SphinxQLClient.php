<?php

namespace Nerds\SphinxQL;
use Nerds\SphinxQL\SphinxQLException;


class SphinxQLClient {

    const FETCH_NUM     = 1;
    const FETCH_ASSOC   = 2;
    const FETCH_OBJ     = 3;

    /**
     * @var string The address of the server this client is to connect to
     */
    protected $_server = null;

    /**
     * @var string port of the server this client is to connect to
     */

    protected $_port = null;

    /**
     * @var \mysqli _handle resource A reference to the mysql link that this client will be using
     */
    protected $_handle = null;

    /**
     * @var \mysqli_result resource A reference to the mysql result returned by a query that this client has performed
     */
    protected $_result = null;

    public function __construct($server = null, $port = null) {
        if ($server && !is_string($server)) {
            throw new SphinxQLException ("server is not string");
        }

        if ($port && !is_numeric($port)) {
            throw new SphinxQLException ("port is not numeric");
        }

        $this->_server = $server;
        $this->_port = $port;
    }

    public function setServer($server) {
        if (!is_string($server)) {
            throw new SphinxQLException ("server is not string");
        }

        $this->_server = $server;
    }

    public function setPort($port) {
        if (!is_numeric($port)) {
            throw new SphinxQLException ("port is not numeric");
        }

        $this->_port = $port;
    }

    protected function connect() {
        if ($this->_handle) {
            return true;
        }

        $this->_handle = mysqli_init();
        if (!$this->_handle) {
            throw new SphinxQLException ("mysqli init failed");
        }
        try {
            $this->_handle->options(MYSQLI_OPT_CONNECT_TIMEOUT, 2);
            $this->_handle->real_connect($this->_server . ':' . $this->_port);
        } catch (Exception $e) {
            throw new SphinxQLException ($this->_handle->error);
        }
        return true;
    }

    /**
     * Perform a query
     *
     * @param string $query The query to perform
     * @return SphinxQLClient This client object
     * @throws SphinxQLException
     *
     */
    public function query($query) {
        $this->_result = false;
        $this->connect();

        $this->_result = $this->_handle->query((string)$query);
        if (!$this->_result) {
            throw new SphinxQLException ($this->_handle->error);
        }
        return $this;
    }

    /**
     * @param int $fetchStyle
     * @param null $class_name
     * @param array|null $params
     * @return array|bool|null|object|\stdClass
     * @throws SphinxQLException
     */
    public function fetch($fetchStyle = self::FETCH_ASSOC, $class_name = null, array $params = null) {
        if ($this->_result === false) {
            return false;
        }

        switch ($fetchStyle) {
            case self::FETCH_ASSOC:
                if ($row = $this->_result->fetch_assoc()) {
                    return $row;
                }
                return array();
            case self::FETCH_NUM:
                if ($row = $this->_result->fetch_row()) {
                    return $row;
                }
                return array();
            case self::FETCH_OBJ:
                if ($row = $this->_result->fetch_object($class_name, $params)) {
                    return $row;
                }
                return null;
        }

        throw new SphinxQLException ("Incorrect fetch style");
    }


    public function fetchAll($fetchStyle = self::FETCH_ASSOC, $class_name = null, $params = null) {
        if ($this->_result === false) {
            return false;
        }

        $return = array();

        while ($row = $this->fetch($fetchStyle, $class_name, $params)) {
            $return[] = $row;
        }

        return $return;
    }

}
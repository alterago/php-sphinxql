<?php

namespace Nerds;
use Nerds\SphinxQLException;
use Nerds\SphinxQLClient;
use Nerds\SphinxQLQuery;
use \Exception;

class SphinxQL {

    protected static $_client;

    private static $server;
    private static $port;

    private static $instance = null;

    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new self(self::$server, self::$port);
        }

        return self::$instance;
    }

    public static function init($server, $port) {
        self::$server = $server;
        self::$port = $port;
    }

    public function __construct($server, $port) {
        self::$_client = new SphinxQLClient($server, $port);
    }

    public function getQuery() {
        return new SphinxQLQuery();
    }

    public static function fromString($queryString) {
        return SphinxQLQuery::fromString($queryString);
    }

    public function query(SphinxQLQuery $query) {
        self::$_client->query($query->toString());
        return $this;
    }

    public function fetch($fetchStyle = SphinxQLClient::FETCH_ASSOC, $class_name = null, array $params = null) {
        return self::$_client->fetch($fetchStyle, $class_name, $params);
    }

    public function fetchArray($params = null) {
        return self::$_client->fetch(SphinxQLClient::FETCH_NUM, null, $params);
    }

    public function fetchObject($class_name, $params = null) {
        return self::$_client->fetch(SphinxQLClient::FETCH_OBJ, $class_name, $params);
    }

    public function fetchAll() {
        return self::$_client->fetchAll();
    }

    public function getMeta() {
        $result = $this->query($this->getQuery()->setTypeShow('META'))->fetchAll();
        if (!$result) return false;
        $meta = array();
        foreach ($result as $key => $value) {
            $meta[$value['Variable_name']] = $value['Value'];
        }
        return $meta;
    }
}

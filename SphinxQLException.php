<?php
namespace Nerds\SphinxQL;

class SphinxQLException extends \RuntimeException {
    public function __construct($message = 'SphinxQL Exception', \Exception $previous = null) {
        parent::__construct($message, 500, $previous);
    }
}
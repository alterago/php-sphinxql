<?php
namespace Nerds\SphinxQL;

use Nerds\SphinxQL\SphinxQLException;
use Nerds\SphinxQL\SphinxQLClient;
use Nerds\SphinxQL\SphinxQLQuery;
use Nerds\SphinxQL\SphinxQL;

require_once '../SphinxQLQuery.php';
require_once '../SphinxQL.php';
require_once '../SphinxQLClient.php';
require_once '../SphinxQLException.php';


$sphinx = new SphinxQL('127.0.0.1', 3307);
$query = $sphinx->getQuery();
$query->addIndex('searchIndex');
$query->setSearch('in');
$query->addWhere('links_count', 20, '>');
$sphinx->query($query);
$rows = $sphinx->fetchAll();

var_dump($rows);


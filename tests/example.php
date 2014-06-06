<?php
namespace Nerds\SphinxQL;

use Nerds\SphinxQL\SphinxQL;

require_once '../src/Nerds/SphinxQLQuery.php';
require_once '../src/Nerds/SphinxQL.php';
require_once '../src/Nerds/SphinxQLClient.php';
require_once '../src/Nerds/SphinxQLException.php';


$sphinx = new SphinxQL('127.0.0.1', 3307);
$query = $sphinx->getQuery();
$query->addIndex('searchIndex');
$query->setSearch('in');
$query->addWhere('links_count', 20, '>');
$sphinx->query($query);
$rows = $sphinx->fetchAll();

var_dump($rows);


<?php

use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;

$loader = new Loader();

$loader->registerDirs(
  [
    __DIR__ . '/models/'
  ]
)->register();

$di = new FactoryDefault();

$di->set(
  'db',
  function () {
    return new PdoMysql(
      [
        'host'     => 'localhost',
        'username' => "root",
        'password' => "",
        'dbname'   => "apimerlin",
        "options" => array( // this is your important part
          1002 => 'SET NAMES utf8',
          PDO::ATTR_EMULATE_PREPARES => false,
          PDO::ATTR_STRINGIFY_FETCHES => false
        )
      ]
    );
  }
);

$app = new Micro($di);

$app->get(
  '/api/flights',
  function () use ($app) {
    $phl = "SELECT * FROM flight";
    $flights = $app->db->fetchAll($phl);
    
    echo $flights;
  }
);

$app->notFound(function () use ($app) {
  $app->response->setStatusCode(404, "Not Found")->sendHeaders();
  echo '<h1> Page not found ! </h1>';
});


$app->handle($_SERVER["REQUEST_URI"]);
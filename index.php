<?php

use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;
use Phalcon\Http\Response;
use Phalcon\Exception;


function createJsonResponse($code, $reason, $data)
{
  $response = new Response();
  $response->setStatusCode($code, $reason);
  $response->setContent(json_encode($data));
  return $response;
}

function getUserData($app, $username, $password)
{

  $user = $app->db->fetchOne("SELECT * FROM users WHERE username = '" . $username . "' AND password = '" . $password . "'");

  if ($user == null)
    return null;

  return $user;
}

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

    $username = $app->request->getServer('PHP_AUTH_USER');
    $password = $app->request->getServer('PHP_AUTH_PW');
    $userData = getUserData($app, $username, $password);

    if ($userData == null)
      return createJsonResponse(403, "Credential error", "Unauthorized");
    else {
      $phl = "SELECT * FROM flight";
      $flights = $app->db->fetchAll($phl);

      return createJsonResponse(200, "Ok", $flights);
    }
  }
);

$app->get(
  '/api/users',
  function () use ($app) {
    $phl = "SELECT * FROM users";
    $users = $app->db->fetchAll($phl);

    return createJsonResponse(200, "Ok", $users);
  }
);

$app->get(
  '/api/flight/{id}',
  function ($id) use ($app) {

    $username = $app->request->getServer('PHP_AUTH_USER');
    $password = $app->request->getServer('PHP_AUTH_PW');
    $userData = getUserData($app, $username, $password);

    if ($userData == null)
      return createJsonResponse(403, "Credential error", "Unauthorized");

    else {
      $phl = "SELECT * FROM flight WHERE id =" . $id;
      $flights = $app->db->fetchAll($phl);

      return createJsonResponse(200, "Ok", $flights);
    }
  }
);

$app->post(
  '/api/add_flight',
  function () use ($app) {
    $username = $app->request->getServer('PHP_AUTH_USER');
    $password = $app->request->getServer('PHP_AUTH_PW');
    $userData = getUserData($app, $username, $password);

    if ($userData == null)
      return createJsonResponse(403, "Credential error", "Unauthorized");
    else {
      $data = $app->request->getJsonRawBody();

      $app->db->execute("INSERT INTO flight (type, specie, date, note) VALUES ('" . $data->type . "', '" . $data->specie . "', " . $data->date . ", '" . $data->note . "')");
      return createJsonResponse(201, "Ok", "Fligh added");
    }
  }
);

$app->put(
  '/api/edit_flight/{id}',
  function ($id) use ($app) {

    $username = $app->request->getServer('PHP_AUTH_USER');
    $password = $app->request->getServer('PHP_AUTH_PW');
    $userData = getUserData($app, $username, $password);

    if ($userData == null)
      return createJsonResponse(403, "Credential error", "Unauthorized");
    else {
      $data = $app->request->getJsonRawBody();

      $app->db->execute("UPDATE flight SET type = '" . $data->type . "', specie = '" . $data->specie . "', date =  '" . $data->date . "', note = '" . $data->note . "' WHERE id =" . $id);
      return createJsonResponse(200, "Ok", "Fligh updated");
    }
  }
);

$app->delete(
  '/api/delete_flight/{id}',
  function ($id) use ($app) {

    $username = $app->request->getServer('PHP_AUTH_USER');
    $password = $app->request->getServer('PHP_AUTH_PW');
    $userData = getUserData($app, $username, $password);

    if ($userData == null)
      return createJsonResponse(403, "Credential error", "Unauthorized");
    else {
      $phl = "DELETE FROM flight WHERE id =" . $id;
      $app->db->fetchAll($phl);

      return createJsonResponse(200, "Ok", "Flight deleted");
    }
  }
);

$app->notFound(function () use ($app) {

  $username = $app->request->getServer('PHP_AUTH_USER');
  $password = $app->request->getServer('PHP_AUTH_PW');
  $userData = getUserData($app, $username, $password);

  if ($userData == null)
    return createJsonResponse(403, "Credential error", "Unauthorized");
  else {
    $app->response->setStatusCode(404, "Not Found")->sendHeaders();
    echo '<h2> Request not found </h2>';
  }
});

$app->handle($_SERVER["REQUEST_URI"]);

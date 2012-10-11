<?php
session_start();

require_once("Api.class.php");

$api = new Api();

$r = new Redis();
$r->open("localhost");

$callback = function($info, $payload) use ($r) {
    switch ($info['method']) {
        case "POST":
            return $r->multi()
              ->set($info['key'], $payload['payload'])
              ->publish("e:data:" . $info['method'], $info['uri'])
              ->exec();
            break;
        case "GET":
            return $r->get($info['key']);
            break;
        default: throw new ApiException("Method " . $info['method'] . " not handled in callback");
    }
};

$setup = function($info, $payload) use ($r, $api) {
    $setupInfo = array(
        "resources" => $api->getResourceNames(),
        "sid" => session_id()
    );
    $r->multi();
    foreach ($setupInfo['resources'] as $resource) $r->sadd($setupInfo['sid'], $resource);
    $r->expire($setupInfo['sid'], ini_get("session.gc_maxlifetime"));
    $r->publish("e:ctrl:new", $setupInfo['sid']);
    $r->exec();

    $error = false;

    // wait max. 5sec for node/socket to register namespaces
    $ts = time() + 5;
    do {
        usleep(1e3);
        if (time() >= $ts) {
            $error = true;
            break;
        }
    } while ($r->scard($setupInfo['sid']) > 0);

    return array(
        "data" => $setupInfo,
        "error" => false,
        "status" => 200
    );
};

$api
  ->bind("/color", "GET", $callback)
  ->bind("/color", "POST", $callback)
  ->bind("/text", "GET", $callback)
  ->bind("/text", "POST", $callback)
  ->bind("/message", "GET", $callback)
  ->bind("/message", "POST", $callback)
  ->setup($setup);

$api->serve();
<?php
session_start();

require_once("Api.class.php");

$api = new Api();

$r = new Redis();
$r->open("localhost");

$callback = function($info, $data) use ($r) {
    $r->multi();
    switch ($info['method']) {
        case "POST":
        case "PUT":
            $r->hmSet("data:" . $info['key'], array(
                "payload" => $data['payload'],
                "date" => time()
            ));
            $r->publish("e:data:" . $info['method'], $info['uri']);
            break;
        case "GET":
            $r->get($info['key']);
            break;
        default: throw new ApiException("Method " . $info['method'] . " not handled in callback");
    }
    return $r->exec();
};

$setup = function($info, $payload) use ($r, $api) {
    $setupInfo = array(
        "resources" => $api->getResourceNames(),
        "sid" => session_id()
    );
    $r->multi();
    foreach ($setupInfo['resources'] as $resource) {
        $r->sadd($setupInfo['sid'], $resource);
        $r->sadd("ns:" . str_replace("/", ":", preg_replace("/\//", '', $resource, 1)), $setupInfo['sid']);
    }
    $r->expire($setupInfo['sid'], ini_get("session.gc_maxlifetime"));
    $r->publish("e:ctrl:client:new", $setupInfo['sid']);
    $r->exec();




    // wait max. 5sec for node/socket to register namespaces
    $ts = time() + 5;
    do {
        $error = (time() >= $ts || usleep(2e4));
    } while (!$error && $r->scard($setupInfo['sid']) > 0);






    return array(
        "payload" => $setupInfo,
        "error" => $error,
        "status" => 200
    );
};

$api
  ->bind("/color", "GET", $callback)
  ->bind("/color", "PUT", $callback)
  ->bind("/text", "GET", $callback)
  ->bind("/text", "PUT", $callback)
  ->bind("/messages", "GET", $callback)
  ->bind("/messages", "POST", $callback)
  ->setup($setup);

$api->serve();
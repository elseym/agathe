<?php

class Api
{
    private $base;
    private $resources;
    private $methods;

    const SETUP_URI = "/setup";

    /**
     * @param string $base
     */
    public function __construct($base = "/api") {
        $this->base = $base;
        $this->resources = array();
        $this->methods = array("GET", "POST", "PUT", "DELETE");
    }

    /**
     * @param string $uri uri
     * @param string $method
     * @param callable $func receives ($info, $data, $children, ...); should return array with keys: status, body
     * @throws ApiException
     */
    public function bind($uri, $method, $func) {
        if (!in_array($method, $this->methods)) {
            throw new ApiException("Method $method not supported.");
        }

        if (!array_key_exists($uri, $this->resources)) {
            $this->resources[$uri] = array();
        }

        $this->resources[$uri][$method] = $func;

        return $this;
    }

    /**
     * @param callable $func
     * @return Api
     */
    public function setup($func) {
        return $this->bind(self::SETUP_URI, "GET", $func);
    }

    /**
     * @throws ApiException
     */
    public function serve() {
        $reqUri = preg_replace(array("#^" . $this->base . "#", "/\?.*$/"), "", $_SERVER['REQUEST_URI']);
        $reqMethod = strtoupper($_SERVER['REQUEST_METHOD']);

        // retrieve payload data
        $payload = array();
        switch ($reqMethod) {
            case "GET":     $payload = $_GET; break;
            case "POST":    $payload = $_POST; break;
            case "PUT":     $payload = file_get_contents("php://input"); break;
            case "DELETE":  break;
            default:        throw new ApiException("Method $reqMethod not supported.");
        }

        // find best match for: $reqUri <-> definedUri,
        // give rest of $reqUri as 2nd to nth parameter to callback
        $reqUriComponents = explode("/", $reqUri);
        $funcParams = array();
        do {
            if ($reqUri == "") {
                $this->respond(array("error" => "$reqUri not found."), 404);
            }
            if (isset($this->resources[$reqUri])) {
                break;
            }
            array_unshift($funcParams, array_pop($reqUriComponents));
            $reqUri = implode("/", $reqUriComponents);
        } while (count($reqUriComponents));

        // prepend $payload array to callback
        array_unshift($funcParams, $payload);

        // provide additional info as callback function's first parameter
        $header = array(
            "method" => $reqMethod,
            "uri" => $reqUri,
            "key" => str_replace("/", ":", preg_replace("/\//", '', $reqUri, 1))
        );
        array_unshift($funcParams, $header);

        // setup-requests get extra info as last callback-param
//        if ($reqUri === self::SETUP_URI) {
//            $setupInfo = array(
//                "resources" => $this->getResourceNames()
//            );
//            array_push($funcParams, $setupInfo);
//        }

        if (!array_key_exists($reqMethod, $this->resources[$reqUri])) {
            $this->respond(array("error" => "Method $reqMethod not handled."), 400);
        } else {
            $ret = call_user_func_array($this->resources[$reqUri][$reqMethod], $funcParams);
            if (!is_array($ret)) {
                $this->respond($ret);
            } else {
                $retBody = isset($ret['data']) ? $ret['data'] : $ret;
                $retStatus = isset($ret['status']) ? $ret['status'] : 200;
                $retError = isset($ret['error']) ? $ret['error'] : false;
                $this->respond($retBody, $retStatus, $retError);
            }
        }
    }

    public function getResourceNames() {
        return array_keys(array_diff_key($this->resources, array(self::SETUP_URI => 1)));
    }

    /**
     * @param $body
     * @param int $status
     */
    public function respond($data, $status = 200, $error = false) {
        $codes = Array(
            200 => 'OK', // success for get, delete, put
            201 => 'Created', // success for post
            202 => 'Accepted', // queued
            204 => 'No Content',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            307 => 'Temporary Redirect',
            400 => 'Bad Request', // invalid data
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            409 => 'Conflict', // duplicate
            500 => 'Internal Server Error'
        );

        if (!array_key_exists($status, $codes)) {
            throw new ApiException("Status code $status not allowed.");
        }

        $data = array("data" => $data);
        if (!array_key_exists("error", $data)) {
            $data['error'] = false;
        }
        $data['error'] &= $error;
        $res = json_encode($data);

        header("HTTP/1.1 " . $status . $codes[$status]);
        header("Content-Type: application/json");
        header("Content-Length: " . strlen($res));

        print($res);
        exit();
    }
}

class ApiException extends Exception {}
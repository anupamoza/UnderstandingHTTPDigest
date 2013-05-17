<?php
/**
 * This script demonstrates HTTP Digest Access Authentication from the
 * server's perspective.
 */

$username = 'admin';
$password = 'pass';
$realm = 'Authorized users of example.com';

$nonce = md5(uniqid());
$opaque = md5(uniqid());
$valid = false;

$headers = getallheaders();
if (array_key_exists('Authorization', $headers)) {
    $authHeader = substr($headers['Authorization'],  strlen('Digest'));
    $parsed = array();
    foreach (explode(',', $authHeader) as $pair) {
        $vals = explode('=', $pair);
        $parsed[trim($vals[0])] = trim($vals[1], '" ');
    }

    $A1 = md5("$username:" . $parsed['realm'] . ":$password");
    $A2 = md5($_SERVER['REQUEST_METHOD'] . ':' . $parsed['uri']);
    $response = md5("$A1:" . $parsed['nonce'] . ":$A2");

    $valid = ($response == $parsed['response']);
}

if (!$valid) {
    header('HTTP/1.1 401 Unauthorized');
    header('Content-Type: text/html');
    header(sprintf('WWW-Authenticate: Digest realm="%s", nonce="%s", opaque="%s"', $realm, $nonce, $opaque));
    echo 'You need to authenticate.';
    exit();
}
else {
    print 'You can now see this super restricted area.';
}

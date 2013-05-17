<?php
/**
 * This script demonstrates HTTP Digest Access Authentication from the
 * client's perspective.
 */

$method = 'GET';
$uriBase = 'http://localhost';
$uriPath = '/server.php';
$username = 'admin';
$password = 'pass';

$uri = $uriBase . $uriPath;

$ch = curl_init($uri);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 1);

$response = curl_exec($ch);
$info = curl_getinfo($ch);

preg_match('/WWW-Authenticate: Digest (.*)/', $response, $matches);
$authHeader = $matches[1];
$parsed = array();
foreach (explode(',', $authHeader) as $pair) {
    $vals = explode('=', $pair);
    $parsed[trim($vals[0])] = trim($vals[1], '" ');
}

$A1 = md5("$username:" . $parsed['realm'] . ":$password");
$A2 = md5("$method:$uriPath");
$response = md5("$A1:" . $parsed['nonce'] . ":$A2");

$request = sprintf('Authorization: Digest username="%s", realm="%s", nonce="%s", opaque="%s", uri="%s", response="%s"',
    $username, $parsed['realm'], $parsed['nonce'], $parsed['opaque'], $uriPath, $response);
$reqHeaders = array($request);

$ch = curl_init($uri);
curl_setopt($ch, CURLOPT_HTTPHEADER, $reqHeaders);
curl_exec($ch);

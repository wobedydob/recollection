<?php
// Simple test to check if API is reachable
header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'message' => 'API is working',
    'php_version' => PHP_VERSION,
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'request_uri' => $_SERVER['REQUEST_URI']
]);

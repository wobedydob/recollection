<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../jwt.php';

if (getMethod() !== 'POST') {
    errorResponse('Method not allowed', 405);
}

removeAuthCookie();

jsonResponse(['success' => true]);

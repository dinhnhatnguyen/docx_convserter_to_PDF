<?php


require_once __DIR__ . '/vendor/autoload.php';

// Handle direct access
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new \App\Controllers\ConvertController(
        (new \App\Config\Database())->getConnection()
    );
    
    header('Content-Type: application/json');
    echo $controller->convert();
    exit;
}

// Redirect to home page if accessed directly
header('Location: /');
exit;
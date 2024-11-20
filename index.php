<?php



ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


spl_autoload_register(function ($class) {
    // Convert namespace to full file path
    $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Create uploads directory if it doesn't exist
$uploadsDir = __DIR__ . '/uploads';
if (!file_exists($uploadsDir)) {
    mkdir($uploadsDir, 0777, true);
}

// Basic routing
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($path) {
    case '/':
    case '/index.php':
        require __DIR__ . '/templates/upload.php';
        break;
        
    case '/convert':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller = new App\Controllers\ConvertController();
            echo $controller->convert();
        } else {
            header('HTTP/1.1 405 Method Not Allowed');
            echo 'Method not allowed';
        }
        break;
        
    default:
        // Check if file exists in public directory
        $publicPath = __DIR__ . '/public' . $path;
        if (file_exists($publicPath)) {
            // Set correct content type
            $ext = pathinfo($publicPath, PATHINFO_EXTENSION);
            switch ($ext) {
                case 'css':
                    header('Content-Type: text/css');
                    break;
                case 'js':
                    header('Content-Type: application/javascript');
                    break;
                case 'png':
                    header('Content-Type: image/png');
                    break;
                case 'jpg':
                case 'jpeg':
                    header('Content-Type: image/jpeg');
                    break;
            }
            readfile($publicPath);
            exit;
        }
        
        // 404 page
        header('HTTP/1.1 404 Not Found');
        echo '404 Not Found';
}
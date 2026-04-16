<?php
declare(strict_types=1);

define('CADA_START', microtime(true));
define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/app/bootstrap.php';

use App\Core\Router;
use App\Core\Request;
use App\Core\Response;
use App\Core\Logger;

try {
    $router = new Router();
    require BASE_PATH . '/config/routes.php';

    $request  = Request::capture();
    $response = $router->dispatch($request);
    $response->send();
} catch (Throwable $e) {
    Logger::error($e);

    $debug = (bool) (config('app.debug') ?? false);
    http_response_code(500);

    if ($debug) {
        echo '<pre style="padding:16px;background:#111;color:#f88;font-family:monospace;">';
        echo 'Error: ' . htmlspecialchars($e->getMessage()) . "\n\n";
        echo 'File: ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . "\n\n";
        echo htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
    } else {
        $errorView = BASE_PATH . '/app/Views/errors/500.php';
        if (is_file($errorView)) {
            include $errorView;
        } else {
            echo 'Error interno del servidor.';
        }
    }
}

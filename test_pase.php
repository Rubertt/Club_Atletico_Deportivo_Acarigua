<?php
define('BASE_PATH', __DIR__);
require BASE_PATH . '/app/bootstrap.php';
try {
    $c = new \App\Controllers\Web\AsistenciasController();
    $c->pase(\App\Core\Request::capture());
    echo "OK\n";
} catch (\Throwable $e) {
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}

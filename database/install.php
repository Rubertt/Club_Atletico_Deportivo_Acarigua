<?php
/**
 * Instalador de la base de datos de LEGADO (adaptada al MVC)
 *
 * Uso:
 *   php database/install.php             (Crea DB + importa cada_db_clean.sql)
 *   php database/install.php --fresh     (Dropea DB antes de crearla)
 */
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

// Carga .env
if (is_file(BASE_PATH . '/.env')) {
    foreach (file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        [$name, $value] = array_pad(explode('=', $line, 2), 2, '');
        $_ENV[trim($name)] = trim($value, " \t\"'");
    }
}

$args = $argv;
array_shift($args);
$fresh = in_array('--fresh', $args, true);

$dbHost = $_ENV['DB_HOST'] ?? '127.0.0.1';
$dbPort = (int) ($_ENV['DB_PORT'] ?? 3306);
$dbName = $_ENV['DB_NAME'] ?? 'cada_db';
$dbUser = $_ENV['DB_USER'] ?? 'root';
$dbPass = $_ENV['DB_PASS'] ?? '';

$log    = fn(string $msg) => fwrite(STDOUT, $msg . PHP_EOL);
$err    = fn(string $msg) => fwrite(STDERR, "\033[31m$msg\033[0m" . PHP_EOL);
$ok     = fn(string $msg) => fwrite(STDOUT, "\033[32m✓ $msg\033[0m" . PHP_EOL);
$step   = fn(string $msg) => fwrite(STDOUT, "\033[36m→ $msg\033[0m" . PHP_EOL);

try {
    // Conexión sin DB (para crear/dropear la base)
    $serverDsn = "mysql:host=$dbHost;port=$dbPort;charset=utf8mb4";
    $server    = new PDO($serverDsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
    ]);
    $ok("Conectado a MySQL en $dbHost:$dbPort");

    if ($fresh) {
        $step("Eliminando base de datos `$dbName` (--fresh)...");
        $server->exec("DROP DATABASE IF EXISTS `$dbName`");
    }

    // Crear DB
    $server->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // Conectar a la DB
    $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
    ]);

    // Importar el schema legacy corregido
    $step("Importando schema de legado: cada_db_clean.sql ...");
    $schema = file_get_contents(__DIR__ . '/cada_db_clean.sql');
    if ($schema === false) {
        throw new RuntimeException('No se pudo leer cada_db_clean.sql');
    }
    
    // Ejecutar el script SQL
    $pdo->exec($schema);
    $ok("Base de datos `$dbName` importada correctamente.");

    // Estadísticas
    $step('Verificando instalación...');
    $tables = $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$dbName'")->fetchColumn();
    $users  = $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    $roles  = $pdo->query('SELECT COUNT(*) FROM rol_usuarios')->fetchColumn();
    $cats   = $pdo->query('SELECT COUNT(*) FROM categoria')->fetchColumn();
    $estados = $pdo->query('SELECT COUNT(*) FROM estados')->fetchColumn();

    $log('');
    $log(str_repeat('=', 60));
    $ok("Instalación completada (Base de Legado Alineada)");
    $log(str_repeat('=', 60));
    $log("  Base de datos : $dbName");
    $log("  Tablas        : $tables");
    $log("  Roles         : $roles");
    $log("  Categorías    : $cats");
    $log("  Estados VE    : $estados");
    $log("  Usuarios      : $users");
    $log('');
    $log("  🔐 USUARIOS DISPONIBLES");
    $log("     admin@gmail.com / directivo@gmail.com / entrenador@gmail.com / medico@gmail.com");
    $log("     Contraseña   : 12345678");
    $log('');
    exit(0);
} catch (Throwable $e) {
    $err('✗ Error: ' . $e->getMessage());
    if (getenv('DEBUG')) {
        $err($e->getTraceAsString());
    }
    $log('');
    $log('Sugerencias:');
    $log('  1. Verifica que MySQL/MariaDB esté corriendo (XAMPP).');
    $log('  2. Revisa las credenciales en .env (DB_HOST, DB_USER, DB_PASS)');
    $log('  3. Si la base ya existe, intenta: php database/install.php --fresh');
    exit(1);
}

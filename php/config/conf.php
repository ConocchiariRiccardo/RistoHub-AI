<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);

$db_host    = 'localhost';
$db_name    = 'ristohub_ai';
$db_user    = 'root';
$db_charset = 'utf8mb4';

// MAMP
$db_pass = 'root';
$db_port = '8889';

// XAMPP
// $db_pass = '';
// $db_port = '3306';

$dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset={$db_charset}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Connessione al database fallita.']));
}

define('BASE_URL', 'http://localhost:8888/RistoHub-AI');
define('ROOT_PATH', dirname(__DIR__, 2));
define('NONE', 0);
?>
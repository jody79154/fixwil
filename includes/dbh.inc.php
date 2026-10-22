<?php
require_once __DIR__ . '/../env.php';

$dbHost     = getenv('DB_HOST');
$dbPort     = getenv('DB_PORT');
$dbName     = getenv('DB_NAME');
$dbusername = getenv('DB_USER');
$dbpassword = getenv('DB_PASSWORD');

if (!$dbHost || !$dbName || !$dbusername) {
    die("Database configuration missing — check env.php");
}

$dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName";

try {
    $pdo = new PDO($dsn, $dbusername, $dbpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection Failed: " . $e->getMessage());
}
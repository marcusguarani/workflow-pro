<?php
function config(): array
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../config/config.php';
    }
    return $config;
}

function db(bool $withDatabase = true): PDO
{
    static $pdoWithDb = null;
    static $pdoNoDb = null;

    if ($withDatabase && $pdoWithDb instanceof PDO) {
        return $pdoWithDb;
    }
    if (!$withDatabase && $pdoNoDb instanceof PDO) {
        return $pdoNoDb;
    }

    $db = config()['db'];
    $dsn = $withDatabase
        ? sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $db['host'], $db['port'], $db['database'], $db['charset'])
        : sprintf('mysql:host=%s;port=%s;charset=%s', $db['host'], $db['port'], $db['charset']);

    $pdo = new PDO($dsn, $db['username'], $db['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    if ($withDatabase) {
        $pdoWithDb = $pdo;
    } else {
        $pdoNoDb = $pdo;
    }

    return $pdo;
}

function table_exists(string $tableName): bool
{
    try {
        $stmt = db()->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$tableName]);
        return (bool) $stmt->fetchColumn();
    } catch (Throwable $e) {
        return false;
    }
}

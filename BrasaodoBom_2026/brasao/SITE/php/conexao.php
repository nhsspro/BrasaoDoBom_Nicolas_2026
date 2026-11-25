<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Retorna uma única conexão PDO (padrão Singleton simples).
 * - ERRMODE_EXCEPTION: erros de SQL viram exceções;
 * - EMULATE_PREPARES desligado: prepared statements reais, contra SQL Injection.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    return $pdo;
}

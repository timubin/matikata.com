<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Returns a shared PDO connection and makes sure the table exists.
 */
function get_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    ensure_schema($pdo);
    return $pdo;
}

/**
 * Creates the submissions table if it does not exist.
 */
function ensure_schema(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS mason_applications (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            mason_name VARCHAR(255) NOT NULL,
            mobile_number VARCHAR(50) NOT NULL,
            father_name VARCHAR(255),
            mother_name VARCHAR(255),
            nid_number VARCHAR(100),
            gender VARCHAR(10),
            marital_status VARCHAR(20),
            spouse_name VARCHAR(255),
            spouse_nid VARCHAR(100),
            wife_name VARCHAR(255),
            wife_nid VARCHAR(100),
            blood_group VARCHAR(10),
            permanent_address TEXT,
            present_address TEXT,
            sons INT DEFAULT 0,
            daughters INT DEFAULT 0,
            note TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    add_column_if_missing($pdo, 'mason_applications', 'gender', 'VARCHAR(10)');
    add_column_if_missing($pdo, 'mason_applications', 'marital_status', 'VARCHAR(20)');
    add_column_if_missing($pdo, 'mason_applications', 'spouse_name', 'VARCHAR(255)');
    add_column_if_missing($pdo, 'mason_applications', 'spouse_nid', 'VARCHAR(100)');
    add_column_if_missing($pdo, 'mason_applications', 'wife_name', 'VARCHAR(255)');
    add_column_if_missing($pdo, 'mason_applications', 'wife_nid', 'VARCHAR(100)');
}

/**
 * Adds a column if it does not exist (idempotent).
 */
function add_column_if_missing(PDO $pdo, string $table, string $column, string $type): void
{
    $stmt = $pdo->prepare("DESCRIBE $table");
    $stmt->execute();
    $columns = array_column($stmt->fetchAll(), 'Field');
    if (!in_array($column, $columns, true)) {
        $pdo->exec("ALTER TABLE $table ADD COLUMN $column $type");
    }
}

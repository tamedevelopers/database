<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Session\Handlers;

use PDO;
use SessionHandlerInterface;

/**
 * PDO-backed session handler.
 *
 * Responsibilities:
 * - Persist session data in an SQL table with columns: id, payload, last_activity
 * - Auto-create the table if it does not exist (simple schema)
 * - Enforce TTL by filtering reads and running GC
 */
final class DatabaseSessionHandler implements SessionHandlerInterface
{
    /** @var \Tamedevelopers\Database\Connectors\Connector> Instance of Database Object*/
    private $conn;

    /** @var string Database table name storing sessions */
    private string $table;

    /** @var int Session TTL in seconds used for reads and GC */
    private int $ttl;

    /**
     * @param mixed $conn Instance of Database Object
     * @param string $table Table name to use (default: sessions)
     * @param int $ttl Time-to-live in seconds (default: 1440)
     */
    public function __construct($conn, string $table = 'sessions', int $ttl = 1440)
    {
        $this->conn     = $conn;
        $this->table    = $table;
        $this->ttl      = $ttl;
        $this->createTableIfMissing();
    }

    /**
     * Create the sessions table if it does not exist.
     * @return void
     */
    private function createTableIfMissing(): void
    {

        dd(
            $this->conn->tableExists($this->table)
        );

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id VARCHAR(255) PRIMARY KEY,
            user_id INT UNSIGNED DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent TEXT DEFAULT NULL,
            payload longtext NOT NULL,
            browser VARCHAR(50) DEFAULT NULL,
            platform VARCHAR(50) DEFAULT NULL,
            platform_details TEXT DEFAULT NULL,
            last_activity INT NOT NULL
        )";
        $this->pdo->exec($sql);
    }

    /** @inheritDoc */
    public function open($savePath, $sessionName): bool { return true; }

    /** @inheritDoc */
    public function close(): bool { return true; }

    /** @inheritDoc */
    public function read($id): string|false
    {
        $stmt = $this->pdo->prepare("SELECT payload FROM {$this->table} WHERE id = :id AND last_activity > :exp");
        $stmt->execute([
            ':id' => $id,
            ':exp' => time() - $this->ttl,
        ]);
        $row = $stmt->fetchColumn();
        return $row !== false ? (string) $row : '';
    }

    /** @inheritDoc */
    public function write($id, $data): bool
    {
        $stmt = $this->pdo->prepare("
            REPLACE INTO {$this->table} 
            (id, user_agent, payload, last_activity) 
            VALUES (:id, :agent, :payload, :time)
        ");
        

        dd(
            'ss'
        );
        return $stmt->execute([
            ':id' => $id,
            ':agent' => $id,
            ':payload' => $data,
            ':time' => time(),
        ]);
    }

    /** @inheritDoc */
    public function destroy($id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /** @inheritDoc */
    public function gc($max_lifetime): int|false
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE last_activity < :exp");
        $stmt->execute([':exp' => time() - $this->ttl]);
        return $stmt->rowCount();
    }
}